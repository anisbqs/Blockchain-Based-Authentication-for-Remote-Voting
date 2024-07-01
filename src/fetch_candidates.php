<?php
include 'db_connect.php';

$sql = "SELECT candidate_name, candidate_student_id FROM election";
$result = $conn->query($sql);

$candidates = [];
while ($row = $result->fetch_assoc()) {
    $candidates[] = [
        'candidate_name' => $row['candidate_name'],
        'candidate_student_id' => $row['candidate_student_id']
    ];
}

echo json_encode($candidates);
?>
