<?php
// SCRIPT CHECK & FIX DATABASE - RUN THIS FIRST!
require_once __DIR__ . '/model/database.php';

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Database Fix</title>";
echo "<style>
body { font-family: Arial; padding: 20px; background: #f5f5f5; }
.container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
h1 { color: #333; border-bottom: 3px solid #007bff; padding-bottom: 10px; }
h2 { color: #555; margin-top: 30px; }
table { width: 100%; border-collapse: collapse; margin: 20px 0; }
th, td { padding: 12px; text-align: left; border: 1px solid #ddd; }
th { background: #007bff; color: white; }
.cat-1 { background: #e3f2fd; }
.cat-2 { background: #fff3e0; }
.cat-3 { background: #f3e5f5; }
.success { color: green; font-weight: bold; padding: 10px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px; margin: 10px 0; }
.error { color: red; font-weight: bold; padding: 10px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px; margin: 10px 0; }
.info { color: #004085; background: #cce5ff; border: 1px solid #b8daff; padding: 10px; border-radius: 5px; margin: 10px 0; }
.btn { padding: 12px 30px; background: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: bold; margin: 10px 5px; text-decoration: none; display: inline-block; }
.btn:hover { background: #218838; }
.btn-danger { background: #dc3545; }
.btn-danger:hover { background: #c82333; }
.btn-primary { background: #007bff; }
.btn-primary:hover { background: #0056b3; }
.check { color: green; font-size: 20px; }
.cross { color: red; font-size: 20px; }
pre { background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto; }
</style></head><body><div class='container'>";

echo "<h1>🔧 DATABASE CHECK & FIX TOOL</h1>";

// STEP 1: CHECK CURRENT STATE
echo "<h2>📊 STEP 1: Current Database State</h2>";

$query = "SELECT product_id, name, category_id, image, stock, status FROM products ORDER BY product_id";
$result = $conn->query($query);

echo "<table>";
echo "<tr><th>Product ID</th><th>Name</th><th>Category ID</th><th>Image</th><th>Stock</th><th>Status</th></tr>";

$has_wrong_categories = false;
while ($row = $result->fetch_assoc()) {
    $class = "cat-" . $row['category_id'];
    
    // Detect if category is likely wrong
    $name_lower = strtolower($row['name']);
    $expected_cat = 1; // default
    if (strpos($name_lower, 'boot') !== false || strpos($name_lower, 'giày') !== false) {
        $expected_cat = 2;
    } elseif (strpos($name_lower, 'goggle') !== false || strpos($name_lower, 'kính') !== false || strpos($name_lower, 'phụ kiện') !== false) {
        $expected_cat = 3;
    }
    
    if ($row['category_id'] != $expected_cat) {
        $has_wrong_categories = true;
        $class .= " error";
    }
    
    echo "<tr class='$class'>";
    echo "<td><strong>{$row['product_id']}</strong></td>";
    echo "<td>{$row['name']}</td>";
    echo "<td><strong>{$row['category_id']}</strong></td>";
    echo "<td>{$row['image']}</td>";
    echo "<td>{$row['stock']}</td>";
    echo "<td>{$row['status']}</td>";
    echo "</tr>";
}
echo "</table>";

// STEP 2: CHECK CATEGORIES TABLE
echo "<h2>📋 STEP 2: Categories Table</h2>";
$cat_query = "SELECT * FROM categories ORDER BY category_id";
$cat_result = $conn->query($cat_query);

echo "<table>";
echo "<tr><th>Category ID</th><th>Category Name</th></tr>";
while ($cat = $cat_result->fetch_assoc()) {
    echo "<tr>";
    echo "<td><strong>{$cat['category_id']}</strong></td>";
    echo "<td>{$cat['category_name']}</td>";
    echo "</tr>";
}
echo "</table>";

// STEP 3: CHECK BANNER FILES
echo "<h2>🎪 STEP 3: Banner Files Check</h2>";
$banner_dir = __DIR__ . "/Images/baner/";
$expected_banners = [
    'baner_product.jpg' => 'Category 1 (Snowboards)',
    'ski-boots4.jpg' => 'Category 2 (Boots)',
    'goggles1.jpg' => 'Category 3 (Goggles)'
];

echo "<table>";
echo "<tr><th>Banner File</th><th>For Category</th><th>Status</th><th>Path</th></tr>";
foreach ($expected_banners as $file => $desc) {
    $path = $banner_dir . $file;
    $exists = file_exists($path);
    $status = $exists ? "<span class='check'>✅ EXISTS</span>" : "<span class='cross'>❌ MISSING</span>";
    
    echo "<tr>";
    echo "<td><strong>$file</strong></td>";
    echo "<td>$desc</td>";
    echo "<td>$status</td>";
    echo "<td><small>$path</small></td>";
    echo "</tr>";
}
echo "</table>";

// STEP 4: CHECK IMAGE FOLDERS
echo "<h2>🖼️ STEP 4: Product Image Folders Check</h2>";
$product_dir = __DIR__ . "/Images/product/";

echo "<table>";
echo "<tr><th>Folder</th><th>Status</th><th>Image Count</th><th>Files</th></tr>";

for ($i = 1; $i <= 7; $i++) {
    $folder = $product_dir . "Sp$i/";
    $exists = is_dir($folder);
    $status = $exists ? "<span class='check'>✅</span>" : "<span class='cross'>❌</span>";
    
    echo "<tr>";
    echo "<td><strong>Sp$i/</strong></td>";
    echo "<td>$status</td>";
    
    if ($exists) {
        $files = array_diff(scandir($folder), ['.', '..']);
        $images = array_filter($files, function($f) {
            return preg_match('/\.(jpg|jpeg|png|gif)$/i', $f);
        });
        echo "<td><strong>" . count($images) . " images</strong></td>";
        echo "<td><small>" . implode(", ", array_slice($images, 0, 3));
        if (count($images) > 3) echo "...";
        echo "</small></td>";
    } else {
        echo "<td colspan='2'><span class='error'>Folder not found!</span></td>";
    }
    
    echo "</tr>";
}
echo "</table>";

// STEP 5: FIX BUTTON
echo "<h2>🔧 STEP 5: Fix Database</h2>";

if (isset($_POST['fix_now'])) {
    echo "<div class='info'><strong>⏳ Running fixes...</strong></div>";
    
    // Fix based on product names
    $fixes = [
        "UPDATE products SET category_id = 1 WHERE product_id IN (1, 4) OR name LIKE '%snowboard%' OR name LIKE '%ván trượt%'",
        "UPDATE products SET category_id = 2 WHERE product_id IN (2, 5) OR name LIKE '%boot%' OR name LIKE '%giày%'",
        "UPDATE products SET category_id = 3 WHERE product_id IN (3, 6, 7) OR name LIKE '%goggle%' OR name LIKE '%kính%' OR name LIKE '%phụ kiện%'"
    ];
    
    $success_count = 0;
    foreach ($fixes as $sql) {
        if ($conn->query($sql)) {
            $success_count++;
        } else {
            echo "<div class='error'>❌ Error: " . $conn->error . "</div>";
        }
    }
    
    if ($success_count == count($fixes)) {
        echo "<div class='success'>✅ <strong>SUCCESS!</strong> Database updated successfully! Reload page to see changes.</div>";
        echo "<a href='{$_SERVER['PHP_SELF']}' class='btn btn-primary'>🔄 Reload Page</a>";
    }
    
} else {
    echo "<p><strong>This will update product categories based on product names:</strong></p>";
    echo "<ul>";
    echo "<li>🏂 Snowboards → Category 1 (baner_product.jpg)</li>";
    echo "<li>👢 Boots → Category 2 (ski-boots4.jpg)</li>";
    echo "<li>🥽 Goggles/Accessories → Category 3 (goggles1.jpg)</li>";
    echo "</ul>";
    
    if ($has_wrong_categories) {
        echo "<div class='error'>⚠️ <strong>Warning:</strong> Some products have incorrect category_id!</div>";
    }
    
    echo "<form method='POST'>";
    echo "<button type='submit' name='fix_now' class='btn'>✅ FIX DATABASE NOW</button>";
    echo "</form>";
}

// STEP 6: TEST LINKS
echo "<h2>🧪 STEP 6: Test Product Detail Pages</h2>";
echo "<p>After fixing, test these pages:</p>";

$result->data_seek(0);
echo "<div style='display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px; margin: 20px 0;'>";
while ($row = $result->fetch_assoc()) {
    $id = $row['product_id'];
    echo "<a href='view/User/product_detail.php?id=$id' target='_blank' class='btn btn-primary' style='text-align: center;'>";
    echo "📦 Product $id<br><small>{$row['name']}</small>";
    echo "</a>";
}
echo "</div>";

// STEP 7: SQL COMMANDS
echo "<h2>💻 STEP 7: Manual SQL Commands</h2>";
echo "<p>If you prefer to run SQL manually:</p>";
echo "<pre>";
echo "-- Fix categories based on product names\n";
echo "UPDATE products SET category_id = 1 WHERE name LIKE '%snowboard%' OR name LIKE '%ván trượt%';\n";
echo "UPDATE products SET category_id = 2 WHERE name LIKE '%boot%' OR name LIKE '%giày%';\n";
echo "UPDATE products SET category_id = 3 WHERE name LIKE '%goggle%' OR name LIKE '%kính%' OR name LIKE '%phụ kiện%';\n\n";
echo "-- Or manually by ID\n";
echo "UPDATE products SET category_id = 1 WHERE product_id IN (1, 4);\n";
echo "UPDATE products SET category_id = 2 WHERE product_id IN (2, 5);\n";
echo "UPDATE products SET category_id = 3 WHERE product_id IN (3, 6, 7);\n\n";
echo "-- Verify\n";
echo "SELECT product_id, name, category_id FROM products ORDER BY product_id;\n";
echo "</pre>";

echo "</div></body></html>";
?>
