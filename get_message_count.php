<?php
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

$conn = new mysqli("localhost", "root", "", "myforum");

if ($conn->connect_error) {
    echo json_encode(["count" => 0]);
    exit;
}

$result = $conn->query("SELECT COUNT(*) as count FROM posts");
$row = $result->fetch_assoc();

echo json_encode(["count" => (int)$row["count"]]);

$conn->close();
?>
