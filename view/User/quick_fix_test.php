<?php
require_once '../../model/database.php';
require_once '../../model/product_model.php';

header('Content-Type: text/html; charset=UTF-8');

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 1;
$product = getProductById($product_id);

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Quick Fix Test</title>";
echo "<style>
body { font-family: Arial; padding: 20px; background: #f5f5f5; }
.section { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
h2 { color: #007bff; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
pre { background: #282c34; color: #abb2bf; padding: 15px; border-radius: 5px; overflow-x: auto; }
img { max-width: 200px; margin: 10px; border: 3px solid #28a745; }
.error-img { border-color: #dc3545 !important; }
table { width: 100%; border-collapse: collapse; margin: 15px 0; }
th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
th { background: #007bff; color: white; }
.success { color: #28a745; font-weight: bold; }
.error { color: #dc3545; font-weight: bold; }
</style></head><body>";

echo "<h1>🔧 Quick Product Image Fix - ID: $product_id</h1>";

// SECTION 1: Product Data
echo "<div class='section'>";
echo "<h2>📦 Product Data from Database</h2>";
echo "<table>";
echo "<tr><th>Field</th><th>Value</th></tr>";
echo "<tr><td><strong>product_id</strong></td><td>{$product['product_id']}</td></tr>";
echo "<tr><td><strong>name</strong></td><td>{$product['name']}</td></tr>";
echo "<tr><td><strong>category_id</strong></td><td>{$product['category_id']}</td></tr>";
echo "<tr><td><strong>image</strong></td><td>{$product['image']}</td></tr>";
echo "<tr><td><strong>price</strong></td><td>" . number_format($product['price']) . " VNĐ</td></tr>";
echo "<tr><td><strong>stock</strong></td><td>{$product['stock']}</td></tr>";
echo "</table>";
echo "</div>";

// SECTION 2: Current Image Loading Logic
echo "<div class='section'>";
echo "<h2>🖼️ Current Image Loading (product_detail.php logic)</h2>";

$base_dir = __DIR__ . "/../../Images/product/";
$base_url = "../../Images/product/";
$sp_folder = "Sp" . $product_id . "/";
$detail_images = [];
$image_folder_path = $base_dir . $sp_folder;

echo "<p><strong>Paths:</strong></p>";
echo "<pre>";
echo "base_dir      : " . $base_dir . "\n";
echo "base_url      : " . $base_url . "\n";
echo "sp_folder     : " . $sp_folder . "\n";
echo "Full path     : " . $image_folder_path . "\n";
echo "Folder exists?: " . (is_dir($image_folder_path) ? 'YES ✅' : 'NO ❌') . "\n";
echo "</pre>";

if (is_dir($image_folder_path)) {
    $files = scandir($image_folder_path);
    if ($files) {
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..' && preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $file)) {
                $detail_images[] = $base_url . $sp_folder . $file;
            }
        }
    }
    
    if (!empty($detail_images)) {
        natsort($detail_images);
        $detail_images = array_values($detail_images);
    }
}

if (empty($detail_images)) {
    $detail_images[] = $base_url . $product['image'];
}

echo "<p><strong>Images array:</strong></p>";
echo "<pre>";
print_r($detail_images);
echo "</pre>";

echo "<h3>Rendered Images (with current paths):</h3>";
foreach ($detail_images as $idx => $img) {
    echo "<img src='$img' alt='Image $idx' onerror=\"this.classList.add('error-img');\">";
}
echo "</div>";

// SECTION 3: Alternative Path (Absolute from root)
echo "<div class='section'>";
echo "<h2>🔄 Alternative Path (Absolute from /Web_TMDT/)</h2>";

$alt_folder = $_SERVER['DOCUMENT_ROOT'] . "/Web_TMDT/Images/product/Sp" . $product_id . "/";
$alt_url = "/Web_TMDT/Images/product/Sp" . $product_id . "/";
$alt_images = [];

echo "<p><strong>Paths:</strong></p>";
echo "<pre>";
echo "Folder        : " . $alt_folder . "\n";
echo "URL           : " . $alt_url . "\n";
echo "Folder exists?: " . (is_dir($alt_folder) ? 'YES ✅' : 'NO ❌') . "\n";
echo "</pre>";

if (is_dir($alt_folder)) {
    $files = scandir($alt_folder);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..' && preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $file)) {
            $alt_images[] = $alt_url . $file;
        }
    }
}

echo "<p><strong>Images array:</strong></p>";
echo "<pre>";
print_r($alt_images);
echo "</pre>";

echo "<h3>Rendered Images (with alternative paths):</h3>";
foreach ($alt_images as $idx => $img) {
    echo "<img src='$img' alt='Image $idx' onerror=\"this.classList.add('error-img');\">";
}
echo "</div>";

// SECTION 4: Direct test
echo "<div class='section'>";
echo "<h2>🎯 Direct Image Test</h2>";
$direct_tests = [
    "/Web_TMDT/Images/product/Sp$product_id/fw25-lib-25sn032-son-of-birdman.jpg",
    "../../Images/product/Sp$product_id/fw25-lib-25sn032-son-of-birdman.jpg",
];

foreach ($direct_tests as $url) {
    echo "<p><strong>URL:</strong> <code>$url</code></p>";
    echo "<img src='$url' alt='Direct test' onerror=\"this.classList.add('error-img');\">";
}
echo "</div>";

// SECTION 5: Recommendation
echo "<div class='section'>";
echo "<h2>💡 Which path works?</h2>";
echo "<p>Look at images above:</p>";
echo "<ul>";
echo "<li><strong>Green border</strong> = Image loaded successfully ✅</li>";
echo "<li><strong>Red border</strong> = Image failed to load ❌</li>";
echo "</ul>";
echo "<p><strong>Recommendation:</strong> Use the path that shows green border!</p>";
echo "</div>";

// SECTION 6: Test links
echo "<div class='section'>";
echo "<h2>🔗 Test Other Products</h2>";
for ($i = 1; $i <= 7; $i++) {
    $active = ($i == $product_id) ? " style='background:#28a745; color:white;'" : "";
    echo "<a href='?id=$i'$active style='padding:10px 20px; margin:5px; display:inline-block; background:#007bff; color:white; text-decoration:none; border-radius:5px;'>Product $i</a> ";
}
echo "</div>";

echo "</body></html>";
?>
