<?php
include 'config.php'; // Database connection

$data = json_decode(file_get_contents('php://input'), true);
if (isset($data['ids']) && is_array($data['ids'])) {
    $ids = $data['ids'];
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    $sqlDelete = "DELETE FROM notifications WHERE id IN ($placeholders)";
    $stmt = $conn->prepare($sqlDelete);
    $stmt->bind_param(str_repeat('i', count($ids)), ...$ids);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false]);
}
?>
