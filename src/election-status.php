<?php
include 'db_connect.php';

function fetchElectionData($election_name) {
    global $conn;

    // Fetch election details
    $sql = "SELECT DISTINCT election_start_time, election_end_time FROM election WHERE election_name = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $election_name);
    $stmt->execute();
    $result = $stmt->get_result();
    $election = $result->fetch_assoc();

    $start_date = $election['election_start_time'];
    $end_date = $election['election_end_time'];

    // Determine status with detailed cases
    $current_time = date("Y-m-d H:i:s");
    $start_timestamp = strtotime($start_date);
    $end_timestamp = strtotime($end_date);
    $current_timestamp = strtotime($current_time);

    if ($current_timestamp < $start_timestamp) {
        $status = 'Inactive'; // The election has not started yet
    } elseif ($current_timestamp > $end_timestamp) {
        $status = 'Inactive'; // The election has ended
    } elseif ($current_timestamp >= $start_timestamp && $current_timestamp <= $end_timestamp) {
        $status = 'Active'; // The election is currently active
    } else {
        $status = 'Inactive'; // Default to inactive for any other case
    }

    // Fetch candidates
    $sql = "SELECT candidate_name, candidate_student_id FROM election WHERE election_name = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $election_name);
    $stmt->execute();
    $result = $stmt->get_result();

    $candidates = [];
    while ($row = $result->fetch_assoc()) {
        $candidates[] = ['name' => $row['candidate_name'], 'id' => $row['candidate_student_id']];
    }

    return ['start_date' => $start_date, 'end_date' => $end_date, 'status' => $status, 'candidates' => $candidates];
}
?>
