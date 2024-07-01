<?php
include 'db_connect.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $admin_id = $_POST['admin-id'];
    $password = $_POST['password'];
    $passphrase = $_POST['passphrase'];

    $sql = "SELECT * FROM VotingHub WHERE admin_id = ? AND password = ? AND admin_passphrase = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $admin_id, $password, $passphrase);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Set session variables
        $_SESSION['user_type'] = 'admin';
        $_SESSION['admin_id'] = $admin_id;
        // Redirect to admin dashboard
        header("Location: admin.html");
        exit();
    } else {
        echo "<script>alert('Wrong username or password or passphrase'); window.location.href = 'login-admin.php';</script>";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="css/login.css">
    <link rel="icon" type="image/x-icon" href="images/mmu.ico">
</head>
<body>
    <div class="container">
        <div class="left" style="background-image: url('../src/images/login-admin.jpg');"></div>
        <div class="right">
            <a href="index.html">
                <img src="images/mmu-logo.png" alt="MMU Logo" class="logo">
            </a>
            <div class="login-form">
                <h2>Welcome to the Voting Hub</h2>
                <form method="post">
                    <input type="text" id="admin-id" name="admin-id" placeholder="Admin ID" required>
                    <input type="password" id="password" name="password" placeholder="Password" required>
                    <input type="text" id="passphrase" name="passphrase" placeholder="Passphrase" required>
                    <button type="submit" class="yellow-button">Login</button>
                    <a href="register-admin.php">Register new admin account</a>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
