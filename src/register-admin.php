<?php
include 'db_connect.php';

$error_message = '';
$popup_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $admin_id = $_POST['admin-id'];
    $password = $_POST['admin-password'];
    $passphrase = $_POST['passphrase'];

    // Check if passphrase is correct
    if ($passphrase !== 'mmuvote') {
        $error_message = 'Admin passphrase is incorrect.';
    } else {
        // Check for duplicate admin_id
        $sql = "SELECT * FROM VotingHub WHERE admin_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $admin_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $popup_message = 'Admin ID already exists.';
        } else {
            // Check password complexity
            $password_pattern = '/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';
            if (!preg_match($password_pattern, $password)) {
                $error_message = 'Password must contain at least one letter, one number, one special character, and be at least 8 characters long.';
            } else {
                // Insert new admin
                $sql = "INSERT INTO VotingHub (admin_id, password, admin_passphrase) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sss", $admin_id, $password, $passphrase);

                if ($stmt->execute()) {
                    // Redirect to login page after successful registration
                    header("Location: login-admin.php");
                    exit();
                } else {
                    $error_message = "Error: " . $stmt->error;
                }

                $stmt->close();
                $conn->close();
            }
        }

        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Registration</title>
    <link rel="stylesheet" href="css/login.css">
    <link rel="icon" type="image/x-icon" href="images/mmu.ico">
</head>
<body>
    <div class="container">
        <?php if ($popup_message): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                alert("<?php echo $popup_message; ?>");
            });
        </script>
        <?php endif; ?>
        <?php if ($error_message): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var errorMessage = "<?php echo $error_message; ?>";
                if (errorMessage.includes('Admin passphrase is incorrect')) {
                    var passphraseInput = document.getElementById('passphrase');
                    passphraseInput.setCustomValidity(errorMessage);
                    passphraseInput.reportValidity();
                } else if (errorMessage.includes('Password must contain')) {
                    var passwordInput = document.getElementById('admin-password');
                    passwordInput.setCustomValidity(errorMessage);
                    passwordInput.reportValidity();
                }
            });
        </script>
        <?php endif; ?>
        <div class="left" style="background-image: url('../src/images/register-admin.jpg');"></div>
        <div class="right">
            <a href="index.html">
                <img src="images/mmu-logo.png" alt="MMU Logo" class="logo">
            </a>
            <div class="registration-form">
                <h2>Welcome to the Voting Hub Registration Page</h2>
                <form method="post" onsubmit="return validateForm()">
                    <input type="text" id="admin-id" name="admin-id" placeholder="Admin ID" required>
                    <input type="password" id="admin-password" name="admin-password" placeholder="Password" required>
                    <input type="password" id="admin-password-again" name="admin-password-again" placeholder="Password Again" required oninput="checkPassword(this)">
                    <input type="text" id="passphrase" name="passphrase" placeholder="Admin Passphrase" required>
                    <button type="submit" class="yellow-button">Register</button>
                </form>
                <a href="login-admin.php" class="back-to-login">Back to Admin Login</a>
            </div>
        </div>
    </div>
    <script>
        function checkPassword(input) {
            if (input.value != document.getElementById('admin-password').value) {
                input.setCustomValidity('Passwords must match.');
            } else {
                input.setCustomValidity('');
            }
        }

        function validateForm() {
            var valid = true;

            var passwordInput = document.getElementById('admin-password');
            var password_pattern = /^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
            if (!password_pattern.test(passwordInput.value)) {
                passwordInput.setCustomValidity('Password must contain at least one letter, one number, one special character, and be at least 8 characters long.');
                passwordInput.reportValidity();
                valid = false;
            } else {
                passwordInput.setCustomValidity('');
            }

            var passphraseInput = document.getElementById('passphrase');
            if (passphraseInput.value !== 'mmuvote') {
                passphraseInput.setCustomValidity('Admin passphrase is incorrect.');
                passphraseInput.reportValidity();
                valid = false;
            } else {
                passphraseInput.setCustomValidity('');
            }

            return valid;
        }

        document.addEventListener('DOMContentLoaded', function() {
            var inputs = document.querySelectorAll('input');
            inputs.forEach(function(input) {
                input.addEventListener('input', function() {
                    input.setCustomValidity('');
                });
            });
        });
    </script>
</body>
</html>
