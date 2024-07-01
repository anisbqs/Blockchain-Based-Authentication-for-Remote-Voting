<?php
session_start();

//Checks if user type is set in the session
if (isset($_SESSION['user_type'])) {
    $user_type = $_SESSION['user_type'];

   
    session_unset();
    session_destroy();

    //Redirect based on user type
    if ($user_type == 'admin') {
       header("Location: login-admin.php");
    } elseif ($user_type == 'voter') {
         header("Location: login-student.php");
    }
} else {

    session_unset();
    session_destroy();
        header("Location: index.html");
}
exit();
?>
