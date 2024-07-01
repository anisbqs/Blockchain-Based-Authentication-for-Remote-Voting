<?php
include 'db_connect.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_POST['student-id'];
    $password = $_POST['password'];

    $current_time = date('Y-m-d H:i:s');
    $sql = "SELECT * FROM Election WHERE candidate_student_id = ? AND election_start_time <= ? AND election_end_time >= ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $student_id, $current_time, $current_time);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('The entered ID belongs to a candidate. Candidates cannot access the voter page until the election ends.'); window.location.href = 'login-student.php';</script>";
    } else {
        $sql = "SELECT * FROM Student WHERE student_id = ? AND student_password = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $student_id, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $student = $result->fetch_assoc();

            $_SESSION['student_id'] = $student['student_id'];
            $_SESSION['student_name'] = $student['student_name'];
            $_SESSION['student_email'] = $student['student_email'];
            $_SESSION['user_type'] = 'voter';

            if (isset($_POST['remember'])) {
                setcookie("student_id", $student_id, time() + (86400 * 30), "/");
                setcookie("password", $password, time() + (86400 * 30), "/");
            } else {
                setcookie("student_id", "", time() - 3600, "/");
                setcookie("password", "", time() - 3600, "/");
            }

            header("Location: voter.php");
            exit();
        } else {
            echo "<script>alert('Wrong student ID or password'); window.location.href = 'login-student.php';</script>";
        }
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
    <title>Student Login</title>
    <link rel="stylesheet" href="css/login.css">
    <link rel="icon" type="image/x-icon" href="images/mmu.ico">
    <style>
        .blockchain-account {
            margin-top: 15px;
            font-family: Arial, sans-serif;
            font-size: 14px;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="left" style="background-image: url('images/student.jpg');"></div>
        <div class="right">
            <a href="index.html">
                <img src="images/mmu-logo.png" alt="MMU Logo" class="logo">
            </a>
            <div class="login-form">
                <h2>Welcome back, you've been missed!</h2>
                <form method="post">
                    <input type="text" id="student-id" name="student-id" placeholder="Student ID" value="<?php echo isset($_COOKIE['student_id']) ? $_COOKIE['student_id'] : ''; ?>" required>
                    <input type="password" id="password" name="password" placeholder="Password" value="<?php echo isset($_COOKIE['password']) ? $_COOKIE['password'] : ''; ?>" required>
                    <div class="remember-me">
                        <input type="checkbox" id="remember" name="remember" <?php echo isset($_COOKIE['student_id']) ? 'checked' : ''; ?>>
                        <label for="remember">Remember me</label>
                    </div>
                    <button type="submit">Login</button>
                    <a href="register-student.php">Create new account</a>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
