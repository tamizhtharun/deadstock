<?php
include 'header.php';

// Function to create a new notification
function createNotification($recipient_id, $recipient_type, $title, $message, $type = 'info') {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO notifications (recipient_id, recipient_type, title, message, type) VALUES (?, ?, ?, ?, ?)");
    return $stmt->execute([$recipient_id, $recipient_type, $title, $message, $type]);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_notification'])) {
    $recipient_type = $_POST['recipient_type'];
    $recipients = isset($_POST['recipients']) ? $_POST['recipients'] : [];
    $title = $_POST['title'];
    $message = $_POST['message'];
    $type = $_POST['type'];
    
    $success = true;
    foreach ($recipients as $recipient_id) {
        if (!createNotification($recipient_id, $recipient_type, $title, $message, $type)) {
            $success = false;
        }
    }
    
    if ($success) {
        $success_message = "Notifications sent successfully!";
    } else {
        $error_message = "Some notifications failed to send!";
    }
}

// Fetch all notifications with recipient details
$stmt = $pdo->query("SELECT 
    n.*,
    COUNT(n.id) as recipient_count, 
    SUM(n.is_read = '1') as read_count,
    CASE 
        WHEN n.recipient_type = 'seller' THEN s.seller_name 
        ELSE u.username 
    END as recipient_name
    FROM notifications n
    LEFT JOIN sellers s ON n.recipient_id = s.seller_id AND n.recipient_type = 'seller'
    LEFT JOIN users u ON n.recipient_id = u.id AND n.recipient_type = 'user'
    GROUP BY n.created_at
    ORDER BY n.created_at DESC
    LIMIT 7");
$notifications = $stmt->fetchAll();

// Fetch sellers and users for recipient selection
$sellers = $pdo->query("SELECT seller_id, seller_name, seller_email FROM sellers ORDER BY seller_name")->fetchAll();
$users = $pdo->query("SELECT id, username, email FROM users ORDER BY username")->fetchAll();
?>

<!-- Content Header -->
<section class="content-header">
    <div class="content-header-left">
        <h1>Notification Management</h1>
    </div>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <!-- Compose Notification Section -->
        <div class="col-md-8">
            <div class="notification-container">
                <form method="POST" id="notificationForm">
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
                                <input type="text" class="search-input" placeholder="Search recipients...">
                                <button type="button" class="btn btn-link btn-sm select-all-btn">Select All</button>
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
                            <input type="text" name="title" class="form-control form-control-lg" placeholder="Notification Title" required>
                        </div>

                        <div class="mb-3">
                            <textarea name="message" class="form-control" rows="5" placeholder="Write your notification message here..." required></textarea>
                        </div>

                        <div class="mb-3">
                            <select name="type" class="form-control">
                                <option value="info">Info</option>
                                <option value="success">Success</option>
                                <option value="warning">Warning</option>
                                <option value="error">Error</option>
                            </select>
                        </div>

                        <div class="text-end">
                            <button type="submit" name="send_notification" class="btn btn-primary btn-lg">
                                <i class="fas fa-bell me-2"></i> Send Notification
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Notification History Section -->
        <div class="col-md-4">
            <div class="notification-history">
                <h4 class="mb-4">Recent Notifications</h4>
                <?php foreach ($notifications as $notification): ?>
                    <div class="history-item">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="mb-0"><?php echo $notification['title']; ?></h6>
                            <span class="status-badge status-<?php echo $notification['type']; ?>">
                                <?php echo ucfirst($notification['type']); ?>
                            </span>
                        </div>
                        <div class="text-muted small mb-2">
                            To: <?php echo $notification['recipient_count'] ?> <?php echo $notification['recipient_type']; ?>(s)
                        </div>
                        <div class="text-muted small mb-2">
                            Read by: <?php echo $notification['read_count'] ?> <?php echo $notification['recipient_type']; ?>(s)
                        </div>
                        <div class="text-muted small">
                            <?php echo date('M d, Y h:i A', strtotime($notification['created_at'])); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<style>
.notification-container {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 0 20px rgba(0,0,0,0.05);
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

.recipients-container {
    height: 300px;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    background: white;
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
    align-items: center;
}

.search-input {
    flex: 1;
    padding: 8px 12px;
    border: 1px solid #ced4da;
    border-radius: 4px;
}

.recipient-list {
    height: calc(100% - 60px);
    overflow-y: auto;
}

.recipient-item {
    padding: 10px 15px;
    border-bottom: 1px solid #e9ecef;
    transition: background-color 0.2s;
}

.recipient-item:hover {
    background: #f8f9fa;
}

.recipient-item.selected {
    background: #e7f0ff;
}

.notification-history {
    background: white;
    border-radius: 10px;
    box-shadow: 0 0 20px rgba(0,0,0,0.05);
    padding: 20px;
}

.history-item {
    padding: 15px;
    border-bottom: 1px solid #f0f0f0;
}

.status-badge {
    padding: 5px 10px;
    border-radius: 15px;
    font-size: 12px;
    font-weight: 500;
}

.status-info {
    background: #e7f5ff;
    color: #0066cc;
}

.status-success {
    background: #d4edda;
    color: #155724;
}

.status-warning {
    background: #fff3cd;
    color: #856404;
}

.status-error {
    background: #f8d7da;
    color: #721c24;
}

.select-all-btn {
    white-space: nowrap;
    margin-left: 10px;
}

/* Custom scrollbar */
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
</style>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@selectjs/select2@4.1.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
<script>
$(document).ready(function() {
    // Toggle recipient type
    $('.type-btn').click(function() {
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

    // Recipient item selection
    $('.recipient-item').click(function(e) {
        if (!$(e.target).is('input[type="checkbox"], label')) {
            const checkbox = $(this).find('input[type="checkbox"]');
            checkbox.prop('checked', !checkbox.prop('checked')).trigger('change');
        }
    });

    // Checkbox change handling
    $('.recipient-item input[type="checkbox"]').change(function() {
        $(this).closest('.recipient-item').toggleClass('selected', $(this).prop('checked'));
    });

    // Select all functionality
    $('.select-all-btn').click(function() {
        const visibleList = $('.recipient-list:visible');
        const checkboxes = visibleList.find('input[type="checkbox"]');
        const allChecked = checkboxes.length === checkboxes.filter(':checked').length;
        
        checkboxes.prop('checked', !allChecked).trigger('change');
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
});
</script>

<?php include 'footer.php'; ?>