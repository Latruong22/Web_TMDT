<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Test Image Paths</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        .test-box { margin: 20px 0; padding: 20px; border: 2px solid #ccc; border-radius: 8px; }
        img { max-width: 300px; margin: 10px; border: 2px solid #007bff; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>🧪 Image Path Testing</h1>

    <?php
    require_once '../../model/database.php';
    require_once '../../model/product_model.php';

    $product_id = 1;
    $product = getProductById($product_id);

    // Test different path constructions
    echo "<h2>📂 Current Directory Tests:</h2>";
    echo "<div class='test-box'>";
    echo "<p><strong>__DIR__:</strong> " . __DIR__ . "</p>";
    echo "<p><strong>getcwd():</strong> " . getcwd() . "</p>";
    echo "<p><strong>DOCUMENT_ROOT:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
    echo "</div>";

    // Test Method 1: Current code
    echo "<h2>🔍 Method 1: Current Implementation</h2>";
    echo "<div class='test-box'>";
    
    $base_dir = __DIR__ . "/../../Images/product/";
    $base_url = "../../Images/product/";
    $sp_folder = "Sp" . $product_id . "/";
    
    echo "<h3>Paths:</h3>";
    echo "<pre>";
    echo "base_dir: " . $base_dir . "\n";
    echo "base_url: " . $base_url . "\n";
    echo "sp_folder: " . $sp_folder . "\n";
    echo "Full path: " . $base_dir . $sp_folder . "\n";
    echo "</pre>";
    
    $detail_images = [];
    $image_folder_path = $base_dir . $sp_folder;
    
    if (is_dir($image_folder_path)) {
        echo "<p class='success'>✅ Folder exists!</p>";
        $files = scandir($image_folder_path);
        if ($files) {
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..' && preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $file)) {
                    $detail_images[] = $base_url . $sp_folder . $file;
                }
            }
        }
    } else {
        echo "<p class='error'>❌ Folder not found!</p>";
    }
    
    echo "<p><strong>Images found:</strong> " . count($detail_images) . "</p>";
    echo "<h3>Generated URLs:</h3>";
    echo "<pre>";
    print_r($detail_images);
    echo "</pre>";
    
    echo "<h3>Rendered Images:</h3>";
    foreach ($detail_images as $img) {
        echo "<div>";
        echo "<img src='$img' alt='Test' onerror=\"this.style.border='2px solid red'; this.alt='FAILED: $img';\">";
        echo "<p><small>$img</small></p>";
        echo "</div>";
    }
    
    echo "</div>";

    // Test Method 2: Absolute from document root
    echo "<h2>🔍 Method 2: Absolute Path from Root</h2>";
    echo "<div class='test-box'>";
    
    $images2 = [];
    $folder2 = $_SERVER['DOCUMENT_ROOT'] . "/Web_TMDT/Images/product/Sp" . $product_id . "/";
    $url2 = "/Web_TMDT/Images/product/Sp" . $product_id . "/";
    
    echo "<h3>Paths:</h3>";
    echo "<pre>";
    echo "Folder: " . $folder2 . "\n";
    echo "URL: " . $url2 . "\n";
    echo "</pre>";
    
    if (is_dir($folder2)) {
        echo "<p class='success'>✅ Folder exists!</p>";
        $files2 = scandir($folder2);
        foreach ($files2 as $file) {
            if ($file !== '.' && $file !== '..' && preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $file)) {
                $images2[] = $url2 . $file;
            }
        }
    }
    
    echo "<p><strong>Images found:</strong> " . count($images2) . "</p>";
    echo "<h3>Rendered Images:</h3>";
    foreach ($images2 as $img) {
        echo "<div>";
        echo "<img src='$img' alt='Test' onerror=\"this.style.border='2px solid red'; this.alt='FAILED: $img';\">";
        echo "<p><small>$img</small></p>";
        echo "</div>";
    }
    
    echo "</div>";

    // Test Method 3: Direct server path
    echo "<h2>🔍 Method 3: Direct Test</h2>";
    echo "<div class='test-box'>";
    
    $test_urls = [
        "/Web_TMDT/Images/product/Sp1/fw25-lib-25sn032-son-of-birdman.jpg",
        "../../Images/product/Sp1/fw25-lib-25sn032-son-of-birdman.jpg",
        "../../../Images/product/Sp1/fw25-lib-25sn032-son-of-birdman.jpg",
    ];
    
    foreach ($test_urls as $url) {
        echo "<div>";
        echo "<p><strong>Testing:</strong> <code>$url</code></p>";
        echo "<img src='$url' alt='Test' onerror=\"this.style.border='2px solid red'; this.alt='FAILED';\">";
        echo "</div>";
    }
    
    echo "</div>";

    // Show what product_detail.php would see
    echo "<h2>🎯 What product_detail.php sees:</h2>";
    echo "<div class='test-box'>";
    echo "<p><strong>Current file location:</strong> " . __FILE__ . "</p>";
    echo "<p><strong>Product detail location:</strong> " . __DIR__ . "/product_detail.php</p>";
    echo "<p><strong>Images folder from product_detail.php:</strong></p>";
    echo "<p>Should go UP 2 levels (../../) from view/User/</p>";
    echo "<ul>";
    echo "<li>From: <code>/view/User/product_detail.php</code></li>";
    echo "<li>Up 1: <code>/view/</code> (..)</li>";
    echo "<li>Up 2: <code>/</code> (..)</li>";
    echo "<li>Then: <code>/Images/product/Sp1/</code></li>";
    echo "<li><strong>Result:</strong> <code>../../Images/product/Sp1/file.jpg</code></li>";
    echo "</ul>";
    echo "</div>";
    ?>

    <h2>📝 Conclusion:</h2>
    <div class="test-box">
        <p><strong>Which method works?</strong></p>
        <p>Check images above. The method that shows images with BLUE border is correct!</p>
        <p>We should use that path in product_detail.php</p>
    </div>

</body>
</html>
