<?php
require_once 'database.php';

/**
 * Lấy danh sách sản phẩm cùng tên danh mục.
 */
function getAllProducts() {
    global $conn;
    $sql = "SELECT p.product_id, p.name, p.price, p.manual_discount, p.description, p.image, p.stock, p.category_id, p.status, p.created_at,
                   c.name AS category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.category_id
            ORDER BY p.created_at DESC";
    $result = $conn->query($sql);

    $products = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
    return $products;
}

/**
 * Lấy thông tin chi tiết của một sản phẩm theo ID.
 */
function getProductById($product_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT product_id, name, price, manual_discount, description, image, stock, category_id, status FROM products WHERE product_id = ?");
    $stmt->bind_param('i', $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result ? $result->fetch_assoc() : null;
}

/**
 * Thêm sản phẩm mới.
 * Returns product_id if successful, false otherwise.
 */
function createProduct($name, $price, $discount, $description, $image_path, $stock, $category_id, $status = 'active') {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO products (name, price, manual_discount, description, image, stock, category_id, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('sddssiis', $name, $price, $discount, $description, $image_path, $stock, $category_id, $status);
    
    if ($stmt->execute()) {
        return $conn->insert_id; // Return the new product_id
    }
    return false;
}

/**
 * Cập nhật dữ liệu của sản phẩm.
 */
function updateProduct($product_id, $name, $price, $discount, $description, $image_path, $stock, $category_id, $status) {
    global $conn;
    $stmt = $conn->prepare("UPDATE products SET name = ?, price = ?, manual_discount = ?, description = ?, image = ?, stock = ?, category_id = ?, status = ? WHERE product_id = ?");
    $stmt->bind_param('sddssiisi', $name, $price, $discount, $description, $image_path, $stock, $category_id, $status, $product_id);
    return $stmt->execute();
}

/**
 * Đặt trạng thái sản phẩm thành inactive (xóa mềm).
 */
function deleteProduct($product_id) {
    global $conn;
    $stmt = $conn->prepare("UPDATE products SET status = 'inactive' WHERE product_id = ?");
    $stmt->bind_param('i', $product_id);
    return $stmt->execute();
}

/**
 * Xóa vĩnh viễn một sản phẩm khỏi cơ sở dữ liệu.
 */
function hardDeleteProduct($product_id) {
    global $conn;
    $stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
    $stmt->bind_param('i', $product_id);
    return $stmt->execute();
}

/**
 * Lấy danh sách sản phẩm với filter cho trang admin.
 * Filters: category_id, status, search (by name), price_min, price_max
 */
function getAdminProducts(array $filters = []) {
    global $conn;
    
    $sql = "SELECT p.product_id, p.name, p.price, p.manual_discount, p.description, p.image, p.stock, p.category_id, p.status, p.created_at,
                   c.name AS category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.category_id
            WHERE 1 = 1";
    
    $types = '';
    $params = [];
    
    // Category filter
    if (!empty($filters['category']) && $filters['category'] !== 'all') {
        $sql .= " AND p.category_id = ?";
        $types .= 'i';
        $params[] = (int) $filters['category'];
    }
    
    // Status filter
    if (!empty($filters['status']) && $filters['status'] !== 'all') {
        $sql .= " AND p.status = ?";
        $types .= 's';
        $params[] = $filters['status'];
    }
    
    // Search by product name
    if (!empty($filters['search'])) {
        $sql .= " AND p.name LIKE ?";
        $types .= 's';
        $params[] = '%' . $filters['search'] . '%';
    }
    
    // Price range filter
    if (!empty($filters['price_min']) && is_numeric($filters['price_min'])) {
        $sql .= " AND p.price >= ?";
        $types .= 'd';
        $params[] = (float) $filters['price_min'];
    }
    
    if (!empty($filters['price_max']) && is_numeric($filters['price_max'])) {
        $sql .= " AND p.price <= ?";
        $types .= 'd';
        $params[] = (float) $filters['price_max'];
    }
    
    $sql .= " ORDER BY p.created_at DESC";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log('getAdminProducts prepare failed: ' . $conn->error);
        return [];
    }
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $products = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
    
    $stmt->close();
    return $products;
}

/**
 * Lấy thống kê sản phẩm cho dashboard admin.
 */
function getProductStats() {
    global $conn;
    
    $sql = "SELECT
                COUNT(*) AS total_products,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) AS active_products,
                SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) AS inactive_products,
                SUM(CASE WHEN stock > 0 THEN 1 ELSE 0 END) AS in_stock_products,
                SUM(CASE WHEN stock = 0 THEN 1 ELSE 0 END) AS out_of_stock_products
            FROM products";
    
    $result = $conn->query($sql);
    if (!$result) {
        return [
            'total_products' => 0,
            'active_products' => 0,
            'inactive_products' => 0,
            'in_stock_products' => 0,
            'out_of_stock_products' => 0,
        ];
    }
    
    $row = $result->fetch_assoc();
    return [
        'total_products' => (int) ($row['total_products'] ?? 0),
        'active_products' => (int) ($row['active_products'] ?? 0),
        'inactive_products' => (int) ($row['inactive_products'] ?? 0),
        'in_stock_products' => (int) ($row['in_stock_products'] ?? 0),
        'out_of_stock_products' => (int) ($row['out_of_stock_products'] ?? 0),
    ];
}
?>
