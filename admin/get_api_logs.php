<?php
$logFile = '../logs/delhivery_api.log';

if (file_exists($logFile)) {
    $logs = file_get_contents($logFile);
    $logLines = explode("\n", $logs);
    $recentLogs = array_slice($logLines, -20); // Get last 20 lines
    
    echo '<div style="font-family: monospace; font-size: 12px;">';
    foreach ($recentLogs as $line) {
        if (!empty(trim($line))) {
            echo '<div style="margin-bottom: 5px; padding: 2px 5px; background: white; border-radius: 3px;">';
            echo htmlspecialchars($line);
            echo '</div>';
        }
    }
    echo '</div>';
} else {
    echo '<p class="text-muted">No API logs found yet.</p>';
}
?>

