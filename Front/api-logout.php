<?php
session_start(); // Start the session
session_destroy(); // Destroy all session data
header("Location: api-check-number.php"); // Redirect to your login page
exit();
?>
