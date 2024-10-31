<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: index.php"); // Redirect to login page if not logged in
    exit;
}

$username = $_SESSION['username'];

// Get user ID from the database using the username
$stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

if (!$user) {
    die("Debug: User not found in the database for username: " . htmlspecialchars($username));
}

$user_id = $user['id']; // Get the actual user ID

// Fetch characters with status accepted and pending only for the main list
$stmt = $pdo->prepare("SELECT * FROM characters WHERE user_id = ? AND status IN ('accepted', 'pending')");
$stmt->execute([$user_id]);
$characters = $stmt->fetchAll();

// Fetch declined characters for the new section
$stmt_declined = $pdo->prepare("SELECT * FROM characters WHERE user_id = ? AND status = 'declined'");
$stmt_declined->execute([$user_id]);
$declined_characters = $stmt_declined->fetchAll();
?>

<!DOCTYPE html>
<html lang="lt">
<head>
    <meta charset="UTF-8">
    <title>Jūsų veikėjai</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" type="image/png" sizes="32x32" href="./assets/images/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="./assets/images/favicon-16x16.png">
    <script>
        function toggleDeclinedCharacters() {
            const declinedSection = document.getElementById('declined-characters');
            const button = document.getElementById('toggle-button');

            if (declinedSection.style.display === 'none' || declinedSection.style.display === '') {
                declinedSection.style.display = 'block';
                button.textContent = 'Slėpti atmestus veikėjus';
            } else {
                declinedSection.style.display = 'none';
                button.textContent = 'Rodyti atmestus veikėjus';
            }
        }
    </script>
</head>
<body>

<?php include 'sidebar.html'; ?>   

<div class="container mt-5">
    <h2 class="text-center pd70">Jūsų veikėjai</h2>

    <?php
    // Display any error message if exists
    if (isset($_SESSION['error_message'])) {
        echo '<div class="alert alert-danger">' . $_SESSION['error_message'] . '</div>';
        unset($_SESSION['error_message']);
    }

    // Display any success message if exists
    if (isset($_SESSION['success_message'])) {
        echo '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
        unset($_SESSION['success_message']);
    }
    ?>

    <?php if (empty($characters)): ?>
        <div class="alert alert-info text-center">
            Jūs neturite veikėjų.
        </div>
    <?php else: ?>
        <ul class="list-group mt-3">
            <?php foreach ($characters as $character): ?>
                <li class="list-group-item">
                    <a href="user_stats.php?character_id=<?php echo $character['id']; ?>" class="charListName">
                        <?php echo htmlspecialchars($character['name']); ?>
                    </a>
                    <?php if ($character['status'] === 'pending'): ?>
                        - Laukiama patvirtinimo
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <!-- Button to toggle declined characters section -->
    <div class="text-center mt-4">
        <button id="toggle-button" class="btn btn-secondary" onclick="toggleDeclinedCharacters()">Rodyti atmestus veikėjus</button>
    </div>

    <!-- Declined Characters Section -->
    <div id="declined-characters" style="display: none; margin-top: 20px;">
        <h3 class="text-center">Atmesti veikėjai</h3>
        <?php if (empty($declined_characters)): ?>
            <div class="alert alert-info text-center">
                Jūs neturite atmestų veikėjų.
            </div>
        <?php else: ?>
            <ul class="list-group">
                <?php foreach ($declined_characters as $declined_character): ?>
                    <li class="list-group-item">
                        <?php echo htmlspecialchars($declined_character['name']); ?> - 
                        <strong>Priežastis:</strong> <?php echo htmlspecialchars($declined_character['decline_reason']); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <!-- Always show the "Kurti veikėją" button -->
    <div class="text-center mt-4">
        <a href="create_character.php" class="btn btn-primary">Kurti veikėją</a>
    </div>
</div>

</body>
</html>
