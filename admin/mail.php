<?php
include 'header.php';
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
?>
<link href="https://cdn.jsdelivr.net/npm/@selectjs/select2@4.1.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
<?php
// Initialize message variables at the top
$success_message = '';
$error_message = '';
// Email configuration
define('SELLER_EMAIL', 'support@destock.in');
define('SELLER_EMAIL_PASSWORD', '3q7Y4a0bnfni');
define('USER_EMAIL', 'support@destock.in');
define('USER_EMAIL_PASSWORD', '3q7Y4a0bnfni');

// Function to send email with different sender emails and proper attachment handling
function sendEmail($recipient_email, $subject, $message, $recipient_type, $attachment = null)
{
    $mail = new PHPMailer(true);
    $success = true;

    try {
        // Server settings
        $mail->isSMTP();
                    $mail->Host       = 'smtp.zoho.in';      // try smtp.zoho.in, or smtp.zoho.com if that fails
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'support@destock.in';
                    $mail->Password   = '3q7Y4a0bnfni';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // 'tls'
                    $mail->Port       = 587;

        // Set sender based on recipient type
        if ($recipient_type === 'seller') {
            $mail->Username = SELLER_EMAIL;
            $mail->Password = SELLER_EMAIL_PASSWORD;
            $mail->setFrom(SELLER_EMAIL, 'DeadStock Seller Support');
        } else {
            $mail->Username = USER_EMAIL;
            $mail->Password = USER_EMAIL_PASSWORD;
            $mail->setFrom(USER_EMAIL, 'DeadStock Customer Support');
        }

        // Add recipient
        $mail->addAddress($recipient_email);

        // Handle attachment with original filename
        if ($attachment && is_array($attachment)) {
            if (
                isset($attachment['tmp_name']) &&
                isset($attachment['name']) &&
                file_exists($attachment['tmp_name'])
            ) {
                // Sanitize filename to prevent security issues
                $original_filename = basename($attachment['name']); // Get base name to prevent directory traversal
                $original_filename = preg_replace("/[^a-zA-Z0-9._-]/", "", $original_filename); // Remove special characters

                // Add attachment with original filename
                $mail->addAttachment(
                    $attachment['tmp_name'],     // Temporary file path
                    $original_filename           // Original filename
                );
            }
        }

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $message;

        $mail->send();
    } catch (Exception $e) {
        $success = false;
        error_log("Email sending failed: " . $e->getMessage());
    }

    return $success;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_email'])) {
    $recipient_type = $_POST['recipient_type'];
    $recipients = isset($_POST['recipients']) ? $_POST['recipients'] : [];
    $subject = $_POST['subject'];
    $message = $_POST['message'];

    // Get recipient emails
    $recipient_emails = [];

    if ($recipients) {
        if (in_array('all', $recipients)) {
            // Send to all users/sellers
            $table = ($recipient_type === 'seller') ? 'sellers' : 'users';
            $email_field = ($recipient_type === 'seller') ? 'seller_email' : 'email';
            $stmt = $pdo->query("SELECT $email_field FROM $table");
            while ($row = $stmt->fetch()) {
                $recipient_emails[] = $row[$email_field];
            }
        } else {
            // Send to selected users/sellers
            $placeholders = str_repeat('?,', count($recipients) - 1) . '?';
            $table = ($recipient_type === 'seller') ? 'sellers' : 'users';
            $email_field = ($recipient_type === 'seller') ? 'seller_email' : 'email';
            $id_field = ($recipient_type === 'seller') ? 'seller_id' : 'id';
            $stmt = $pdo->prepare("SELECT $email_field FROM $table WHERE $id_field IN ($placeholders)");
            $stmt->execute($recipients);
            while ($row = $stmt->fetch()) {
                $recipient_emails[] = $row[$email_field];
            }
        }
    }

    // Handle file upload
    $attachment = null;
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $attachment = $_FILES['attachment']; // Pass the entire $_FILES array element
    }

    // Send emails individually
    $all_sent = true;
    foreach ($recipient_emails as $recipient_email) {
        if (!sendEmail($recipient_email, $subject, $message, $recipient_type, $attachment)) {
            $all_sent = false;
        } else {
            // Store in database for each recipient
            $stmt = $pdo->prepare("INSERT INTO emails (sender_id, recipient_id, recipient_type, subject, message, status) VALUES (?, ?, ?, ?, ?, 'sent')");
            $stmt->execute([$_SESSION['admin_session']['id'], $recipient_email, $recipient_type, $subject, $message]);
        }
    }

    if ($all_sent) {
        $success_message = "All emails sent successfully!";
    } else {
        $error_message = "Failed to send some emails!";
    }
}

