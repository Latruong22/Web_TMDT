<?php
require_once '../../model/database.php';
require_once '../../model/product_model.php';
require_once '../../model/category_model.php';
session_start();
checkAccess('admin');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

/**
 * Xử lý upload ảnh sản phẩm.
 */
function processProductImage($file, $existing_path = null) {
    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['success' => true, 'path' => $existing_path];
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Tải ảnh thất bại.'];
    }

    // Chỉ chấp nhận định dạng ảnh phổ biến để hạn chế upload file độc hại
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowed_types)) {
        return ['success' => false, 'message' => 'Định dạng ảnh không hợp lệ.'];
    }

    // Giới hạn tối đa 2MB giúp kiểm soát dung lượng và thời gian tải
    if ($file['size'] > 2 * 1024 * 1024) {
        return ['success' => false, 'message' => 'Ảnh vượt quá 2MB.'];
    }

    $upload_dir = realpath(__DIR__ . '/../../Images/product');
    if ($upload_dir === false) {
        $upload_dir = __DIR__ . '/../../Images/product';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
    }

    // Đặt tên file ngẫu nhiên nhằm tránh trùng lặp và lộ đường dẫn
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $file_name = uniqid('product_', true) . '.' . strtolower($extension);
    $target_path = rtrim($upload_dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $file_name;

    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        // Xóa ảnh cũ nếu có
        if ($existing_path) {
            $old_full_path = __DIR__ . '/../../' . $existing_path;
            if (is_file($old_full_path)) {
                @unlink($old_full_path);
            }
        }
        return ['success' => true, 'path' => 'Images/product/' . $file_name];
    }

    return ['success' => false, 'message' => 'Không thể lưu ảnh lên máy chủ.'];
}

switch ($action) {
    case 'add':
        // Chuẩn hóa dữ liệu đầu vào nhằm tránh ký tự dư thừa và mã độc
        $name = strip_tags(trim($_POST['name'] ?? ''));
        $price = isset($_POST['price']) ? (float)$_POST['price'] : 0;
        $manual_discount = isset($_POST['manual_discount']) ? (float)$_POST['manual_discount'] : 0;
        $description = trim(strip_tags($_POST['description'] ?? ''));
        $stock = isset($_POST['stock']) ? (int)$_POST['stock'] : 0;
        $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
        $status = $_POST['status'] ?? 'active';

        if (!$name || $price < 0 || $stock < 0 || !$category_id || $manual_discount < 0 || $manual_discount > 100) {
            header('Location: ../../view/Admin/admin_product.php?msg=invalid');
            exit();
        }

    $file = $_FILES['image'] ?? ['error' => UPLOAD_ERR_NO_FILE];
    $upload = processProductImage($file);
        if (!$upload['success']) {
            header('Location: ../../view/Admin/admin_product.php?msg=' . urlencode($upload['message']));
            exit();
        }

        $image_path = $upload['path'] ?? '';

        if (!$image_path) {
            header('Location: ../../view/Admin/admin_product.php?msg=noimage');
            exit();
        }

        if (createProduct($name, $price, $manual_discount, $description, $image_path, $stock, $category_id, $status)) {
            header('Location: ../../view/Admin/admin_product.php?msg=created');
            exit();
        }

        header('Location: ../../view/Admin/admin_product.php?msg=error');
        exit();

    case 'update':
        $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
        $name = strip_tags(trim($_POST['name'] ?? ''));
        $price = isset($_POST['price']) ? (float)$_POST['price'] : 0;
        $manual_discount = isset($_POST['manual_discount']) ? (float)$_POST['manual_discount'] : 0;
        $description = trim(strip_tags($_POST['description'] ?? ''));
        $stock = isset($_POST['stock']) ? (int)$_POST['stock'] : 0;
        $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
        $status = $_POST['status'] ?? 'active';
        $current_image = $_POST['current_image'] ?? null;

        // Không cho phép thao tác khi thiếu ID hoặc dữ liệu cốt lõi
        if (!$product_id || !$name || $price < 0 || $stock < 0 || !$category_id || $manual_discount < 0 || $manual_discount > 100) {
            header('Location: ../../view/Admin/admin_product.php?msg=invalid');
            exit();
        }

    $file = $_FILES['image'] ?? ['error' => UPLOAD_ERR_NO_FILE];
    $upload = processProductImage($file, $current_image);
        if (!$upload['success']) {
            header('Location: ../../view/Admin/admin_product.php?action=edit&id=' . $product_id . '&msg=' . urlencode($upload['message']));
            exit();
        }

        $image_path = $upload['path'] ?? '';

        if (updateProduct($product_id, $name, $price, $manual_discount, $description, $image_path, $stock, $category_id, $status)) {
            header('Location: ../../view/Admin/admin_product.php?msg=updated');
            exit();
        }

        header('Location: ../../view/Admin/admin_product.php?action=edit&id=' . $product_id . '&msg=error');
        exit();

    case 'delete':
    // Xóa mềm: chuyển trạng thái giúp vẫn lưu dữ liệu lịch sử bán hàng
    $product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($product_id && deleteProduct($product_id)) {
            header('Location: ../../view/Admin/admin_product.php?msg=deleted');
            exit();
        }
        header('Location: ../../view/Admin/admin_product.php?msg=error');
        exit();

    case 'hard-delete':
    // Xóa cứng: xoá hẳn cả bản ghi và file ảnh khỏi hệ thống
    $product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $image_path = $_GET['image'] ?? '';
        if ($product_id && hardDeleteProduct($product_id)) {
            if ($image_path) {
                $full_path = __DIR__ . '/../../' . $image_path;
                if (is_file($full_path)) {
                    @unlink($full_path);
                }
            }
            header('Location: ../../view/Admin/admin_product.php?msg=removed');
            exit();
        }
        header('Location: ../../view/Admin/admin_product.php?msg=error');
        exit();

    default:
        header('Location: ../../view/Admin/admin_product.php');
        exit();
}
?>
