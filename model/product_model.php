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
 */
function createProduct($name, $price, $discount, $description, $image_path, $stock, $category_id, $status = 'active') {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO products (name, price, manual_discount, description, image, stock, category_id, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('sddssiis', $name, $price, $discount, $description, $image_path, $stock, $category_id, $status);
    return $stmt->execute();
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
?>