// Fetch all sent emails with recipient details
$stmt = $pdo->query("SELECT 
    e.id,
    e.subject,
    e.message,
    e.status,
    e.created_at,
    e.recipient_type,
    COUNT(e.id) as recipient_count,
    GROUP_CONCAT(
        CASE 
            WHEN e.recipient_type = 'seller' THEN s.seller_name 
            ELSE u.username 
        END
    ) as all_recipients
    FROM emails e 
    LEFT JOIN sellers s ON e.recipient_id = s.seller_id AND e.recipient_type = 'seller'
    LEFT JOIN users u ON e.recipient_id = u.id AND e.recipient_type = 'user'
    GROUP BY e.subject, e.message, DATE(e.created_at)
    ORDER BY e.created_at DESC
    LIMIT 8");
$emails = $stmt->fetchAll();

// Fetch sellers and users for select options
$sellers = $pdo->query("SELECT seller_id, seller_name, seller_email FROM sellers ORDER BY seller_name")->fetchAll();
$users = $pdo->query("SELECT id, username, email FROM users ORDER BY username")->fetchAll();
?>

<!-- Content Header -->
<section class="content-header">
    <div class="content-header-left">
        <h1>Email Management</h1>
        <div>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <!-- Compose Email Section -->
        <div class="col-md-8">
            <div class="email-container">
                <form method="POST" enctype="multipart/form-data" id="emailForm">
                    <input type="hidden" name="recipient_type" id="recipientTypeInput" value="seller">

                    <!-- Recipient Selection -->
                    <div class="recipient-selector">
                        <div class="type-selector">
                            <button type="button" class="type-btn active" data-type="seller">
                                <i class="fas fa-store"></i> Sellers
                            </button>
                            <button type="button" class="type-btn" data-type="user">
                                <i class="fas fa-users"></i> Users
                            </button>
                        </div>

                        <div class="recipients-container">
                            <div class="search-container">
                                <input type="text" class="search-input" placeholder="Search by name or email...">
                                <button type="button" class="btn btn-link btn-sm p-0 mt-2 select-all-btn">Select All</button>
                            </div>

                            <!-- Seller List -->
                            <div id="sellerList" class="recipient-list">
                                <?php foreach ($sellers as $seller): ?>
                                    <div class="recipient-item" data-id="<?php echo $seller['seller_id']; ?>">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong><?php echo $seller['seller_name']; ?></strong>
                                                <div class="text-muted small"><?php echo $seller['seller_email']; ?></div>
                                            </div>
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" name="recipients[]"
                                                    value="<?php echo $seller['seller_id']; ?>"
                                                    id="seller<?php echo $seller['seller_id']; ?>">
                                                <label class="custom-control-label" for="seller<?php echo $seller['seller_id']; ?>"></label>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- User List -->
                            <div id="userList" class="recipient-list" style="display: none;">
                                <?php foreach ($users as $user): ?>
                                    <div class="recipient-item" data-id="<?php echo $user['id']; ?>">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong><?php echo $user['username']; ?></strong>
                                                <div class="text-muted small"><?php echo $user['email']; ?></div>
                                            </div>
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" name="recipients[]"
                                                    value="<?php echo $user['id']; ?>"
                                                    id="user<?php echo $user['id']; ?>">
                                                <label class="custom-control-label" for="user<?php echo $user['id']; ?>"></label>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Compose Section -->
                    <div class="compose-section">
                        <div class="mb-3">
                            <input type="text" name="subject" class="form-control form-control-lg" placeholder="Email Subject" required>
                        </div>

                        <div class="mb-3">
                            <div class="email-editor">
                                <textarea name="message" id="emailMessage" class="form-control" rows="10" placeholder="Write your message here..." required></textarea>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="attachment" name="attachment">
                                <label class="custom-file-label" for="attachment">Attach Files</label>
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" name="send_email" class="btn btn-primary btn-lg">
                                <i class="fas fa-paper-plane me-2"></i> Send Email
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Email History Section -->
        <div class="col-md-4">
            <div class="email-history">
                <h4 class="mb-4">Recent Emails</h4>
                <?php foreach ($emails as $email): ?>
                    <div class="history-item">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="mb-0"><?php echo $email['subject']; ?></h6>
                            <span class="status-badge <?php echo $email['status'] === 'sent' ? 'status-sent' : 'status-failed'; ?>">
                                <?php echo ucfirst($email['status']); ?>
                            </span>
                        </div>
                        <div class="text-muted small mb-2">
                            To: <?php echo $email['recipient_count']; ?> <?php echo ucfirst($email['recipient_type']); ?>(s)
                        </div>
                        <div class="text-muted small">
                            <?php echo date('M d, Y h:i A', strtotime($email['created_at'])); ?>
                        </div>
                        <!-- <button class="btn btn-link btn-sm p-0 mt-2 view-email" 
                                data-id="<?php echo $email['id']; ?>"
                                data-subject="<?php echo htmlspecialchars($email['subject']); ?>"
                                data-message="<?php echo htmlspecialchars($email['message']); ?>"
                                data-recipients="<?php echo htmlspecialchars($email['all_recipients']); ?>"
                                data-created="<?php echo date('M d, Y h:i A', strtotime($email['created_at'])); ?>"
                                data-toggle="modal" 
                                data-target="#emailViewModal">
                            View Details
                        </button> -->
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- Email View Modal -->
<div class="modal fade" id="emailViewModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title">Email Details</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="email-details">
                    <h4 class="email-subject mb-3"></h4>
                    <div class="email-meta mb-4">
                        <div class="meta-item">
                            <strong>Sent:</strong> <span class="email-date"></span>
                        </div>
                    </div>
                    <div class="email-content"></div>
                </div>
            </div>
        </div>
    </div>
</div>


<style>
    .modal-backdrop {
        background-color: none !important;
    }

    .modal-backdrop.show {
        opacity: 1 !important;
    }

    /* Enhance modal appearance */
    .modal-content {
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        border: none;
        border-radius: 8px;
    }

    .modal-header {
        padding: 1.5rem 1.5rem 0.5rem;
        align-items: center;
    }

    .modal-header .btn-close {
        padding: 0;
        background: none;
        border: none;
        font-size: 1.5rem;
        line-height: 1;
        color: #6c757d;
        opacity: 0.75;
        transition: opacity 0.15s;
        margin: -1rem -1rem -1rem auto;
    }

    .modal-header .btn-close:hover {
        opacity: 1;
        color: #000;
    }

    /* Animation for smoother modal appearance */
    .modal.fade .modal-dialog {
        transition: transform 0.2s ease-out;
    }

    .modal.fade.show .modal-dialog {
        transform: none;
    }
</style>
<!-- style -->
<style>
    .email-container {
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
        padding: 25px;
        margin-bottom: 20px;
    }

    .recipient-selector {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .type-selector {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
    }

    .type-btn {
        flex: 1;
        padding: 12px;
        border: 2px solid #e9ecef;
        border-radius: 6px;
        background: white;
        font-weight: 500;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .type-btn.active {
        background: #4361ee;
        color: white;
        border-color: #4361ee;
    }

    /* Scrollable recipient list */
    .recipient-list {
        max-height: 230px;
        overflow-y: auto !important;
        border: 0px solid #e9ecef;
        border-radius: 6px;
        /* z-index: -1000 !important; */

    }

    .recipient-item {
        padding: 8px 12px;
        border-bottom: 1px solid #e9ecef;
        cursor: pointer;
    }

    .recipient-item:last-child {
        border-bottom: none;
    }

    .recipient-item:hover {
        background: #f8f9fa;
    }

    .recipient-item.selected {
        background: #e7f0ff;
    }

    /* Preserve original email history design */
    .email-history {
        background: white;
        border-radius: 10px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
        padding: 20px;
        font-size: 14px !important;
    }

    .history-item {
        padding: 15px;
        border-bottom: 1px solid #f0f0f0;
    }

    .history-item:hover {
        background: #f8f9fa;
    }

    .status-badge {
        padding: 5px 10px;
        border-radius: 15px;
        font-size: 12px;
        font-weight: 500;
    }

    .status-sent {
        background: #d4edda;
        color: #155724;
    }

    .status-failed {
        background: #f8d7da;
        color: #721c24;
    }

    /* Custom scrollbar for better visibility */
    .recipient-list::-webkit-scrollbar {
        width: 8px;
    }

    .recipient-list::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }

    .recipient-list::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }

    .recipient-list::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    .note-editor {
        border: 1px solid #e9ecef;
        border-radius: 6px;
    }


    .recipient-selector {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .search-container {
        position: sticky;
        top: 0;
        background: #f8f9fa;
        padding: 10px;
        border-bottom: 1px solid #e9ecef;
        z-index: 1;
        display: flex;
        justify-content: space-between;
    }

    .search-input {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #ced4da;
        border-radius: 4px;
        margin-bottom: 10px;
    }

    .recipients-container {
        height: 300px;
        border: 1px solid #e9ecef;
        border-radius: 6px;
        background: white;
    }

    .recipient-list {
        height: 100%;
        overflow-y: auto;
    }

    .recipient-item {
        padding: 10px 15px;
        border-bottom: 1px solid #e9ecef;
        background: white;
    }

    .recipient-item:last-child {
        border-bottom: none;
    }

    .recipient-item:hover {
        background: #f8f9fa;
    }

    .recipient-item.selected {
        background: #e7f0ff;
    }

    /* Custom scrollbar for the list */
    .recipient-list::-webkit-scrollbar {
        width: 6px;
    }

    .recipient-list::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .recipient-list::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 3px;
    }

    .recipient-list::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    .select-all-btn {
        padding: 10px !important;
        outline: none;
        border: 0px !important;
        margin-left: 50px !important;
        decoration: none !important;
    }

    .note-editor {
        width: 100% !important;
        /* Adjust width as per requirement */
        max-width: 800px;
        /* Optional: Set a max width */
    }
</style>


<!-- Add these before your other scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@selectjs/select2@4.1.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>

<!-- Page specific script -->
<script>
    $(document).ready(function() {
        $('.view-email').click(function() {
            const subject = $(this).data('subject');
            const message = $(this).data('message');
            const created = $(this).data('created');
            const recipients = $(this).data('recipients').split(',');

            // Update modal content
            $('.email-subject').text(subject);
            $('.email-date').text(created);
            $('.email-content').html(message);

            // Create recipients list
            const recipientsList = $('.recipients-list');
            recipientsList.empty();

            recipients.forEach(recipient => {
                recipientsList.append(`
                <div class="recipient-chip">
                    ${recipient.trim()}
                </div>
            `);
            });
        });
    });


    // Modified JavaScript for email management system
    $(document).ready(function() {
        // Check if email message exists before initializing Summernote
        if ($('#emailMessage').length) {
            $('#emailMessage').summernote({
                height: 300,
                toolbar: [
                    ['style', ['bold', 'italic', 'underline', 'clear']],
                    ['font', ['strikethrough']],
                    ['para', ['ul', 'ol']],
                    ['insert', ['link']]
                ],
                placeholder: 'Write your message here...',
                callbacks: {
                    onInit: function() {
                        $('.note-editor').addClass('border-0');
                    }
                }
            });
        }

        // Only attach event handlers if elements exist
        if ($('.type-btn').length) {
            $('.type-btn').off('click').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                const type = $(this).data('type');

                $('.type-btn').removeClass('active');
                $(this).addClass('active');

                $('#recipientTypeInput').val(type);

                if (type === 'seller') {
                    $('#userList').hide();
                    $('#sellerList').show();
                } else {
                    $('#sellerList').hide();
                    $('#userList').show();
                }
            });
        }

        // Check for recipient items
        if ($('.recipient-item').length) {
            $('.recipient-item').off('click').on('click', function(e) {
                if (!$(e.target).is('input[type="checkbox"], label')) {
                    const checkbox = $(this).find('input[type="checkbox"]');
                    checkbox.prop('checked', !checkbox.prop('checked')).trigger('change');
                }
            });
        }

        // Check for checkboxes
        if ($('.recipient-item input[type="checkbox"]').length) {
            $('.recipient-item input[type="checkbox"]').off('change').on('change', function() {
                $(this).closest('.recipient-item').toggleClass('selected', $(this).prop('checked'));
            });
        }

        // Check for select all button
        if ($('.select-all-btn').length) {
            $('.select-all-btn').off('click').on('click', function() {
                const visibleList = $('.recipient-list:visible');
                const checkboxes = visibleList.find('input[type="checkbox"]');
                const allChecked = checkboxes.length === checkboxes.filter(':checked').length;

                checkboxes.prop('checked', !allChecked).trigger('change');
            });
        }

        // Check for search input
        if ($('#recipientSearch').length) {
            $('#recipientSearch').on('input', function() {
                const searchTerm = $(this).val().toLowerCase();
                const activeList = $('.recipient-list:visible');

                activeList.find('.recipient-item').each(function() {
                    const name = $(this).find('strong').text().toLowerCase();
                    const email = $(this).find('.text-muted').text().toLowerCase();
                    const matches = name.includes(searchTerm) || email.includes(searchTerm);
                    $(this).toggle(matches);
                });
            });
        }

        // Check for file input
        if ($('.custom-file-input').length) {
            $('.custom-file-input').on('change', function() {
                const fileName = $(this).val().split('\\').pop();
                $(this).next('.custom-file-label').html(fileName || 'Attach Files');
            });
        }
    });
    // Search functionality
    $('.search-input').on('input', function() {
        const searchTerm = $(this).val().toLowerCase();
        const activeList = $('.recipient-list:visible');

        activeList.find('.recipient-item').each(function() {
            const name = $(this).find('strong').text().toLowerCase();
            const email = $(this).find('.text-muted').text().toLowerCase();
            const matches = name.includes(searchTerm) || email.includes(searchTerm);
            $(this).toggle(matches);
        });
    });
</script>

<?php include 'footer.php'; ?>