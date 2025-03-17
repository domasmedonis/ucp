<?php
session_start();

if (isset($_SESSION['username'])) {
    header("Location: dashboard.php");
    exit;
}

require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Fetch user data including email verification status
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        // **Check if email is verified**
        if ($user['email_verified'] == 0) { // Assuming 0 means not verified
            $_SESSION['error_message'] = 'Jūsų el. paštas nėra patvirtintas. Prašome patikrinti savo el. paštą.';
            header("Location: index.php");
            exit();
        }

        // Set session and log in
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $user['username'];

        header("Location: dashboard.php");
        exit();
    } else {
        $_SESSION['error_message'] = 'Neteisingas vartotojo vardas arba slaptažodis';
        header("Location: index.php");
        exit();
    }
}
?>
