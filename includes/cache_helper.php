<?php
/**
 * Section-Based Caching Helper
 * 
 * Provides granular caching for individual page sections.
 */

/**
 * Render content with caching support
 * 
 * @param string $key Cache key identifier
 * @param int $ttl Time-to-live in seconds
 * @param callable $callback Function that generates the content
 */
function renderCached($key, $ttl, $callback) {
    $cacheDir = __DIR__ . '/../cache/';
    $cacheFile = $cacheDir . $key . '.html';
    
    // Create cache directory if it doesn't exist
    if (!file_exists($cacheDir)) {
        mkdir($cacheDir, 0755, true);
    }
    
    // Serve from cache if valid
    if (file_exists($cacheFile) && time() - filemtime($cacheFile) < $ttl) {
        echo file_get_contents($cacheFile);
        return;
    }
    
    // Generate and cache
    ob_start();
    $callback();
    $content = ob_get_clean();
    file_put_contents($cacheFile, $content);
    echo $content;
}

/**
 * Clear a specific cache section
 * 
 * @param string $key Cache key to clear
 */
function clearCache($key) {
    $cacheFile = __DIR__ . '/../cache/' . $key . '.html';
    if (file_exists($cacheFile)) {
        unlink($cacheFile);
    }
}

/**
 * Clear all cache sections
 */
function clearAllCache() {
    $cacheDir = __DIR__ . '/../cache/';
    $files = glob($cacheDir . '*.html');
    foreach ($files as $file) {
        if (is_file($file)) {
            unlink($file);
        }
    }
}
?>
