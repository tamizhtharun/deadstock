<!-- Invoice Modal -->
<div id="invoiceModal" class="invoice-modal">
    <div class="invoice-modal-content">
        <div class="invoice-modal-header">
            <h3>Invoice Preview</h3>
            <button class="invoice-modal-close">&times;</button>
        </div>
        <div class="invoice-modal-body" id="invoiceContent">
            <div style="text-align: center; padding: 50px;">
                <i class="fa fa-spinner fa-spin fa-3x"></i>
                <p>Loading invoice...</p>
            </div>
        </div>
        <div class="invoice-modal-footer">
            <button id="downloadInvoiceBtn" class="invoice-action-btn download-btn">
                <i class="fa fa-download"></i> Download PDF
            </button>
            <button id="printInvoiceBtn" class="invoice-action-btn print-btn">
                <i class="fa fa-print"></i> Print
            </button>
        </div>
    </div>
</div>

<style>
.invoice-modal {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.8);
}

.invoice-modal-content {
    background-color: #fff;
    margin: 2% auto;
    width: 95%;
    max-width: 1000px;
    border-radius: 8px;
    box-shadow: 0 5px 30px rgba(0,0,0,0.4);
    max-height: 90vh;
    display: flex;
    flex-direction: column;
}

.invoice-modal-header {
    padding: 15px 20px;
    background: #333;
    color: #fff;
    border-radius: 8px 8px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.invoice-modal-header h3 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
}

.invoice-modal-close {
    background: none;
    border: none;
    font-size: 28px;
    font-weight: bold;
    color: #fff;
    cursor: pointer;
    padding: 0;
    width: 30px;
    height: 30px;
    line-height: 1;
    transition: color 0.3s;
}

.invoice-modal-close:hover {
    color: #ff6b6b;
}

.invoice-modal-body {
    padding: 20px;
    overflow-y: auto;
    flex: 1;
    background: #f5f5f5;
}

.invoice-modal-footer {
    padding: 20px;
    background: #f8f9fa;
    border-top: 1px solid #dee2e6;
    border-radius: 0 0 8px 8px;
    display: flex;
    justify-content: center;
    gap: 15px;
}

.invoice-action-btn {
    padding: 12px 30px;
    border: none;
    border-radius: 5px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.download-btn {
    background: #28a745;
    color: #fff;
}

.download-btn:hover {
    background: #218838;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.4);
}

.print-btn {
    background: #007bff;
    color: #fff;
}

.print-btn:hover {
    background: #0056b3;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 123, 255, 0.4);
}
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<script>
let currentOrderId = null;

function openInvoiceModal(orderId) {
    currentOrderId = orderId;
    const modal = document.getElementById('invoiceModal');
    const content = document.getElementById('invoiceContent');
    
    modal.style.display = 'block';
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

document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('invoiceModal');
    const closeBtn = document.querySelector('.invoice-modal-close');
    
    if (closeBtn) {
        closeBtn.onclick = function() {
            modal.style.display = 'none';
        };
    }
    
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    };
    
    // Download button - generates PDF
    const downloadBtn = document.getElementById('downloadInvoiceBtn');
    if (downloadBtn) {
        downloadBtn.onclick = function() {
            const invoiceElement = document.querySelector('#invoiceContent .invoice-wrapper');
            if (!invoiceElement) {
                alert('Invoice not loaded');
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
                html2canvas: { scale: 2, useCORS: true },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };
            
            html2pdf().set(opt).from(invoiceElement).save();
        };
    }
    
    // Print button - prints directly
    const printBtn = document.getElementById('printInvoiceBtn');
    if (printBtn) {
        printBtn.onclick = function() {
            const invoiceContent = document.getElementById('invoiceContent').innerHTML;
            
            // Create a hidden iframe for printing
            let printFrame = document.getElementById('printFrame');
            if (!printFrame) {
                printFrame = document.createElement('iframe');
                printFrame.id = 'printFrame';
                printFrame.style.display = 'none';
                document.body.appendChild(printFrame);
            }
            
            const frameDoc = printFrame.contentWindow.document;
            frameDoc.open();
            frameDoc.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Print Invoice</title>
                    <style>
                        @page { size: A4; margin: 0; }
                        body { margin: 0; padding: 0; }
                    </style>
                </head>
                <body>${invoiceContent}</body>
                </html>
            `);
            frameDoc.close();
            
            // Wait for content to load then print
            printFrame.contentWindow.focus();
            setTimeout(() => {
                printFrame.contentWindow.print();
            }, 100);
        };
    }
});
</script>
