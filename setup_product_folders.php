<?php
/**
 * SETUP PRODUCT FOLDERS - Tự động tạo folders Sp{id} cho tất cả products
 * 
 * Script này sẽ:
 * 1. Lấy tất cả products từ database
 * 2. Tạo folder Sp{product_id}/ cho mỗi product
 * 3. Copy main image vào folder đó
 * 4. Báo cáo kết quả
 */

require_once 'model/database.php';
$pdo = getDatabaseConnection();

// Get all products
$stmt = $pdo->query("SELECT product_id, name, image FROM products ORDER BY product_id");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<!DOCTYPE html>
<html>
<head>
    <title>Setup Product Folders</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 3px solid #007bff; padding-bottom: 10px; }
        .product { background: #f8f9fa; padding: 15px; margin: 10px 0; border-left: 4px solid #007bff; border-radius: 5px; }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .warning { color: #ffc107; font-weight: bold; }
        .info { color: #17a2b8; }
        .button { background: #007bff; color: white; padding: 12px 30px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; margin: 20px 0; }
        .button:hover { background: #0056b3; }
        .stats { background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 20px 0; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🔧 Setup Product Folders</h1>
        <p class='info'>Found <strong>" . count($products) . "</strong> products in database</p>
";

$created = 0;
$copied = 0;
$errors = 0;

foreach ($products as $product) {
    $product_id = $product['product_id'];
    $name = htmlspecialchars($product['name']);
    $image = $product['image'];
    
    echo "<div class='product'>";
    echo "<strong>Product #{$product_id}:</strong> {$name}<br>";
    
    // Tạo folder name
    $folder_name = "Sp" . $product_id;
    $folder_path = __DIR__ . "/Images/product/" . $folder_name;
    
    // Kiểm tra folder đã tồn tại chưa
    if (!is_dir($folder_path)) {
        // Tạo folder
        if (mkdir($folder_path, 0755, true)) {
            echo "<span class='success'>✅ Created folder: {$folder_name}/</span><br>";
            $created++;
        } else {
            echo "<span class='error'>❌ Failed to create folder: {$folder_name}/</span><br>";
            $errors++;
            echo "</div>";
            continue;
        }
    } else {
        echo "<span class='warning'>⚠️ Folder already exists: {$folder_name}/</span><br>";
    }
    
    // Copy main image vào folder
    if (!empty($image)) {
        // Image path từ database (có thể là Images/product/xxx.jpg)
        $source_image = __DIR__ . "/" . $image;
        
        // Lấy filename từ path
        $filename = basename($image);
        $dest_image = $folder_path . "/" . $filename;
        
        // Check source exists
        if (file_exists($source_image)) {
            // Copy vào folder mới
            if (!file_exists($dest_image)) {
                if (copy($source_image, $dest_image)) {
                    echo "<span class='success'>✅ Copied image: {$filename}</span><br>";
                    $copied++;
                } else {
                    echo "<span class='error'>❌ Failed to copy: {$filename}</span><br>";
                    $errors++;
                }
            } else {
                echo "<span class='info'>ℹ️ Image already exists: {$filename}</span><br>";
            }
        } else {
            echo "<span class='error'>❌ Source image not found: <code>{$image}</code></span><br>";
            $errors++;
        }
    } else {
        echo "<span class='warning'>⚠️ No image in database</span><br>";
    }
    
    // List files in folder
    $files = glob($folder_path . "/*.{jpg,jpeg,png,gif,webp}", GLOB_BRACE);
    $file_count = count($files);
    echo "<span class='info'>📁 Folder has {$file_count} image(s)</span><br>";
    
    if ($file_count > 0) {
        echo "<small style='color: #666;'>";
        foreach ($files as $file) {
            echo "  • " . basename($file) . "<br>";
        }
        echo "</small>";
    }
    
    echo "</div>";
}

echo "<div class='stats'>
        <h3>📊 Summary</h3>
        <p>✅ Folders created: <strong>{$created}</strong></p>
        <p>📋 Images copied: <strong>{$copied}</strong></p>
        <p>❌ Errors: <strong>{$errors}</strong></p>
    </div>";

echo "<div class='info' style='padding: 20px; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 5px; margin: 20px 0;'>
        <h3>⚠️ NEXT STEPS:</h3>
        <ol>
            <li><strong>Upload thêm ảnh detail:</strong> Mỗi folder Sp{id}/ nên có 3-4 ảnh sản phẩm từ góc độ khác nhau</li>
            <li><strong>Tên file:</strong> Đặt tên: <code>product_1.jpg</code>, <code>product_2.jpg</code>, <code>product_3.jpg</code>...</li>
            <li><strong>Test:</strong> Mở product_detail.php?id=16 để xem gallery</li>
        </ol>
        <p><strong>📁 Upload ảnh vào:</strong> <code>C:\\xampp\\htdocs\\Web_TMDT\\Images\\product\\Sp{id}\\</code></p>
    </div>";

echo "<a href='view/User/product_detail.php?id=" . $products[0]['product_id'] . "' class='button'>🔍 Test First Product</a>";
echo "<a href='view/User/product_list.php' class='button' style='background: #28a745; margin-left: 10px;'>📋 View Product List</a>";

echo "
    </div>
</body>
</html>";
?>
