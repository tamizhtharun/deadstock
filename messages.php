<?php

class MessageSystem {
    // Set message in session
    public static function set($text, $type = 'success') {
        $_SESSION['flash_messages'][] = [
            'text' => $text,
            'type' => $type
        ];
    }

    // Display messages and clear session
    public static function display() {
        if (!empty($_SESSION['flash_messages'])) {
            echo '<script>';
            foreach ($_SESSION['flash_messages'] as $message) {
                echo "showMessage('{$message['text']}', '{$message['type']}');";
            }
            echo '</script>';
            unset($_SESSION['flash_messages']);
        }
    }
}
?>