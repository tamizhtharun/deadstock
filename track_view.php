<?php
include("db_connection.php");

function trackPageView($pageId, $pageTitle) {
    global $pdo;
    
    $today = date('Y-m-d');
    $now = time();
    $viewTimeout = 30 * 60; // 30 minutes
    
    // Generate a unique visitor ID
    $visitorId = getVisitorId();
    
    // Check if this page has been viewed recently by this visitor
    $cookieName = 'page_view_' . $pageId;
    if (!isset($_COOKIE[$cookieName]) || (int)$_COOKIE[$cookieName] < ($now - $viewTimeout)) {
        // Record the view in the database
        $stmt = $pdo->prepare("
            INSERT INTO page_views (page_id, page_title, view_count, view_date) 
            VALUES (?, ?, 1, ?) 
            ON DUPLICATE KEY UPDATE view_count = view_count + 1
        ");
        $stmt->execute([$pageId, $pageTitle, $today]);
        
        // Set cookie to prevent immediate recounting
        setcookie($cookieName, $now, time() + 86400, '/', '', true, true); // Secure and HTTP-only
    }
}

function getVisitorId() {
    if (!isset($_COOKIE['visitor_id'])) {
        $visitorId = uniqid('', true);
        setcookie('visitor_id', $visitorId, time() + 365 * 24 * 60 * 60, '/', '', true, true);
    } else {
        $visitorId = $_COOKIE['visitor_id'];
    }
    
    // Fallback to IP address if cookie is not set (e.g., cookies disabled)
    if (empty($visitorId)) {
        $visitorId = $_SERVER['REMOTE_ADDR'];
    }
    
    return $visitorId;
}