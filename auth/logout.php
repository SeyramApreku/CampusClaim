<?php
// auth/logout.php
// Destroys the session and sends the user back to the login page.

session_start();
session_unset();    // Clear all session variables
session_destroy();  // Destroy the session cookie

header("Location: login.php");
exit;
?>