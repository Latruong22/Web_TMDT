<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Product Detail Debug</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            padding: 20px; 
            background: #f5f5f5; 
        }
        .container { 
            max-width: 1400px; 
            margin: 0 auto; 
            background: white; 
            padding: 30px; 
            border-radius: 10px; 
            box-shadow: 0 2px 20px rgba(0,0,0,0.1); 
        }
        h1 { 
            color: #333; 
            border-bottom: 4px solid #007bff; 
            padding-bottom: 15px; 
            margin-bottom: 30px; 
        }
        h2 { 
            color: #555; 
            margin: 30px 0 15px 0; 
            padding: 10px; 
            background: #f8f9fa; 
            border-left: 4px solid #007bff; 
        }
        .info-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); 
            gap: 20px; 
            margin: 20px 0; 
        }
        .info-box { 
            padding: 20px; 
            background: #f8f9fa; 
            border-radius: 8px; 
            border: 2px solid #dee2e6; 
        }
        .info-box h3 { 
            color: #007bff; 
            margin-bottom: 10px; 
            font-size: 18px; 
        }
        .info-box p { 
            margin: 8px 0; 
            font-size: 14px; 
        }
        .info-box strong { 
            color: #333; 
        }
        .success { 
            color: #28a745; 
            font-weight: bold; 
        }
        .error { 
            color: #dc3545; 
            font-weight: bold; 
        }
        .warning { 
            color: #ffc107; 
            font-weight: bold; 
        }
        .image-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); 
            gap: 15px; 
            margin: 20px 0; 
        }
        .image-item { 
            border: 2px solid #dee2e6; 
            border-radius: 8px; 
            overflow: hidden; 
            background: white; 
        }
        .image-item img { 
            width: 100%; 
            height: 200px; 
            object-fit: contain; 
            background: #f8f9fa; 
        }
        .image-item p { 
            padding: 10px; 
            font-size: 12px; 
            background: #f8f9fa; 
            word-break: break-all; 
        }
        pre { 
            background: #282c34; 
            color: #abb2bf; 
            padding: 20px; 
            border-radius: 8px; 
            overflow-x: auto; 
            font-size: 13px; 
            line-height: 1.6; 
        }
        .banner-preview { 
            width: 100%; 
            height: 120px; 
            object-fit: cover; 
            border-radius: 8px; 
            border: 2px solid #dee2e6; 
        }
        .test-links { 
            display: flex; 
            gap: 10px; 
            flex-wrap: wrap; 
            margin: 20px 0; 
        }
        .test-links a { 
            padding: 10px 20px; 
            background: #007bff; 
            color: white; 
            text-decoration: none; 
            border-radius: 5px; 
            font-weight: bold; 
        }
        .test-links a:hover { 
            background: #0056b3; 
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border: 1px solid #dee2e6;
        }
        th {
            background: #007bff;
            color: white;
        }
        tr:nth-child(even) {
            background: #f8f9fa;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php
        require_once '../../model/database.php';
        require_once '../../model/product_model.php';

        $product_id = isset($_GET['id']) ? intval($_GET['id']) : 1;
        $product = getProductById($product_id);

        if (!$product) {
            echo "<h1 class='error'>❌ Product not found!</h1>";
            exit;
        }

        echo "<h1>🔍 Product Detail Debug - ID: $product_id</h1>";

        // Product Info
        echo "<h2>📦 Product Information</h2>";
        echo "<div class='info-grid'>";
        
        echo "<div class='info-box'>";
        echo "<h3>Basic Info</h3>";
        echo "<p><strong>ID:</strong> {$product['product_id']}</p>";
        echo "<p><strong>Name:</strong> {$product['name']}</p>";
        echo "<p><strong>Price:</strong> " . number_format($product['price']) . " VNĐ</p>";
        echo "<p><strong>Discount:</strong> {$product['manual_discount']}%</p>";
        echo "<p><strong>Stock:</strong> {$product['stock']}</p>";
        echo "<p><strong>Status:</strong> {$product['status']}</p>";
        echo "</div>";

        echo "<div class='info-box'>";
        echo "<h3>Category Info</h3>";
        $cat_id = intval($product['category_id']);
        echo "<p><strong>Category ID:</strong> <span style='font-size:20px;'>$cat_id</span></p>";
        
        $cat_names = [1 => 'Snowboards', 2 => 'Boots', 3 => 'Goggles'];
        $cat_name = isset($cat_names[$cat_id]) ? $cat_names[$cat_id] : 'Unknown';
        echo "<p><strong>Category:</strong> $cat_name</p>";
        
        $expected_banner = [1 => 'baner_product.jpg', 2 => 'ski-boots4.jpg', 3 => 'goggles1.jpg'];
        $banner = isset($expected_banner[$cat_id]) ? $expected_banner[$cat_id] : 'baner_product.jpg';
        echo "<p><strong>Expected Banner:</strong> $banner</p>";
        echo "</div>";

        echo "<div class='info-box'>";
        echo "<h3>Image Info</h3>";
        echo "<p><strong>Main Image:</strong> {$product['image']}</p>";
        $main_img_path = __DIR__ . "/../../Images/product/" . $product['image'];
        $exists = file_exists($main_img_path);
        echo "<p><strong>Main Image Exists:</strong> " . ($exists ? "<span class='success'>✅ YES</span>" : "<span class='error'>❌ NO</span>") . "</p>";
        echo "<p><strong>Main Image Path:</strong><br><small>$main_img_path</small></p>";
        echo "</div>";

        echo "</div>";

        // Banner Check
        echo "<h2>🎪 Banner Check</h2>";
        $banner_dir = __DIR__ . "/../../Images/baner/";
        $banner_file = isset($expected_banner[$cat_id]) ? $expected_banner[$cat_id] : 'baner_product.jpg';
        $banner_path = $banner_dir . $banner_file;
        $banner_exists = file_exists($banner_path);

        echo "<div class='info-grid'>";
        echo "<div class='info-box'>";
        echo "<h3>Banner File</h3>";
        echo "<p><strong>File:</strong> $banner_file</p>";
        echo "<p><strong>Exists:</strong> " . ($banner_exists ? "<span class='success'>✅ YES</span>" : "<span class='error'>❌ NO</span>") . "</p>";
        echo "<p><strong>Path:</strong><br><small>$banner_path</small></p>";
        echo "</div>";
        echo "</div>";

        if ($banner_exists) {
            echo "<p><strong>Banner Preview:</strong></p>";
            echo "<img src='../../Images/baner/$banner_file' class='banner-preview' alt='Banner'>";
        }

        // Image Folder Check
        echo "<h2>🖼️ Image Folder Check</h2>";
        
        $base_dir = __DIR__ . "/../../Images/product/";
        $sp_folder = "Sp" . $product_id . "/";
        $folder_path = $base_dir . $sp_folder;
        $folder_exists = is_dir($folder_path);

        echo "<div class='info-grid'>";
        echo "<div class='info-box'>";
        echo "<h3>Folder Info</h3>";
        echo "<p><strong>Folder:</strong> $sp_folder</p>";
        echo "<p><strong>Exists:</strong> " . ($folder_exists ? "<span class='success'>✅ YES</span>" : "<span class='error'>❌ NO</span>") . "</p>";
        echo "<p><strong>Full Path:</strong><br><small>$folder_path</small></p>";
        
        if ($folder_exists) {
            $files = scandir($folder_path);
            $images = array_filter($files, function($f) {
                return !in_array($f, ['.', '..']) && preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $f);
            });
            echo "<p><strong>Image Count:</strong> " . count($images) . " files</p>";
        }
        echo "</div>";
        echo "</div>";

        // List Images
        if ($folder_exists && !empty($images)) {
            echo "<h3>📸 Images in Folder:</h3>";
            echo "<table>";
            echo "<tr><th>#</th><th>Filename</th><th>Size</th><th>Preview</th><th>URL</th></tr>";
            
            $counter = 1;
            foreach ($images as $img) {
                $file_path = $folder_path . $img;
                $file_size = file_exists($file_path) ? filesize($file_path) : 0;
                $file_size_kb = round($file_size / 1024, 2);
                $img_url = "../../Images/product/$sp_folder$img";
                
                echo "<tr>";
                echo "<td>$counter</td>";
                echo "<td><strong>$img</strong></td>";
                echo "<td>{$file_size_kb} KB</td>";
                echo "<td><img src='$img_url' style='max-width:100px; max-height:60px;' alt='$img'></td>";
                echo "<td><a href='$img_url' target='_blank'>Open</a></td>";
                echo "</tr>";
                
                $counter++;
            }
            echo "</table>";

            echo "<h3>🖼️ Image Gallery Preview:</h3>";
            echo "<div class='image-grid'>";
            foreach ($images as $img) {
                $img_url = "../../Images/product/$sp_folder$img";
                echo "<div class='image-item'>";
                echo "<img src='$img_url' alt='$img'>";
                echo "<p>$img</p>";
                echo "</div>";
            }
            echo "</div>";
        } else {
            echo "<div class='info-box' style='background:#fff3cd; border-color:#ffc107;'>";
            echo "<p class='warning'>⚠️ No images found in folder or folder doesn't exist!</p>";
            echo "<p>Using fallback: <strong>{$product['image']}</strong></p>";
            echo "</div>";
        }

        // Code used in product_detail.php
        echo "<h2>💻 Code Logic</h2>";
        echo "<pre>";
        echo "// Image Loading Code\n";
        echo "\$base_dir = __DIR__ . \"/../../Images/product/\";\n";
        echo "\$base_url = \"../../Images/product/\";\n";
        echo "\$sp_folder = \"Sp\" . \$product_id . \"/\";\n";
        echo "\$image_folder_path = \$base_dir . \$sp_folder;\n\n";
        echo "// Result:\n";
        echo "Folder Path: $folder_path\n";
        echo "Folder Exists: " . ($folder_exists ? 'true' : 'false') . "\n";
        echo "Image Count: " . (isset($images) ? count($images) : 0) . "\n";
        echo "</pre>";

        // Test Links
        echo "<h2>🧪 Test Other Products</h2>";
        echo "<div class='test-links'>";
        for ($i = 1; $i <= 7; $i++) {
            $active = ($i == $product_id) ? " style='background:#28a745;'" : "";
            echo "<a href='?id=$i'$active>Product $i</a>";
        }
        echo "</div>";

        echo "<h2>🔗 Quick Links</h2>";
        echo "<div class='test-links'>";
        echo "<a href='product_detail.php?id=$product_id' target='_blank'>View Product Detail</a>";
        echo "<a href='product_list.php' target='_blank'>Back to Product List</a>";
        echo "<a href='../../fix_database.php' target='_blank'>Fix Database</a>";
        echo "</div>";
        ?>
    </div>
</body>
</html>
