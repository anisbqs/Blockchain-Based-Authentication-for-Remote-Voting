<?php
session_start();
include 'db_connect.php';

// Fetch election names for the dropdown
$sql = "SELECT DISTINCT election_name FROM Election";
$result = $conn->query($sql);
$elections = [];
while ($row = $result->fetch_assoc()) {
    $elections[] = $row['election_name'];
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['election-name'])) {
    $election_name = $_POST['election-name'];
    $contract_address = $_POST['contract-address'];
    $start_date = $_POST['start-date'];
    $end_date = $_POST['end-date'];
    $candidate_ids = $_POST['candidate-ids'];

    $candidate_ids_array = explode(',', $candidate_ids);
    if (count($candidate_ids_array) < 2) {
        echo "<div class='alert'>Please select at least 2 candidates</div>";
    } else {
        $sql = "SELECT COUNT(*) AS count FROM Election WHERE LOWER(election_name) = LOWER(?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $election_name);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        if ($row['count'] > 0) {
            echo "<div class='alert'>Election name already exists. Please choose a different name.</div>";
        } else {
            foreach ($candidate_ids_array as $candidate_id) {
                $sql = "SELECT student_name FROM Student WHERE student_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $candidate_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $student = $result->fetch_assoc();
                $candidate_name = $student['student_name'];

                $sql = "INSERT INTO Election (election_name, contract_address, candidate_name, candidate_student_id, election_start_time, election_end_time) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssss", $election_name, $contract_address, $candidate_name, $candidate_id, $start_date, $end_date);
                $stmt->execute();
            }
            echo "<div class='alert success'>Election created successfully</div>";
        }
    }
}

// Fetch student names excluding those already in an election
$sql = "SELECT student_id, student_name FROM Student WHERE student_id NOT IN (SELECT candidate_student_id FROM Election)";
$result = $conn->query($sql);
$students = [];
while ($row = $result->fetch_assoc()) {
    $students[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/web3@1.6.1/dist/web3.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/truffle-contract@4.0.31/dist/truffle-contract.min.js"></script>
    <title>Add Election</title>
    <link rel="stylesheet" href="css/admin-add.css">
    <link rel="icon" type="image/x-icon" href="images/mmu.ico">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <style>
        .alert {
            background-color: #f44336;
            color: white;
            padding: 10px;
            margin-bottom: 15px;
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
                <li><a href="add-election.php" class="active">Add Election</a></li>
                <li><a href="edit-election.php">Edit Election</a></li>
            </ul>
        </nav>
    </div>
    <div class="main-content">
        <h2>Add Election</h2>
        <div class="form-container">
            <form id="add-election-form" action="add-election.php" method="post">
                <label for="election-name">Election Name:</label>
                <input type="text" id="election-name" name="election-name" required>

                <label for="contract-address">Contract Address:</label>
                <input type="text" id="contract-address" name="contract-address" required>

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
                <input type="datetime-local" id="start-date" name="start-date" required>

                <label for="end-date">End Date and Time:</label>
                <input type="datetime-local" id="end-date" name="end-date" required>

                <button type="submit" class="create-btn" form='add-election-form'>Create Election</button>
            </form>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2();
        });

        var addElectionForm = document.getElementById("add-election-form");
        addElectionForm.addEventListener("submit", async function (e) {
            e.preventDefault();
            await validateFormAndCallSmartContract();
            addElectionForm.submit(); 
        });

        var alertMsg = null;
        function showAlert(message, overrideGlobalVar) {
            if (overrideGlobalVar || !alertMsg){
                alert(message);
            }
            else if (alertMsg) {
                alert(alertMsg);
            }
            alertMsg = null;
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

        function updateCandidateIds() {
            var candidateList = document.getElementById('candidate-list');
            var candidateIds = [];
            var existingCandidates = candidateList.getElementsByClassName('student-id-info');
            for (var i = 0; i < existingCandidates.length; i++) {
                candidateIds.push(existingCandidates[i].textContent.replace('Student ID: ', ''));
            }
            document.getElementById('candidate-ids').value = candidateIds.join(',');
        }

        async function validateFormAndCallSmartContract() {
            var candidateIdsString = document.getElementById('candidate-ids').value;
            var candidateIds = candidateIdsString.split(',');
            if (candidateIds.length < 2) {
                showAlert('Please select at least 2 candidates.', true);
                return;
            }

            try {
                await addCandidatesSmartContract(candidateIds);
                showAlert('Added candidates to election smart contract successfully!', true);

            } catch (error) {
                console.error(error);
                showAlert("Failed to add candidates to smart contract! Please check the console.", false);
            }
        }

        async function addCandidatesSmartContract(candidateIds) {
            var contractAddress = document.getElementById('contract-address').value;
            var instance = await createTruffleContractInstance(contractAddress);
            var account = await getEthereumAccount();

            console.log(account);
            const receipt = await instance.addCandidates(candidateIds, candidateIds.length, { from: account, gas: 3000000 });

            console.log("Transaction Receipt:");
            console.log(receipt);
        }

        async function createTruffleContractInstance(contractAddress) {
            const web3 = new Web3(window.ethereum);
            const networkId = await web3.eth.net.getId();
            if (networkId !== 5777) {
                alert('Please connect to the Ganache network in MetaMask.');
                return;
            }

            const contractData = await $.getJSON('../build/contracts/VotingSystem.json');
            const VotingSystem = TruffleContract(contractData);
            VotingSystem.setProvider(web3.currentProvider);

            const instance = await VotingSystem.at(contractAddress);
            return instance;
        }

        async function getEthereumAccount() {
            if (typeof window.ethereum !== 'undefined') {
                await ethereum.request({ method: 'eth_requestAccounts' });
                return ethereum.selectedAddress;
            } else {
                alertMsg = 'MetaMask is not installed. Please install it to use this feature.';
                throw new Error('MetaMask is not installed. Please install it to use this feature.');
            }
        }
    </script>
</body>
</html>
