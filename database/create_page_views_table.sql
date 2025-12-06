CREATE TABLE IF NOT EXISTS page_views (
    id INT AUTO_INCREMENT PRIMARY KEY,
    page_id VARCHAR(50) NOT NULL,
    page_title VARCHAR(255) NOT NULL,
    view_count INT DEFAULT 1,
    view_date DATE NOT NULL,
    UNIQUE KEY unique_page_date (page_id, view_date)
);
