<?php
session_start();
include 'db_connect.php';
include 'election-status.php'; // Include the file containing the function

// Handle POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $election_name = $_POST['election-name'];
    $start_date = $_POST['start-date'];
    $end_date = $_POST['end-date'];
    $candidate_ids = $_POST['candidate-ids']; // Assuming candidate IDs are passed as a comma-separated string

    // Check if at least 2 candidates are selected
    $candidate_ids_array = explode(',', $candidate_ids);
    if (count($candidate_ids_array) < 2) {
        echo "<div class='alert'>Please select at least 2 candidates</div>";
    } else {
        // Get the current contract address for the election
        $sql = "SELECT contract_address FROM election WHERE election_name = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $election_name);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $contract_address = $row['contract_address'];

        // Delete old candidates
        $sql = "DELETE FROM election WHERE election_name = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $election_name);
        $stmt->execute();

        foreach ($candidate_ids_array as $candidate_id) {
            // Fetch the candidate name from the Student table
            $sql = "SELECT student_name FROM Student WHERE student_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $candidate_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $student = $result->fetch_assoc();
                $candidate_name = $student['student_name'];

                // Insert into the election table
                $sql = "INSERT INTO election (election_name, contract_address, candidate_name, candidate_student_id, election_start_time, election_end_time) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssss", $election_name, $contract_address, $candidate_name, $candidate_id, $start_date, $end_date);
                $stmt->execute();
            } else {
                echo "<div class='alert'>Error: Candidate with ID $candidate_id not found.</div>";
            }
        }

        echo "<div class='alert success'>Election updated successfully</div>";
    }
}

// Fetch election names for the dropdown
$sql = "SELECT DISTINCT election_name FROM election";
$result = $conn->query($sql);
$elections = [];
while ($row = $result->fetch_assoc()) {
    $elections[] = $row['election_name'];
}

// Fetch student names for the dropdown excluding those who are already candidates in an election
$sql = "SELECT student_id, student_name FROM Student WHERE student_id NOT IN (SELECT candidate_student_id FROM Election)";
$result = $conn->query($sql);
$students = [];
while ($row = $result->fetch_assoc()) {
    $students[] = $row;
}

