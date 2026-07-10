<?php
// Initialize the session state pipeline
session_start();

// Unset and clear all active session variables
$_SESSION = array();

// Clear the session cookie from the browser if it exists
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000,
        $params["path"], 
        $params["domain"],
        $params["secure"], 
        $params["httponly"]
    );
}

// Destroy the session storage on the server completely
session_destroy();

// Redirect clean back to the customer homepage
header("Location: index.php");
exit();
?>