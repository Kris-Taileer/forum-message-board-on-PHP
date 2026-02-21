<?php
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

$link = mysqli_connect('localhost', 'root', '', 'myforum');

if (!$link) {
    echo json_encode(['count' => 0]);
    exit;
}

mysqli_set_charset($link, 'utf8mb4');

$result = mysqli_query($link, "SELECT COUNT(*) as count FROM posts");

if (!$result) {
    echo json_encode(['count' => 0]);
    mysqli_close($link);
    exit;
}

$row = mysqli_fetch_assoc($result);

echo json_encode([
    'count' => (int)$row['count'],
    'timestamp' => time()
]);

mysqli_close($link);
?>