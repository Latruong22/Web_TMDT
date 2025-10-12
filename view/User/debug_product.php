<?php
require_once '../../model/database.php';
require_once '../../model/product_model.php';

// Test với product_id từ URL
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 1;
$product = getProductById($product_id);

echo "<h2>Debug Product Detail (ID: $product_id)</h2>";
echo "<pre>";
echo "Product Data:\n";
print_r($product);
echo "\n\n";

// Check banner mapping
$category_banners = [
    1 => 'baner_product.jpg',
    2 => 'ski-boots4.jpg',
    3 => 'goggles1.jpg',
];
$banner_image = isset($category_banners[$product['category_id']]) 
    ? $category_banners[$product['category_id']] 
    : 'baner_product.jpg';

echo "Category ID: " . $product['category_id'] . "\n";
echo "Banner should be: " . $banner_image . "\n\n";

// Check image folder
$image_folder = __DIR__ . "/../../Images/product/Sp" . $product_id . "/";
$image_folder_url = "../../Images/product/Sp" . $product_id . "/";

echo "Image Folder (filesystem): " . $image_folder . "\n";
echo "Folder exists: " . (is_dir($image_folder) ? 'YES' : 'NO') . "\n\n";

if (is_dir($image_folder)) {
    echo "Files in folder:\n";
    $files = scandir($image_folder);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            echo "  - $file\n";
        }
    }
}

echo "\n\nImage URLs generated:\n";
$detail_images = [];
if (is_dir($image_folder)) {
    $files = scandir($image_folder);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..' && preg_match('/\.(jpg|jpeg|png|gif)$/i', $file)) {
            $url = $image_folder_url . $file;
            $detail_images[] = $url;
            echo "  - $url\n";
        }
    }
}

if (empty($detail_images)) {
    $detail_images[] = "../../Images/product/" . $product['image'];
    echo "  - (Fallback) " . $detail_images[0] . "\n";
}

echo "</pre>";

// Show actual banner image
echo "<h3>Banner Image:</h3>";
echo "<img src='../../Images/baner/$banner_image' style='max-width:100%; height:120px; object-fit:cover;'><br>";
echo "<p>File: $banner_image (Category: {$product['category_id']})</p>";

// Show product images
echo "<h3>Product Images:</h3>";
foreach ($detail_images as $img) {
    echo "<img src='$img' style='max-width:200px; margin:5px;'><br>";
    echo "<small>$img</small><br><br>";
}
?>
