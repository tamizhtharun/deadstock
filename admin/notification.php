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
    $recipient_ids = $_POST['recipient_ids']; // Can be single ID or array of IDs
    $title = $_POST['title'];
    $message = $_POST['message'];
    $type = $_POST['type'];
    
    if (is_array($recipient_ids)) {
        foreach ($recipient_ids as $recipient_id) {
            createNotification($recipient_id, $recipient_type, $title, $message, $type);
        }
        $success_message = "Notifications sent to multiple recipients!";
    } else {
        if (createNotification($recipient_ids, $recipient_type, $title, $message, $type)) {
            $success_message = "Notification sent successfully!";
        } else {
            $error_message = "Failed to send notification!";
        }
    }
}

// Fetch all notifications
$stmt = $pdo->query("SELECT * FROM notifications ORDER BY created_at DESC");
$notifications = $stmt->fetchAll();
?>

<!-- Content Header -->
<section class="content-header">
    <h1>Notification Management</h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <!-- Notification Form -->
        <div class="col-md-6">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Send New Notification</h3>
                </div>
                <form method="POST">
                    <div class="box-body">
                        <div class="form-group">
                            <label>Recipient Type</label>
                            <select name="recipient_type" class="form-control" required>
                                <option value="seller">Seller</option>
                                <option value="user">User</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Recipient ID(s)</label>
                            <select name="recipient_ids[]" class="form-control select2" multiple>
                                <!-- Populate with actual user/seller IDs -->
                                <?php
                                // Fetch sellers
                                $stmt = $pdo->query("SELECT id, name FROM sellers");
                                while ($row = $stmt->fetch()) {
                                    echo "<option value='{$row['id']}'>Seller: {$row['name']}</option>";
                                }
                                
                                // Fetch users
                                $stmt = $pdo->query("SELECT id, name FROM users");
                                while ($row = $stmt->fetch()) {
                                    echo "<option value='{$row['id']}'>User: {$row['name']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Title</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Message</label>
                            <textarea name="message" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="form-group">
                            <label>Type</label>
                            <select name="type" class="form-control">
                                <option value="info">Info</option>
                                <option value="success">Success</option>
                                <option value="warning">Warning</option>
                                <option value="error">Error</option>
                            </select>
                        </div>
                    </div>
                    <div class="box-footer">
                        <button type="submit" name="send_notification" class="btn btn-primary">Send Notification</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Notification History -->
        <div class="col-md-6">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Notification History</h3>
                </div>
                <div class="box-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Recipient</th>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Read</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($notifications as $notification): ?>
                            <tr>
                                <td><?php echo date('Y-m-d H:i', strtotime($notification['created_at'])); ?></td>
                                <td><?php echo $notification['recipient_type'] . ' #' . $notification['recipient_id']; ?></td>
                                <td><?php echo $notification['title']; ?></td>
                                <td>
                                    <span class="label label-<?php 
                                        echo match($notification['type']) {
                                            'success' => 'success',
                                            'warning' => 'warning',
                                            'error' => 'danger',
                                            default => 'info'
                                        };
                                    ?>">
                                        <?php echo $notification['type']; ?>
                                    </span>
                                </td>
                                <td><?php echo $notification['is_read'] ? 'Yes' : 'No'; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Page specific script -->
<script>
$(function() {
    $('.select2').select2();
});
</script>

<?php include 'footer.php'; ?>