<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $election_name = $_POST['election-name'];

    // Increment the voter count for the specified election
    $sql = "UPDATE Election SET voter_count = voter_count + 1 WHERE election_name = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $election_name);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo "Voter count updated successfully.";
    } else {
        echo "Failed to update voter count.";
    }
}
?>
