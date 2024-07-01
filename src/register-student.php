<?php
include 'db_connect.php';

$error_message = '';
$popup_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $student_id = $_POST['student-id'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check if student ID and email are authorized
    $sql = "SELECT * FROM ActiveStudents WHERE student_id = ? AND student_email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $student_id, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        $popup_message = 'You are not authorized to register. Please use your official student ID and email.';
    } else {
        // Check if student ID or email is already in the database
        $sql = "SELECT * FROM Student WHERE student_id = ? OR student_email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $student_id, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $popup_message = 'Student ID or Email already exists.';
        } else {
            // Check password complexity
            $password_pattern = '/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';
            if (!preg_match($password_pattern, $password)) {
                $error_message = 'Password must contain at least one letter, one number, one special character, and be at least 8 characters long.';
            } else {
                // Insert new student
                $sql = "INSERT INTO Student (student_id, student_name, student_password, student_email) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssss", $student_id, $name, $password, $email);

                if ($stmt->execute()) {
                    // Redirect to login page after successful registration
                    header("Location: login-student.php");
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
    <title>Student Registration</title>
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
                if (errorMessage.includes('Password must contain')) {
                    var passwordInput = document.getElementById('password');
                    passwordInput.setCustomValidity(errorMessage);
                    passwordInput.reportValidity();
                }
            });
        </script>
        <?php endif; ?>
        <div class="left" style="background-image: url('images/mmu-student.png');"></div>
        <div class="right">
            <a href="index.html">
                <img src="images/mmu-logo.png" alt="MMU Logo" class="logo">
            </a>
            <div class="registration-form">
                <h2>Vote for a better future, Register Now!</h2>
                <form id="registrationForm" method="post" onsubmit="return validateForm()">
                    <input type="text" id="name" name="name" placeholder="Name" required>
                    <input type="text" id="student-id" name="student-id" placeholder="Student ID" required>
                    <input type="email" id="email" name="email" placeholder="Email (must be @mmu.student.my)" pattern=".+@mmu\.student\.my" required>
                    <input type="password" id="password" name="password" placeholder="Password" required>
                    <input type="password" id="password-again" name="password-again" placeholder="Password Again" required oninput="checkPassword(this)">
                    <button type="submit">Register</button>
                </form>
                <a href="login-student.php" class="back-to-login">Back to Login</a>
            </div>
        </div>
    </div>
    <script>
        function checkPassword(input) {
            if (input.value != document.getElementById('password').value) {
                input.setCustomValidity('Passwords must match.');
            } else {
                input.setCustomValidity('');
            }
        }

        function validateForm() {
            var valid = true;

            var emailInput = document.getElementById('email');
            var emailPattern = /.+@mmu\.student\.my/;
            if (!emailPattern.test(emailInput.value)) {
                emailInput.setCustomValidity('Email must be @mmu.student.my');
                emailInput.reportValidity();
                valid = false;
            } else {
                emailInput.setCustomValidity('');
            }

            var passwordInput = document.getElementById('password');
            var passwordPattern = /^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
            if (!passwordPattern.test(passwordInput.value)) {
                passwordInput.setCustomValidity('Password must contain at least one letter, one number, one special character, and be at least 8 characters long.');
                passwordInput.reportValidity();
                valid = false;
            } else {
                passwordInput.setCustomValidity('');
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
