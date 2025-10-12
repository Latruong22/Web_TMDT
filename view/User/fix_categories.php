<!DOCTYPE html>
<html>
<head>
    <title>Fix Categories & Test</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        .button { 
            padding: 10px 20px; 
            background: #007bff; 
            color: white; 
            border: none; 
            cursor: pointer; 
            margin: 5px;
            text-decoration: none;
            display: inline-block;
        }
        .button:hover { background: #0056b3; }
        table { border-collapse: collapse; width: 100%; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background: #f2f2f2; }
        .cat-1 { background: #e3f2fd; }
        .cat-2 { background: #fff3e0; }
        .cat-3 { background: #f3e5f5; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
    </style>
</head>
<body>
    <h1>🔧 Fix Product Categories</h1>
    
    <?php
    require_once '../../model/database.php';
    
    $conn = getDatabaseConnection();
    
    if (isset($_POST['fix_categories'])) {
        // Update categories
        $queries = [
            "UPDATE products SET category_id = 1 WHERE product_id IN (1, 4)",
            "UPDATE products SET category_id = 2 WHERE product_id IN (2, 5)",
            "UPDATE products SET category_id = 3 WHERE product_id IN (3, 6, 7)"
        ];
        
        $success = true;
        foreach ($queries as $query) {
            if (!$conn->query($query)) {
                $success = false;
                echo "<p class='error'>Error: " . $conn->error . "</p>";
            }
        }
        
        if ($success) {
            echo "<p class='success'>✅ Categories updated successfully!</p>";
        }
    }
    ?>
    
    <form method="POST">
        <button type="submit" name="fix_categories" class="button">
            🔄 Update Categories Now
        </button>
    </form>
    
    <h2>Current Products:</h2>
    <?php
    $query = "SELECT p.product_id, p.name, p.category_id, c.category_name, p.image 
              FROM products p 
              LEFT JOIN categories c ON p.category_id = c.category_id 
              ORDER BY p.product_id";
    $result = $conn->query($query);
    
    echo "<table>";
    echo "<tr><th>ID</th><th>Name</th><th>Category ID</th><th>Category Name</th><th>Expected Banner</th><th>Test</th></tr>";
    
    $banner_map = [
        1 => 'baner_product.jpg',
        2 => 'ski-boots4.jpg',
        3 => 'goggles1.jpg'
    ];
    
    while ($row = $result->fetch_assoc()) {
        $cat_id = $row['category_id'];
        $class = "cat-" . $cat_id;
        $banner = isset($banner_map[$cat_id]) ? $banner_map[$cat_id] : 'baner_product.jpg';
        
        echo "<tr class='$class'>";
        echo "<td>{$row['product_id']}</td>";
        echo "<td>{$row['name']}</td>";
        echo "<td><strong>{$cat_id}</strong></td>";
        echo "<td>{$row['category_name']}</td>";
        echo "<td>$banner</td>";
        echo "<td><a href='product_detail.php?id={$row['product_id']}' target='_blank' class='button'>View</a></td>";
        echo "</tr>";
    }
    
    echo "</table>";
    ?>
    
    <h2>Banner Files Check:</h2>
    <?php
    $banner_dir = __DIR__ . "/../../Images/baner/";
    $banners = ['baner_product.jpg', 'ski-boots4.jpg', 'goggles1.jpg'];
    
    echo "<ul>";
    foreach ($banners as $banner) {
        $exists = file_exists($banner_dir . $banner);
        $status = $exists ? "✅ EXISTS" : "❌ MISSING";
        $class = $exists ? "success" : "error";
        echo "<li class='$class'>$banner - $status</li>";
    }
    echo "</ul>";
    ?>
    
    <h2>Image Folders Check:</h2>
    <?php
    $product_dir = __DIR__ . "/../../Images/product/";
    
    echo "<ul>";
    for ($i = 1; $i <= 7; $i++) {
        $folder = $product_dir . "Sp$i/";
        $exists = is_dir($folder);
        $status = $exists ? "✅ EXISTS" : "❌ MISSING";
        $class = $exists ? "success" : "error";
        
        echo "<li class='$class'>Sp$i/ - $status";
        
        if ($exists) {
            $files = array_diff(scandir($folder), ['.', '..']);
            $images = array_filter($files, function($f) {
                return preg_match('/\.(jpg|jpeg|png|gif)$/i', $f);
            });
            echo " (" . count($images) . " images)";
        }
        
        echo "</li>";
    }
    echo "</ul>";
    ?>
    
    <h2>Legend:</h2>
    <ul>
        <li class="cat-1">Category 1: Snowboards → baner_product.jpg</li>
        <li class="cat-2">Category 2: Boots → ski-boots4.jpg</li>
        <li class="cat-3">Category 3: Goggles → goggles1.jpg</li>
    </ul>
</body>
</html>
