<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login-admin.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];

// Fetch admin details
$sql = "SELECT admin_id, password, admin_passphrase FROM votinghub WHERE admin_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

$popup_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_admin_id = $_POST['admin-id'];
    $new_password = $_POST['new-password'];
    $current_password = $_POST['current-password'];
    $admin_passphrase = $_POST['admin-passphrase'];

    // Verify current password and passphrase
    $sql = "SELECT * FROM votinghub WHERE admin_id = ? AND password = ? AND admin_passphrase = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $admin_id, $current_password, $admin_passphrase);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Update admin details
        $sql = "UPDATE votinghub SET admin_id = ?, password = ? WHERE admin_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $new_admin_id, $new_password, $admin_id);
        if ($stmt->execute()) {
            $popup_message = "Profile updated successfully";
            $_SESSION['admin_id'] = $new_admin_id; // Update session with new admin ID
        } else {
            $popup_message = "Error updating profile: " . $stmt->error;
        }
    } else {
        $popup_message = "Incorrect current password or admin passphrase";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="css/admin-profile.css">
    <link rel="icon" type="image/x-icon" href="images/mmu.ico">
</head>
<body>
    <div class="header">
        <div class="logo">
            <a href="admin.html"><img src="../src/images/mmu-logo.png" alt="MMU Logo" class="logo-img"></a>
        </div>
        <nav class="navbar">
            <ul class="navbar-nav">
                <li><a href="admin.html">Home</a></li>
                <li><a href="admin-profile.php" class="active">Edit Profile</a></li>
            </ul>
        </nav>
    </div>
    <div class="main-content">
        <h2>Edit Profile</h2>
        <?php if ($popup_message): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                alert("<?php echo $popup_message; ?>");
            });
        </script>
        <?php endif; ?>
        <div class="form-container">
            <form action="admin-profile.php" method="post">
                <label for="admin-id">Admin ID:</label>
                <input type="text" id="admin-id" name="admin-id" value="<?php echo $admin['admin_id']; ?>" required>

                <label for="new-password">New Password:</label>
                <input type="password" id="new-password" name="new-password" required>

                <label for="current-password">Current Password (required to save changes):</label>
                <input type="password" id="current-password" name="current-password" required>

                <label for="admin-passphrase">Admin Passphrase (required to save changes):</label>
                <input type="text" id="admin-passphrase" name="admin-passphrase" required>

                <button type="submit" class="create-btn">Save Changes</button>
            </form>
        </div>
    </div>
</body>
</html>
