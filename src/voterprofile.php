<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: login-student.php");
    exit();
}

$student_id = $_SESSION['student_id'];

$sql = "SELECT * FROM Student WHERE student_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $student = $result->fetch_assoc();
} else {
    $alert_message = "Student not found.";
}

function is_password_valid($password) {
    return strlen($password) >= 8 &&
           preg_match('/[A-Z]/', $password) &&
           preg_match('/[a-z]/', $password) &&
           preg_match('/[0-9]/', $password);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = $_POST['full-name'];
    $new_password = $_POST['new-password'];
    $current_password = $_POST['current-password'];

    if ($current_password !== $student['student_password']) {
        $alert_message = "Incorrect current password";
    } else {
        if (!empty($new_password)) {
            if (!is_password_valid($new_password)) {
                $alert_message = "Password must be at least 8 characters long and include at least one uppercase letter, one lowercase letter, and one number";
            } else {
                $new_password_hashed = $new_password; // Using plain text password for simplicity, should be hashed
            }
        } else {
            $new_password_hashed = $student['student_password'];
        }

        if (!isset($alert_message)) {
            $sql = "UPDATE Student SET student_name = ?, student_password = ? WHERE student_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $full_name, $new_password_hashed, $student_id);
            $stmt->execute();

            $alert_message = "Profile updated successfully";
            header("Refresh: 2; url=voterprofile.php");
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="css/voterprofile.css">
    <link rel="icon" type="image/x-icon" href="images/mmu.ico">
    <style>
        .alert {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px 20px;
            border: 1px solid #f5c6cb;
            border-radius: 5px;
            z-index: 1000;
            max-width: 25%; 
            text-align:center;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var alert = document.querySelector('.alert');
            if (alert) {
                setTimeout(function() {
                    alert.style.display = 'none';
                }, 5000);
            }
        });
    </script>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-brand">
            <a href="voter.php">
                <img src="images/mmu-logo.png" alt="MMU Logo">
            </a>
        </div>
        <ul class="navbar-nav">
            <li><a href="votercastvote.php" class="nav-button">Cast a Vote!</a></li>
        </ul>
    </nav>
    <div class="hero-section">
        <div class="overlay"></div>
        <div class="hero-content">
            <div class="container">
                <h2 class="form-title">Edit Profile</h2>
                <img src="images/profile-picture.jpg" alt="Profile Picture" class="profile-picture">
                <form method="post">
                    <div class="form-group">
                        <label for="student-id">Student ID:</label>
                        <input type="text" id="student-id" name="student-id" value="<?php echo $student['student_id']; ?>" disabled>
                    </div>
                    <div class="form-group">
                        <label for="full-name">Full Name:</label>
                        <input type="text" id="full-name" name="full-name" value="<?php echo $student['student_name']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email-address">Email Address:</label>
                        <input type="email" id="email-address" name="email-address" value="<?php echo $student['student_email']; ?>" disabled>
                    </div>
                    <div class="form-group">
                        <label for="new-password">Change Password:</label>
                        <input type="password" id="new-password" name="new-password" placeholder="New Password">
                    </div>
                    <div class="divider"></div>
                    <div class="form-group">
                        <label for="current-password">Enter Current Password to Save Changes:</label>
                        <input type="password" id="current-password" name="current-password" placeholder="Current Password" required>
                    </div>

                    <div class="form-group">
                        <button type="submit">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php if (isset($alert_message)): ?>
        <div class="alert"><?php echo $alert_message; ?></div>
    <?php endif; ?>
</body>
</html>
