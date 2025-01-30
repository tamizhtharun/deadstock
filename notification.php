<?php
require_once('header.php');

// Check if user is logged in
if (!isset($_SESSION['user_session']['id'])) {
    header('Location: login.php');
    exit;
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['action'])) {
        echo json_encode(['error' => 'No action specified']);
        exit;
    }

    switch($_POST['action']) {
        case 'mark_read':
            if (!isset($_POST['notification_id'])) {
                echo json_encode(['error' => 'No notification ID provided']);
                exit;
            }
            $notification_id = $_POST['notification_id'];
            $sql = "UPDATE notifications SET is_read = 1 
                    WHERE id = ? AND recipient_id = ? AND recipient_type = 'user'";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ii', $notification_id, $_SESSION['user_session']['id']);
            $success = $stmt->execute();
            echo json_encode(['success' => $success]);
            exit;

        case 'mark_all_read':
            $sql = "UPDATE notifications SET is_read = 1 
                    WHERE recipient_id = ? AND recipient_type = 'user'";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $_SESSION['user_session']['id']);
            $success = $stmt->execute();
            echo json_encode(['success' => $success]);
            exit;

        case 'delete_all':
            $sql = "DELETE FROM notifications 
                    WHERE recipient_id = ? AND recipient_type = 'user'";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $_SESSION['user_session']['id']);
            $success = $stmt->execute();
            echo json_encode(['success' => $success]);
            exit;
    }
}

// Function to fetch notifications
function getNotifications($recipient_id) {
    global $conn;
    
    $sql = "SELECT id, title, message as content, type, is_read, 
            CASE 
                WHEN created_at > NOW() - INTERVAL 1 HOUR 
                    THEN CONCAT(FLOOR(TIMESTAMPDIFF(MINUTE, created_at, NOW())), ' minutes ago')
                WHEN created_at > NOW() - INTERVAL 1 DAY 
                    THEN CONCAT(FLOOR(TIMESTAMPDIFF(HOUR, created_at, NOW())), ' hours ago')
                WHEN created_at > NOW() - INTERVAL 7 DAY 
                    THEN CONCAT(FLOOR(TIMESTAMPDIFF(DAY, created_at, NOW())), ' days ago')
                ELSE DATE_FORMAT(created_at, '%b %d, %Y')
            END as time
            FROM notifications 
            WHERE recipient_type = 'user' 
            AND recipient_id = ? 
            ORDER BY created_at DESC";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $recipient_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notifications[] = [
            'id' => $row['id'],
            'type' => $row['type'],
            'title' => $row['title'],
            'content' => $row['content'],
            'detailedContent' => formatDetailedContent($row),
            'time' => $row['time'],
            'unread' => !$row['is_read']
        ];
    }
    
    return $notifications;
}

// Function to format detailed content
function formatDetailedContent($notification) {
    $content = "<div class='detail-content'>";
    $content .= "<p>" . htmlspecialchars($notification['content']) . "</p>";
    
    // Add custom content based on notification type
    switch($notification['type']) {
        case 'orders':
            $content .= "
                <div class='detail-actions mt-4'>
                    <button class='action-btn primary' onclick='viewOrder({$notification['id']})'>
                        View Order Details
                    </button>
                </div>";
            break;
            
        case 'bidding':
            $content .= "
                <div class='detail-actions mt-4'>
                    <button class='action-btn primary' onclick='viewAuction({$notification['id']})'>
                        View Auction
                    </button>
                    <button class='action-btn secondary ml-2' onclick='placeBid({$notification['id']})'>
                        Place Bid
                    </button>
                </div>";
            break;
            
        case 'delivery':
            $content .= "
                <div class='detail-actions mt-4'>
                    <button class='action-btn primary' onclick='trackDelivery({$notification['id']})'>
                        Track Package
                    </button>
                </div>";
            break;
    }
    
    $content .= "</div>";
    return $content;
}

