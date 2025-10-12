<?php
require_once '../../model/database.php';

$conn = getDatabaseConnection();

// Get all products with category info
$query = "SELECT p.product_id, p.name, p.category_id, c.category_name, p.image 
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.category_id 
          ORDER BY p.product_id 
          LIMIT 10";
$result = $conn->query($query);

echo "<h2>Products in Database</h2>";
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>ID</th><th>Name</th><th>Category ID</th><th>Category Name</th><th>Image</th></tr>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['product_id'] . "</td>";
    echo "<td>" . $row['name'] . "</td>";
    echo "<td>" . $row['category_id'] . "</td>";
    echo "<td>" . $row['category_name'] . "</td>";
    echo "<td>" . $row['image'] . "</td>";
    echo "</tr>";
}

echo "</table>";

echo "<h3>Banner Mapping:</h3>";
echo "<ul>";
echo "<li>Category 1 → baner_product.jpg</li>";
echo "<li>Category 2 → ski-boots4.jpg</li>";
echo "<li>Category 3 → goggles1.jpg</li>";
echo "</ul>";

echo "<h3>Test Links:</h3>";
$result->data_seek(0);
while ($row = $result->fetch_assoc()) {
    $id = $row['product_id'];
    echo "<a href='product_detail.php?id=$id' target='_blank'>Product $id ({$row['name']})</a><br>";
}
?>
