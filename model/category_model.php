<?php
require_once 'database.php';

function getAllCategories() {
	global $conn;
	$sql = "SELECT category_id, name FROM categories ORDER BY name";
	$res = $conn->query($sql);
	$rows = [];
	if ($res) {
		while ($row = $res->fetch_assoc()) {
			$rows[] = $row;
		}
	}
	return $rows;
}

function getCategoryById($id) {
	global $conn;
	$stmt = $conn->prepare("SELECT category_id, name FROM categories WHERE category_id = ?");
	$stmt->bind_param("i", $id);
	$stmt->execute();
	$res = $stmt->get_result();
	return $res ? $res->fetch_assoc() : null;
}

?>
