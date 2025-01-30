<?php require_once('header.php');?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
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
}

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

.action-btn {
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 0.5rem;
    cursor: pointer;
    transition: all 0.2s ease;
    font-weight: 500;
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

.notification-count {
    background-color: #e5e7eb;
    padding: 0.5rem 1rem;
    border-radius: 1rem;
    font-size: 0.9rem;
    font-weight: 500;
}

.filters {
    display: flex;
    gap: 1rem;
    padding: 0.75rem 2rem;
    background-color: white;
    border-bottom: 1px solid #e5e7eb;
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

.filter-btn:hover {
    background-color: #d1d5db;
}

.filter-btn.active {
    background-color: #3b82f6;
    color: white;
}

.content-wrapper {
    display: grid;
    grid-template-columns: minmax(300px, 450px) 1fr;
    height: calc(100vh - 110px); /* Subtract header and filters height */
}

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

.notification:hover {
    background-color: #f9fafb;
}

.notification.unread {
    background-color: #f0f7ff;
}

.notification.selected {
    background-color: #f0f7ff;
    border-left: 3px solid #3b82f6;
}

.notification.unread::before {
    content: '';
    position: absolute;
    left: 0.5rem;
    top: 50%;
    transform: translateY(-50%);
    width: 0.5rem;
    height: 0.5rem;
    background-color: #3b82f6;
    border-radius: 50%;
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
    font-size: 0.95rem;
}

.notification-content {
    color: #4b5563;
    font-size: 0.9rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.notification-detail {
    padding: 2rem;
    overflow-y: auto;
    background-color: white;
}

.detail-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
    color: #9ca3af;
    text-align: center;
    padding: 2rem;
}

.detail-placeholder svg {
    width: 48px;
    height: 48px;
    margin-bottom: 1rem;
    color: #d1d5db;
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

.detail-meta {
    display: flex;
    gap: 1rem;
    color: #6b7280;
    font-size: 0.85rem;
}

.detail-body {
    font-size: 0.95rem;
    line-height: 1.6;
    color: #374151;
}

.detail-body p {
    margin-bottom: 1rem;
}

.detail-body ul {
    margin: 1rem 0;
    padding-left: 1.5rem;
}

.detail-body li {
    margin-bottom: 0.5rem;
}

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

    .filters {
        padding: 0.75rem 1rem;
        overflow-x: auto;
    }

    .notification {
        padding: 1rem;
    }
}
</style>    
</head>
<body>
    <div class="container">
        <header>
            <h1>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"></path>
                    <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"></path>
                </svg>
                Notifications
            </h1>
            <div class="header-actions">
                <button id="readAllBtn" class="action-btn secondary">Mark All as Read</button>
                <button id="deleteAllBtn" class="action-btn secondary">Delete All</button>
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
// Sample notification data
const notifications = [
    {
        id: 1,
        type: 'orders',
        title: 'Order #12345 Confirmed',
        content: 'Your order has been confirmed and is being processed. We will notify you once your order ships.',
        detailedContent: `
            <p>Dear Customer,</p>
            <p>We are pleased to confirm that your order #12345 has been successfully processed and is now being prepared for shipment.</p>
            <p>Order Details:</p>
            <ul>
                <li>Order Number: #12345</li>
                <li>Order Date: March 15, 2024</li>
                <li>Expected Delivery: March 18-20, 2024</li>
            </ul>
            <p>We will send you another notification when your order ships with tracking information.</p>
            <p>Thank you for shopping with us!</p>
        `,
        time: '2 minutes ago',
        unread: true
    },
    {
        id: 2,
        type: 'bidding',
        title: 'New Bid on iPhone 13',
        content: 'Someone has placed a higher bid of $650 on iPhone 13.',
        detailedContent: `
            <p>A new bid has been placed on your watched item:</p>
            <p>Item: iPhone 13 - 128GB - Midnight</p>
            <p>Previous Bid: $600</p>
            <p>New Bid: $650</p>
            <p>Time Remaining: 2 hours 15 minutes</p>
            <p>Would you like to place a higher bid to stay in the lead?</p>
            <button class="action-btn primary" onclick="alert('Bidding feature coming soon!')">Place Bid</button>
        `,
        time: '15 minutes ago',
        unread: true
    },
    {
        id: 3,
        type: 'delivery',
        title: 'Package Out for Delivery',
        content: 'Your package with order #12340 is out for delivery.',
        detailedContent: `
            <p>Great news! Your package is out for delivery today.</p>
            <p>Tracking Details:</p>
            <ul>
                <li>Order Number: #12340</li>
                <li>Carrier: Express Delivery</li>
                <li>Expected Delivery: Today by 8:00 PM</li>
                <li>Shipping Address: 123 Main St, Anytown, ST 12345</li>
            </ul>
            <p>Track your package in real-time using the button below:</p>
            <button class="action-btn primary" onclick="alert('Tracking feature coming soon!')">Track Package</button>
        `,
        time: '1 hour ago',
        unread: false
    },
    {
        id: 4,
        type: 'orders',
        title: 'Order #12339 Delivered',
        content: 'Your order has been delivered successfully.',
        detailedContent: `
            <p>Your order has been successfully delivered!</p>
            <p>Order Details:</p>
            <ul>
                <li>Order Number: #12339</li>
                <li>Delivery Date: March 14, 2024</li>
                <li>Delivery Address: 123 Main St, Anytown, ST 12345</li>
            </ul>
            <p>Please take a moment to rate your delivery experience:</p>
            <button class="action-btn primary" onclick="alert('Rating feature coming soon!')">Rate Delivery</button>
        `,
        time: '2 hours ago',
        unread: false
    },
    {
        id: 5,
        type: 'bidding',
        title: 'Auction Ending Soon',
        content: 'The auction for MacBook Pro ends in 1 hour.',
        detailedContent: `
            <p>Hurry! The auction you're watching is ending soon:</p>
            <p>Item: MacBook Pro 14" - M2 Pro</p>
            <p>Current Bid: $1,850</p>
            <p>Your Maximum Bid: $1,800</p>
            <p>Time Remaining: 1 hour</p>
            <p>Don't miss out on this opportunity!</p>
            <button class="action-btn primary" onclick="alert('Bidding feature coming soon!')">Increase Bid</button>
        `,
        time: '3 hours ago',
        unread: true
    }
];

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
        <div class="detail-placeholder">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
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

        notificationElement.addEventListener('click', () => {
            // Update selected state
            document.querySelectorAll('.notification').forEach(el => el.classList.remove('selected'));
            notificationElement.classList.add('selected');
            selectedNotificationId = notification.id;

            // Mark as read
            if (notification.unread) {
                notification.unread = false;
                notificationElement.classList.remove('unread');
                updateUnreadCount();
            }

            // Show detail
            showNotificationDetail(notification);
        });

        notificationList.appendChild(notificationElement);
    });

    if (filteredNotifications.length === 0) {
        notificationList.innerHTML = `
            <div class="notification" style="text-align: center; color: #6b7280;">
                No notifications found
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
readAllBtn.addEventListener('click', () => {
    notifications.forEach(notification => {
        notification.unread = false;
    });
    updateUnreadCount();
    renderNotifications();
});

// Delete all notifications
deleteAllBtn.addEventListener('click', () => {
    if (confirm('Are you sure you want to delete all notifications?')) {
        notifications.length = 0;
        updateUnreadCount();
        renderNotifications();
        showEmptyState();
    }
});

// Initial render
updateUnreadCount();
renderNotifications();
showEmptyState();
    </script>
</body>
</html>
<?php include 'footer.php'; ?>