// Fetch notifications for the current user
$notifications = getNotifications($_SESSION['user_session']['id']);
?>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Reset and base styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: #f5f7fa;
            color: #1a1a1a;
            line-height: 1.6;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            background-color: white;
            min-height: 100vh;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-bottom: 20px !important;
            padding-bottom : 20px !important;
        }

        /* Header styles */
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 2rem;
            background-color: white;
            border-bottom: 1px solid #e5e7eb;
        }

        h1 {
            font-size: 1.5rem;
            color: #1a1a1a;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .header-actions {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        /* Button styles */
        .action-btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.2s ease;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .action-btn.primary {
            background-color: #3b82f6;
            color: white;
        }

        .action-btn.secondary {
            background-color: #e5e7eb;
            color: #4b5563;
        }

        .action-btn:hover {
            opacity: 0.9;
        }

        /* Filter styles */
        .filters {
            display: flex;
            gap: 1rem;
            padding: 0.75rem 2rem;
            background-color: white;
            border-bottom: 1px solid #e5e7eb;
            overflow-x: auto;
        }

        .filter-btn {
            padding: 0.4rem 1.2rem;
            border: none;
            border-radius: 2rem;
            background-color: #e5e7eb;
            color: #4b5563;
            cursor: pointer;
            transition: all 0.2s ease;
            white-space: nowrap;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .filter-btn.active {
            background-color: #3b82f6;
            color: white;
        }

        /* Content layout */
        .content-wrapper {
            display: grid;
            grid-template-columns: minmax(300px, 450px) 1fr;
            height: calc(100vh - 110px);
        }

        /* Notification list styles */
        .notifications {
            border-right: 1px solid #e5e7eb;
            overflow-y: auto;
        }

        .notification {
            padding: 1rem 1.5rem;
            cursor: pointer;
            transition: background-color 0.2s ease;
            border-bottom: 1px solid #e5e7eb;
            position: relative;
        }

        .notification.unread {
            background-color: #f0f7ff;
        }

        .notification.selected {
            background-color: #f0f7ff;
            border-left: 3px solid #3b82f6;
        }

        .notification-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.25rem;
        }

        .notification-type {
            font-size: 0.75rem;
            text-transform: uppercase;
            color: #6b7280;
            font-weight: 600;
        }

        .notification-time {
            font-size: 0.75rem;
            color: #9ca3af;
        }

        .notification-title {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .notification-content {
            color: #4b5563;
            font-size: 0.9rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* Detail view styles */
        .notification-detail {
            padding: 2rem;
            overflow-y: auto;
        }

        .detail-header {
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 1rem;
            margin-bottom: 1rem;
        }

        .detail-type {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            background-color: #f3f4f6;
            border-radius: 1rem;
            font-size: 0.75rem;
            text-transform: uppercase;
            color: #6b7280;
            margin-bottom: 0.5rem;
        }

        .detail-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .detail-content {
            font-size: 0.95rem;
            line-height: 1.6;
            color: #374151;
        }

        .detail-actions {
            margin-top: 1.5rem;
            display: flex;
            gap: 0.5rem;
        }

        /* Utility classes */
        .mt-4 { margin-top: 1rem; }
        .ml-2 { margin-left: 0.5rem; }

        /* Responsive styles */
        @media (max-width: 1024px) {
            .content-wrapper {
                grid-template-columns: 1fr;
            }
            
            .notification-detail {
                display: none;
            }

            .notifications {
                border-right: none;
            }
        }

        @media (max-width: 640px) {
            header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
                padding: 1rem;
            }

            .header-actions {
                width: 100%;
                flex-wrap: wrap;
            }

            .filters {
                padding: 0.75rem 1rem;
            }

            .notification {
                padding: 1rem;
            }
        }
    </style>

    <div class="container">
        <header>
            <h1>
                <i class="fas fa-bell"></i>
                Notifications
            </h1>
            <div class="header-actions">
                <button id="readAllBtn" class="action-btn secondary">Mark All as Read</button>
                <!-- <button id="deleteAllBtn" class="action-btn secondary">Delete All</button> -->
                <div class="notification-count">
                    <span id="unreadCount">0</span> unread
                </div>
            </div>
        </header>

        <nav class="filters">
            <button class="filter-btn active" data-category="all">All</button>
            <button class="filter-btn" data-category="unread">Unread</button>
            <button class="filter-btn" data-category="orders">Orders</button>
            <button class="filter-btn" data-category="bidding">Bidding</button>
            <button class="filter-btn" data-category="delivery">Delivery</button>
        </nav>

        <div class="content-wrapper">
            <div class="notifications" id="notificationList">
                <!-- Notifications will be inserted here by JavaScript -->
            </div>
            <div class="notification-detail" id="notificationDetail">
                <!-- Notification details will be inserted here by JavaScript -->
            </div>
        </div>
    </div>

    <script>
        // Initialize notifications from PHP
        const notifications = <?php echo json_encode($notifications); ?>;
        
        // DOM Elements
        const notificationList = document.getElementById('notificationList');
        const filterButtons = document.querySelectorAll('.filter-btn');
        const unreadCountElement = document.getElementById('unreadCount');
        const notificationDetail = document.getElementById('notificationDetail');
        const readAllBtn = document.getElementById('readAllBtn');
        const deleteAllBtn = document.getElementById('deleteAllBtn');

        // Current filter and selected notification
        let currentFilter = 'all';
        let selectedNotificationId = null;

        // Update unread count
        function updateUnreadCount() {
    const unreadCount = notifications.filter(notification => notification.unread).length;
    unreadCountElement.textContent = unreadCount;
}

        // Show notification detail
        function showNotificationDetail(notification) {
            const detailContent = `
                <div class="detail-header">
                    <span class="detail-type">${notification.type}</span>
                    <h2 class="detail-title">${notification.title}</h2>
                    <div class="detail-meta">
                        <span>${notification.time}</span>
                    </div>
                </div>
                <div class="detail-body">
                    ${notification.detailedContent}
                </div>
            `;
            
            notificationDetail.innerHTML = detailContent;
            notificationDetail.style.display = 'block';
        }

        // Show empty state
        function showEmptyState() {
            notificationDetail.innerHTML = `
                <div style="text-align: center; padding: 2rem; color: #6b7280;">
                    <i class="fas fa-bell" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                    <h3>Select a notification to view details</h3>
                    <p>Click on any notification from the list to view its full content</p>
                </div>
            `;
        }

        // Render notifications based on filter
        function renderNotifications() {
            notificationList.innerHTML = '';
            
            const filteredNotifications = notifications.filter(notification => {
                if (currentFilter === 'all') return true;
                if (currentFilter === 'unread') return notification.unread;
                return notification.type === currentFilter;
            });

            filteredNotifications.forEach(notification => {
                const notificationElement = document.createElement('div');
                notificationElement.className = `notification ${notification.unread ? 'unread' : ''} ${notification.id === selectedNotificationId ? 'selected' : ''}`;
                
                notificationElement.innerHTML = `
                    <div class="notification-header">
                        <span class="notification-type">${notification.type}</span>
                        <span class="notification-time">${notification.time}</span>
                    </div>
                    <div class="notification-title">${notification.title}</div>
                    <div class="notification-content">${notification.content}</div>
                `;

                notificationElement.addEventListener('click', async () => {
    // Update selected state
    document.querySelectorAll('.notification').forEach(el => el.classList.remove('selected'));
    notificationElement.classList.add('selected');
    selectedNotificationId = notification.id;

    // Mark as read if unread
    if (notification.unread) {
        try {
            const response = await fetch('notification.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=mark_read&notification_id=${notification.id}`
            });

            const result = await response.json();
            if (result.success) {
                // Update the notifications array
                const index = notifications.findIndex(n => n.id === notification.id);
                if (index !== -1) {
                    notifications[index].unread = false;
                }
                
                // Update UI
                notificationElement.classList.remove('unread');
                updateUnreadCount();
                renderNotifications(); // Re-render to update all views
            }
        } catch (error) {
            console.error('Error marking notification as read:', error);
        }
    }

    // Show detail
    showNotificationDetail(notification);

    // Show detail panel on mobile
    if (window.innerWidth <= 1024) {
        notificationDetail.style.display = 'block';
        notificationList.style.display = 'none';
    }
});

                notificationList.appendChild(notificationElement);
            });

            if (filteredNotifications.length === 0) {
                notificationList.innerHTML = `
                    <div style="text-align: center; padding: 2rem; color: #6b7280;">
                        <i class="fas fa-inbox" style="font-size: 2rem; margin-bottom: 1rem;"></i>
                        <p>No notifications found</p>
                    </div>
                `;
            }
        }

        // Initialize filter buttons
        filterButtons.forEach(button => {
            button.addEventListener('click', () => {
                filterButtons.forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');
                currentFilter = button.dataset.category;
                renderNotifications();
            });
        });

        // Read all notifications
        readAllBtn.addEventListener('click', async function() {
    try {
        const response = await fetch('notification.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=mark_all_read'
        });

        const result = await response.json();
        if (result.success) {
            // Update the notifications array
            notifications.forEach(notification => {
                notification.unread = false;
            });
            
            // Update UI
            renderNotifications(); // This will update all notifications in the view
            updateUnreadCount();
            
            // If a notification is selected, update its detail view
            if (selectedNotificationId) {
                const selectedNotification = notifications.find(n => n.id === selectedNotificationId);
                if (selectedNotification) {
                    showNotificationDetail(selectedNotification);
                }
            }
        }
    } catch (error) {
        console.error('Error marking all notifications as read:', error);
    }
});

        // Delete all notifications
        // deleteAllBtn.addEventListener('click', async () => {
        //     if (confirm('Are you sure you want to delete all notifications?')) {
        //         try {
        //             const response = await fetch('notification.php', {
        //                 method: 'POST',
        //                 headers: {
        //                     'Content-Type': 'application/x-www-form-urlencoded',
        //                 },
        //                 body: 'action=delete_all'
        //             });

        //             const result = await response.json();
        //             if (result.success) {
        //                 notifications.length = 0;
        //                 updateUnreadCount();
        //                 renderNotifications();
        //                 showEmptyState();
        //             }
        //         } catch (error) {
        //             console.error('Error deleting all notifications:', error);
        //         }
        //     }
        // });

        // Handle back button on mobile
        function addBackButton() {
            const backButton = document.createElement('button');
            backButton.className = 'action-btn secondary';
            backButton.innerHTML = '<i class="fas fa-arrow-left"></i> Back to List';
            backButton.style.marginBottom = '1rem';
            backButton.onclick = () => {
                notificationDetail.style.display = 'none';
                notificationList.style.display = 'block';
            };
            notificationDetail.insertBefore(backButton, notificationDetail.firstChild);
        }

        // Add resize handler for mobile view
        window.addEventListener('resize', () => {
            if (window.innerWidth <= 1024) {
                if (!document.querySelector('.notification-detail button')) {
                    addBackButton();
                }
            }
        });

        // Utility functions for notification actions
        function viewOrder(orderId) {
            window.location.href = `order-details.php?id=${orderId}`;
        }

        function viewAuction(auctionId) {
            window.location.href = `auction-details.php?id=${auctionId}`;
        }

        function trackDelivery(deliveryId) {
            window.location.href = `tracking.php?id=${deliveryId}`;
        }

        function placeBid(auctionId) {
            window.location.href = `place-bid.php?id=${auctionId}`;
        }

        // Initialize the page
        updateUnreadCount();
        renderNotifications();
        showEmptyState();

        // Add back button if on mobile
        if (window.innerWidth <= 1024) {
            addBackButton();
        }
    </script>