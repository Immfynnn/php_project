<?php
// delete_all_messages.php

session_start(); // Start the session to access session variables

include '../config.php'; // Ensure this file establishes the $conn connection variable

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    echo "You must be logged in to delete messages.";
    exit();
}

$admin_id = intval($_SESSION['admin_id']); // Use the correct variable name

// Check if there are any messages to delete
$checkSql = "SELECT COUNT(*) as count FROM messages1 WHERE recipient_aid = ? OR sender_uid = ?";
$checkStmt = $conn->prepare($checkSql);
if (!$checkStmt) {
    echo "Error preparing statement: " . htmlspecialchars($conn->error);
    exit();
}
$checkStmt->bind_param('ii', $admin_id, $admin_id);

if ($checkStmt->execute()) {
    $result = $checkStmt->get_result();
    $row = $result->fetch_assoc();
    $messageCount = $row['count'];
    
    if ($messageCount > 0) {
        // Proceed to delete all messages
        $deleteSql = "DELETE FROM messages1 WHERE recipient_aid = ? OR sender_uid = ?";
        $deleteStmt = $conn->prepare($deleteSql);
        if (!$deleteStmt) {
            echo "Error preparing statement: " . htmlspecialchars($conn->error);
            exit();
        }
        $deleteStmt->bind_param('ii', $admin_id, $admin_id);

        if ($deleteStmt->execute()) {
            // Display the overlay for successful deletion
            echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const overlay = document.createElement('div');
                        overlay.className = 'overlay-delete';
                        overlay.innerHTML = '<div class=\"overlay-content\">All messages have been successfully deleted.</div>';
                        document.body.appendChild(overlay);
                        
                        // Add fade-in animation
                        overlay.style.animation = 'fadeIn 0.5s forwards';
                        
                        // Close the overlay after 3 seconds or on click
                        overlay.addEventListener('click', () => {
                            overlay.style.animation = 'fadeOut 0.5s forwards';
                            setTimeout(() => overlay.remove(), 500);
                        });
                        setTimeout(() => {
                            overlay.style.animation = 'fadeOut 0.5s forwards';
                            setTimeout(() => overlay.remove(), 500);
                        }, 3000);
                    });
                    window.location.href = 'admin-messages.php';
                  </script>";
        } else {
            echo "Error executing delete statement: " . htmlspecialchars($deleteStmt->error);
        }

        $deleteStmt->close();
    } else {
        // No messages to delete
        echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    const overlay = document.createElement('div');
                    overlay.className = 'overlay-delete';
                    overlay.innerHTML = '<div class=\"overlay-content\">No messages to delete.</div>';
                    document.body.appendChild(overlay);
                    
                    // Add fade-in animation
                    overlay.style.animation = 'fadeIn 0.5s forwards';
                    
                    // Close the overlay after 3 seconds or on click
                    overlay.addEventListener('click', () => {
                        overlay.style.animation = 'fadeOut 0.5s forwards';
                        setTimeout(() => overlay.remove(), 500);
                    });
                    setTimeout(() => {
                        overlay.style.animation = 'fadeOut 0.5s forwards';
                        setTimeout(() => overlay.remove(), 500);
                    }, 3000);
                });
                window.location.href = 'admin-messages.php';
              </script>";
    }
    
    $checkStmt->close();
} else {
    echo "Error executing check statement: " . htmlspecialchars($checkStmt->error);
}

$conn->close();
?>
