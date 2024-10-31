<?php
session_start();

if (isset($_SESSION['username'])) {
    // Redirect to user_stats.php if logged in
    header("Location: dashboard.php");
    exit;
}

require_once 'config.php'; // Adjust path if necessary

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepare and execute a query to retrieve user data
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if user exists and verify password
    if ($user && password_verify($password, $user['password'])) {
        // Set session variables
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $user['username'];

        // Redirect to dashboard
        header("Location: dashboard.php");
        exit();
    } else {
        $_SESSION['error_message'] = 'Neteisingas vartotojo vardas arba slaptažodis';
        header("Location: index.php"); // Redirect to login page on error
        exit();
    }
}
?>
