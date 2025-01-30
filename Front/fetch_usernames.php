<?php
include '../config.php';

if (isset($_GET['term'])) {
    $term = $conn->real_escape_string($_GET['term']);
    $query = "SELECT username FROM users WHERE username LIKE ? LIMIT 10";
    $stmt = $conn->prepare($query);
    $likeTerm = "%" . $term . "%";
    $stmt->bind_param("s", $likeTerm);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $usernames = [];
        while ($row = $result->fetch_assoc()) {
            $usernames[] = $row['username'];
        }
        echo json_encode($usernames);
    } else {
        echo json_encode([]);
    }
    $stmt->close();
}

$conn->close();
?>
