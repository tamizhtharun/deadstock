/**
 * Invoice Actions Script
 * Handles opening modal, printing, and downloading invoices.
 */

// Global function to open invoice modal
window.openInvoiceModal = function (orderId) {
    console.log('Opening invoice modal for order:', orderId);

    // Check if modal exists
    const modal = document.getElementById('invoiceModal');
    if (!modal) {
        console.error('Invoice modal not found in DOM');
        alert('Invoice modal component not loaded. Please refresh the page.');
        return;
    }

    // Show modal
    modal.style.display = 'block';

    // Load content
    loadInvoiceContent(modal, orderId);
};

// Helper function to load content
function loadInvoiceContent(modal, orderId) {
    const content = modal.querySelector('.invoice-modal-body') || document.getElementById('invoiceContent');
    if (!content) return;

    content.innerHTML = '<div style="text-align: center; padding: 50px;"><i class="fa fa-spinner fa-spin fa-3x"></i><p>Loading invoice...</p></div>';

    fetch(`generate_invoice.php?order_id=${orderId}`)
        .then(response => response.text())
        .then(html => {
            content.innerHTML = html;
        })
        .catch(error => {
            content.innerHTML = '<div style="text-align: center; padding: 50px; color: red;"><i class="fa fa-exclamation-triangle fa-3x"></i><p>Error loading invoice</p></div>';
            console.error('Error:', error);
        });
}

// Global function for printing invoice
window.printInvoice = function () {
    console.log('Print function called');
    const contentDiv = document.getElementById('invoiceContent');
    if (!contentDiv) {
        console.error('Invoice content div not found');
        return;
    }

    // Get raw HTML
    let invoiceContent = contentDiv.innerHTML;

    // Open new window
    const printWindow = window.open('', '_blank', 'height=600,width=800');
    if (!printWindow) {
        alert('Please allow popups for this website to print the invoice.');
        return;
    }

    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Print Invoice</title>
            <style>
                @page { size: A4; margin: 0; }
                body { margin: 0; padding: 0; background: white; font-family: Arial, sans-serif; }
                .invoice-wrapper { width: 100% !important; box-shadow: none !important; margin: 0 !important; }
                .no-print { display: none !important; }
            </style>
        </head>
        <body>
            ${invoiceContent}
            <script>
                window.onload = function() {
                    setTimeout(function() {
                        window.print();
                        window.close();
                    }, 500);
                };
            <\/script>
        </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.focus();
};

// Global function for downloading PDF
window.downloadInvoicePDF = function () {
    console.log('Download function called');
    const downloadBtn = document.getElementById('downloadInvoiceBtn');

    if (typeof html2pdf === 'undefined') {
        alert('PDF library is not loaded yet. Please check your internet connection and try again.');
        return;
    }

    const invoiceElement = document.querySelector('#invoiceContent .invoice-wrapper');
    if (!invoiceElement) {
        alert('Invoice content not found. Please wait for the invoice to load.');
        return;
    }

    // Get invoice number for filename
    const invoiceNumElement = invoiceElement.querySelector('.info-box p');
    let filename = 'Invoice.pdf';
    if (invoiceNumElement) {
        const text = invoiceNumElement.textContent;
        const match = text.match(/Invoice No:\s*(.+)/);
        if (match) {
            filename = match[1].trim() + '.pdf';
        }
    }

    const opt = {
        margin: 0,
        filename: filename,
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: {
            scale: 2,
            useCORS: true,
            logging: true,
            allowTaint: true
        },
        jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
    };

    // Show loading state
    if (downloadBtn) {
        const originalText = downloadBtn.innerHTML;
        downloadBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Generating...';
        downloadBtn.disabled = true;

        html2pdf().set(opt).from(invoiceElement).save()
            .then(() => {
                console.log('PDF generated successfully');
                downloadBtn.innerHTML = originalText;
                downloadBtn.disabled = false;
            })
            .catch(err => {
                console.error('PDF Generation Error:', err);
                alert('Error generating PDF: ' + (err.message || err));
                downloadBtn.innerHTML = originalText;
                downloadBtn.disabled = false;
            });
    } else {
        // Fallback if button not passed (shouldn't happen with global call but good for safety)
        html2pdf().set(opt).from(invoiceElement).save();
    }
};

// Global function to close modal
window.closeInvoiceModal = function () {
    const modal = document.getElementById('invoiceModal');
    if (modal) {
        modal.style.display = 'none';
    }
};

// Initialize event listeners when DOM is loaded
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('invoiceModal');
    const closeBtn = document.querySelector('.invoice-modal-close');

    // Close button click
    if (closeBtn) {
        closeBtn.onclick = function () {
            closeInvoiceModal();
        };
    }

    // Click outside modal to close
    window.onclick = function (event) {
        if (event.target == modal) {
            closeInvoiceModal();
        }
    };
});
