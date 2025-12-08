<?php
/**
 * Modern Pagination Helper for Seller Panel
 * Provides server-side pagination functionality
 */

class ModernPagination {
    private $pdo;
    private $itemsPerPage;
    private $currentPage;
    private $totalItems;
    private $totalPages;
    
    public function __construct($pdo, $itemsPerPage = 10) {
        $this->pdo = $pdo;
        $this->itemsPerPage = $itemsPerPage;
        $this->currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    }
    
    /**
     * Get pagination data for a query
     */
    public function paginate($query, $countQuery, $params = []) {
        // Get total count
        $stmt = $this->pdo->prepare($countQuery);
        $stmt->execute($params);
        $this->totalItems = $stmt->fetchColumn();
        $this->totalPages = ceil($this->totalItems / $this->itemsPerPage);
        
        // Ensure current page is within bounds
        $this->currentPage = min($this->currentPage, max(1, $this->totalPages));
        
        // Calculate offset
        $offset = ($this->currentPage - 1) * $this->itemsPerPage;
        
        // Get paginated results
        $paginatedQuery = $query . " LIMIT :limit OFFSET :offset";
        $stmt = $this->pdo->prepare($paginatedQuery);
        
        // Bind original params
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        // Bind pagination params
        $stmt->bindValue(':limit', $this->itemsPerPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'data' => $results,
            'pagination' => [
                'current_page' => $this->currentPage,
                'total_pages' => $this->totalPages,
                'total_items' => $this->totalItems,
                'items_per_page' => $this->itemsPerPage,
                'start_item' => $offset + 1,
                'end_item' => min($offset + $this->itemsPerPage, $this->totalItems)
            ]
        ];
    }
    
    /**
     * Render pagination HTML
     */
    public function renderPagination($baseUrl = '') {
        if ($this->totalPages <= 1) {
            return '';
        }
        
        $html = '<div class="pagination-container">';
        $html .= '<div class="pagination-info">';
        $html .= 'Showing ' . (($this->currentPage - 1) * $this->itemsPerPage + 1) . ' to ';
        $html .= min($this->currentPage * $this->itemsPerPage, $this->totalItems) . ' of ' . $this->totalItems . ' entries';
        $html .= '</div>';
        
        $html .= '<ul class="pagination">';
        
        // Previous button
        if ($this->currentPage > 1) {
            $html .= '<li><a href="' . $this->buildUrl($baseUrl, $this->currentPage - 1) . '">Previous</a></li>';
        } else {
            $html .= '<li class="disabled"><span>Previous</span></li>';
        }
        
        // Page numbers
        $start = max(1, $this->currentPage - 2);
        $end = min($this->totalPages, $this->currentPage + 2);
        
        if ($start > 1) {
            $html .= '<li><a href="' . $this->buildUrl($baseUrl, 1) . '">1</a></li>';
            if ($start > 2) {
                $html .= '<li class="disabled"><span>...</span></li>';
            }
        }
        
        for ($i = $start; $i <= $end; $i++) {
            if ($i == $this->currentPage) {
                $html .= '<li class="active"><span>' . $i . '</span></li>';
            } else {
                $html .= '<li><a href="' . $this->buildUrl($baseUrl, $i) . '">' . $i . '</a></li>';
            }
        }
        
        if ($end < $this->totalPages) {
            if ($end < $this->totalPages - 1) {
                $html .= '<li class="disabled"><span>...</span></li>';
            }
            $html .= '<li><a href="' . $this->buildUrl($baseUrl, $this->totalPages) . '">' . $this->totalPages . '</a></li>';
        }
        
        // Next button
        if ($this->currentPage < $this->totalPages) {
            $html .= '<li><a href="' . $this->buildUrl($baseUrl, $this->currentPage + 1) . '">Next</a></li>';
        } else {
            $html .= '<li class="disabled"><span>Next</span></li>';
        }
        
        $html .= '</ul>';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Build URL with page parameter
     */
    private function buildUrl($baseUrl, $page) {
        $params = $_GET;
        $params['page'] = $page;
        
        $queryString = http_build_query($params);
        return $baseUrl . '?' . $queryString;
    }
    
    /**
     * Get current page
     */
    public function getCurrentPage() {
        return $this->currentPage;
    }
    
    /**
     * Get total pages
     */
    public function getTotalPages() {
        return $this->totalPages;
    }
    
    /**
     * Get items per page
     */
    public function getItemsPerPage() {
        return $this->itemsPerPage;
    }
    
    /**
     * Set items per page
     */
    public function setItemsPerPage($itemsPerPage) {
        $this->itemsPerPage = max(1, intval($itemsPerPage));
    }
}
?>
