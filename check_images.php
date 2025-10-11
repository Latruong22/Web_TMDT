<?php
require_once 'model/database.php';

$stmt = $conn->query('SELECT product_id, name, image FROM products LIMIT 5');

echo "Database Image Paths:<br>";
echo "=====================<br><br>";

while ($row = $stmt->fetch_assoc()) {
    echo "ID: " . $row['product_id'] . "<br>";
    echo "Name: " . $row['name'] . "<br>";
    echo "Image: " . $row['image'] . "<br>";
    echo "Full Path: ../../Images/product/" . $row['image'] . "<br>";
    echo "---<br><br>";
}
