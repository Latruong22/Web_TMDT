<?php
require_once '../../model/database.php';
require_once '../../model/product_model.php';
require_once '../../model/category_model.php';
session_start();
checkAccess('admin');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

/**
 * Xử lý upload ảnh sản phẩm (Legacy - for backward compatibility).
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

/**
 * Xử lý upload nhiều ảnh vào folder Sp{product_id}/ (NEW - Multiple images support)
 * 
 * @param int $product_id ID của sản phẩm
 * @param array $main_file Main image file từ $_FILES
 * @param array $detail_files Detail images từ $_FILES (multiple)
 * @return array ['success' => bool, 'path' => string, 'message' => string, 'uploaded_count' => int]
 */
function processMultipleProductImages($product_id, $main_file, $detail_files = null) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $max_size = 2 * 1024 * 1024; // 2MB
    
    // Tạo folder Sp{product_id}
    $folder_name = "Sp" . $product_id;
    $folder_path = __DIR__ . "/../../Images/product/" . $folder_name;
    
    if (!is_dir($folder_path)) {
        if (!mkdir($folder_path, 0755, true)) {
            return ['success' => false, 'message' => 'Không thể tạo thư mục sản phẩm.'];
        }
    }
    
    // Upload main image
    if ($main_file['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($main_file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'Tải ảnh chính thất bại.'];
        }
        
        if (!in_array($main_file['type'], $allowed_types)) {
            return ['success' => false, 'message' => 'Định dạng ảnh chính không hợp lệ.'];
        }
        
        if ($main_file['size'] > $max_size) {
            return ['success' => false, 'message' => 'Ảnh chính vượt quá 2MB.'];
        }
        
        $extension = pathinfo($main_file['name'], PATHINFO_EXTENSION);
        $main_filename = 'main.' . strtolower($extension);
        $main_target = $folder_path . '/' . $main_filename;
        
        if (!move_uploaded_file($main_file['tmp_name'], $main_target)) {
            return ['success' => false, 'message' => 'Không thể lưu ảnh chính.'];
        }
    }
    
    // Upload detail images (optional)
    $uploaded_count = 0;
    if ($detail_files && is_array($detail_files['tmp_name'])) {
        $detail_count = count($detail_files['tmp_name']);
        
        // Max 8 detail images
        if ($detail_count > 8) {
            return ['success' => false, 'message' => 'Chỉ được upload tối đa 8 ảnh chi tiết.'];
        }
        
        for ($i = 0; $i < $detail_count; $i++) {
            // Skip if no file uploaded at this index
            if ($detail_files['error'][$i] === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            
            if ($detail_files['error'][$i] !== UPLOAD_ERR_OK) {
                continue; // Skip failed uploads
            }
            
            if (!in_array($detail_files['type'][$i], $allowed_types)) {
                continue; // Skip invalid types
            }
            
            if ($detail_files['size'][$i] > $max_size) {
                continue; // Skip oversized files
            }
            
            $extension = pathinfo($detail_files['name'][$i], PATHINFO_EXTENSION);
            $detail_filename = 'detail_' . ($uploaded_count + 1) . '.' . strtolower($extension);
            $detail_target = $folder_path . '/' . $detail_filename;
            
            if (move_uploaded_file($detail_files['tmp_name'][$i], $detail_target)) {
                $uploaded_count++;
            }
        }
    }
    
    // Return main image path for database
    $main_image_path = "Images/product/" . $folder_name . "/main.jpg"; // Default to .jpg
    
    // Find actual main image extension
    foreach (['jpg', 'jpeg', 'png', 'gif', 'webp'] as $ext) {
        if (file_exists($folder_path . '/main.' . $ext)) {
            $main_image_path = "Images/product/" . $folder_name . "/main." . $ext;
            break;
        }
    }
    
    return [
        'success' => true,
        'path' => $main_image_path,
        'uploaded_count' => $uploaded_count,
        'message' => "Đã upload 1 ảnh chính" . ($uploaded_count > 0 ? " và {$uploaded_count} ảnh chi tiết" : "") . "."
    ];
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

        // Check main image required
        $main_file = $_FILES['main_image'] ?? ['error' => UPLOAD_ERR_NO_FILE];
        if ($main_file['error'] === UPLOAD_ERR_NO_FILE) {
            header('Location: ../../view/Admin/admin_product.php?msg=noimage');
            exit();
        }

        // Create product first to get product_id
        // Use temporary image path first
        $temp_image = 'Images/product/temp.jpg';
        $product_id = createProduct($name, $price, $manual_discount, $description, $temp_image, $stock, $category_id, $status);
        
        if (!$product_id) {
            header('Location: ../../view/Admin/admin_product.php?msg=error');
            exit();
        }

        // Now upload images to Sp{product_id} folder
        $detail_files = $_FILES['detail_images'] ?? null;
        $upload = processMultipleProductImages($product_id, $main_file, $detail_files);
        
        if (!$upload['success']) {
            // Delete product if image upload fails
            hardDeleteProduct($product_id);
            header('Location: ../../view/Admin/admin_product.php?msg=' . urlencode($upload['message']));
            exit();
        }

        // Update product with correct image path
        $image_path = $upload['path'];
        if (!updateProduct($product_id, $name, $price, $manual_discount, $description, $image_path, $stock, $category_id, $status)) {
            header('Location: ../../view/Admin/admin_product.php?msg=error');
            exit();
        }

        header('Location: ../../view/Admin/admin_product.php?msg=created');
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