// Fetch election data if election name is set
$election_data = null;
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['election_name'])) {
    $election_name = $_GET['election_name'];
    $election_data = fetchElectionData($election_name);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Election</title>
    <link rel="stylesheet" href="css/admin-edit.css">
    <link rel="icon" type="image/x-icon" href="images/mmu.ico">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <style>
        .alert {
            background-color: #f44336;
            color: white;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
        }
        .alert.success {
            background-color: #4CAF50;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">
            <a href="admin.html"><img src="../src/images/mmu-logo.png" alt="MMU Logo" class="logo-img"></a>
        </div>
        <nav class="navbar">
            <ul class="navbar-nav">
                <li><a href="add-election.php">Add Election</a></li>
                <li><a href="edit-election.php" class="active">Edit Election</a></li>
            </ul>
        </nav>
    </div>
    <div class="main-content">
        <h2>Edit Election</h2>
        <div class="form-container">
            <form action="edit-election.php" method="post" onsubmit="return validateForm()">
                <label for="election-name">Election Name:</label>
                <select id="election-name" name="election-name" class="select2" onchange="loadElectionData()" required>
                    <option value="">Select an election</option>
                    <?php foreach ($elections as $election): ?>
                        <option value="<?php echo $election; ?>" <?php echo (isset($_GET['election_name']) && $_GET['election_name'] == $election) ? 'selected' : ''; ?>><?php echo $election; ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="candidate-name">Candidate Names:</label>
                <div class="candidate-input">
                    <select id="candidate-name" name="candidate-name" class="select2" style="width:100%;">
                        <option value="">Select a candidate</option>
                        <?php foreach ($students as $student): ?>
                            <option value="<?php echo $student['student_id']; ?>"><?php echo $student['student_name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" class="add-btn" onclick="addCandidate()">+</button>
                </div>
                <div id="candidate-list"></div>
                <input type="hidden" id="candidate-ids" name="candidate-ids">

                <label for="start-date">Start Date and Time:</label>
                <input type="datetime-local" id="start-date" name="start-date" required value="<?php echo $election_data ? $election_data['start_date'] : ''; ?>">

                <label for="end-date">End Date and Time:</label>
                <input type="datetime-local" id="end-date" name="end-date" required value="<?php echo $election_data ? $election_data['end_date'] : ''; ?>">

                <label for="status">Status:</label>
                <div id="status" class="status-btn <?php echo $election_data ? ($election_data['status'] == 'Active' ? 'status-active' : 'status-inactive') : ''; ?>">
                    <?php echo $election_data ? $election_data['status'] : ''; ?>
                </div>

                <button type="submit" class="create-btn">Update Election</button>
            </form>

            <?php if ($election_data): ?>
            <script>
                function updateCandidateIds() {
                    var candidateList = document.getElementById('candidate-list');
                    var candidateIds = [];
                    var existingCandidates = candidateList.getElementsByClassName('student-id-info');
                    for (var i = 0; i < existingCandidates.length; i++) {
                        candidateIds.push(existingCandidates[i].textContent.replace('Student ID: ', ''));
                    }
                    document.getElementById('candidate-ids').value = candidateIds.join(',');
                }

                var candidateList = document.getElementById('candidate-list');
                candidateList.innerHTML = '';
                var candidates = <?php echo json_encode($election_data['candidates']); ?>;
                candidates.forEach(function(candidate) {
                    var candidateDiv = document.createElement('div');
                    candidateDiv.classList.add('candidate-item');
                    var candidateInfo = document.createElement('p');
                    candidateInfo.textContent = 'Name: ' + candidate.name;
                    candidateInfo.classList.add('candidate-info');
                    var studentIdInfo = document.createElement('p');
                    studentIdInfo.textContent = 'Student ID: ' + candidate.id;
                    studentIdInfo.classList.add('student-id-info');
                    var deleteBtn = document.createElement('button');
                    deleteBtn.textContent = '-';
                    deleteBtn.classList.add('delete-btn');
                    deleteBtn.onclick = function() {
                        candidateList.removeChild(candidateDiv);
                        updateCandidateIds();
                    };
                    candidateDiv.appendChild(candidateInfo);
                    candidateDiv.appendChild(studentIdInfo);
                    candidateDiv.appendChild(deleteBtn);
                    candidateList.appendChild(candidateDiv);
                });
                updateCandidateIds();
            </script>
            <?php endif; ?>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2();
        });

        function loadElectionData() {
            var electionName = document.getElementById('election-name').value;
            if (electionName !== '') {
                window.location.href = "edit-election.php?election_name=" + electionName;
            }
        }

        function addCandidate() {
            var candidateSelect = document.getElementById('candidate-name');
            var candidateName = candidateSelect.options[candidateSelect.selectedIndex].text;
            var candidateId = candidateSelect.value;

            if (candidateId === '' || candidateIdExists(candidateId)) {
                return;
            }

            var candidateList = document.getElementById('candidate-list');
            var candidateDiv = document.createElement('div');
            candidateDiv.classList.add('candidate-item');

            var candidateInfo = document.createElement('p');
            candidateInfo.textContent = 'Name: ' + candidateName;
            candidateInfo.classList.add('candidate-info');

            var studentIdInfo = document.createElement('p');
            studentIdInfo.textContent = 'Student ID: ' + candidateId;
            studentIdInfo.classList.add('student-id-info');

            var deleteBtn = document.createElement('button');
            deleteBtn.textContent = '-';
            deleteBtn.classList.add('delete-btn');
            deleteBtn.onclick = function() {
                candidateList.removeChild(candidateDiv);
                updateCandidateIds();
            };

            candidateDiv.appendChild(candidateInfo);
            candidateDiv.appendChild(studentIdInfo);
            candidateDiv.appendChild(deleteBtn);
            candidateList.appendChild(candidateDiv);

            candidateSelect.value = '';
            updateCandidateIds();
        }

        function candidateIdExists(candidateId) {
            var candidateList = document.getElementById('candidate-list');
            var existingCandidates = candidateList.getElementsByClassName('student-id-info');
            for (var i = 0; i < existingCandidates.length; i++) {
                if (existingCandidates[i].textContent === 'Student ID: ' + candidateId) {
                    return true;
                }
            }
            return false;
        }

        function validateForm() {
            var candidateIds = document.getElementById('candidate-ids').value;
            var candidateIdsArray = candidateIds.split(',');
            if (candidateIdsArray.length < 2) {
                alert('Please select at least 2 candidates');
                return false;
            }
            return true;
        }
    </script>
</body>
</html>
