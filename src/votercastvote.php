<?php
session_start();
include 'db_connect.php';
include 'election-status.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: login-student.php");
    exit();
}

$student_id = $_SESSION['student_id'];

// Fetch election names for the dropdown
$sql = "SELECT DISTINCT election_name FROM election";
$result = $conn->query($sql);
$elections = [];
while ($row = $result->fetch_assoc()) {
    $elections[] = $row['election_name'];
}

// Check if election name and candidate id are set
$election_name = isset($_POST['election-name']) ? $_POST['election-name'] : '';
$candidate_id = isset($_POST['candidate-id']) ? $_POST['candidate-id'] : '';

// Fetch election data and status
$election_data = null;
$contract_address = null;
$status = '';
$candidates = [];
$has_voted = false;

if ($election_name) {
    $election_data = fetchElectionData($election_name);
    $status = $election_data['status'];
    if ($status === 'Active') {
        // Fetch candidates based on selected election name
        $sql = "SELECT candidate_name, candidate_student_id FROM election WHERE election_name = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $election_name);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $candidates[] = $row;
        }

        // Fetch contract address
        $sql = "SELECT contract_address FROM election WHERE election_name = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $election_name);
        $stmt->execute();
        $result = $stmt->get_result();
        $contract_address = $result->fetch_object()->contract_address;

        // Check if the student has already voted in this election
        $checkVoteSql = "SELECT has_voted FROM VotingRecord WHERE student_id = ? AND election_name = ?";
        $stmt = $conn->prepare($checkVoteSql);
        $stmt->bind_param("ss", $student_id, $election_name);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if ($row && $row['has_voted']) {
            $has_voted = true;
        }
    }
}

// Handle vote submission
$alert_message = '';
$vote_cast = false;
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $election_name && $candidate_id && !$has_voted) {
    // Validate candidate ID
    $isValidCandidateId = false;
    foreach ($candidates as $candidate) {
        if ($candidate['candidate_student_id'] == $candidate_id) {
            $isValidCandidateId = true;
            break;
        }
    }

    if (!$isValidCandidateId) {
        $alert_message = 'Invalid candidate ID. Please select a valid candidate.';
    } else {
        // Record the vote in the VotingRecord table to prevent double voting
        $recordVoteSql = "INSERT INTO VotingRecord (student_id, election_name, has_voted) VALUES (?, ?, 1)";
        $recordVoteStmt = $conn->prepare($recordVoteSql);
        $recordVoteStmt->bind_param('ss', $student_id, $election_name);
        $recordVoteStmt->execute();

        // Cast the vote on the blockchain
        $alert_message = 'Vote cast successfully!';
        $vote_cast = true;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cast a Vote</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/web3@1.6.1/dist/web3.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/truffle-contract@4.0.31/dist/truffle-contract.min.js"></script>
    <link rel="stylesheet" href="css/votercastvote.css">
    <link rel="icon" type="image/x-icon" href="images/mmu.ico">
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
            <h1 class="form-title">Cast Your Vote</h1>
            <form id="electionForm" method="post">
                <div class="form-group">
                    <label for="election-name">Election Name:</label>
                    <select id="election-name" name="election-name" onchange="this.form.submit()">
                        <option value="">Select an election</option>
                        <?php foreach ($elections as $election): ?>
                            <option value="<?php echo htmlspecialchars($election); ?>" <?php echo $election == $election_name ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($election); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>

            <?php if ($election_name): ?>
                <?php if ($status === 'Inactive'): ?>
                    <p>The selected election is inactive, you're not allowed to vote.</p>
                <?php elseif ($has_voted): ?>
                    <p>You have already voted in this election.</p>
                <?php else: ?>
                    <div id="blockchain-candidates"></div>
                    <form id="voteForm" method="post">
                        <input type="hidden" id="hidden-account" name="hidden-account">
                        <input type="hidden" name="election-name" value="<?php echo htmlspecialchars($election_name); ?>">
                        
                        <div id="candidate-info"></div>
                        <h3>Choose a candidate:</h3>
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Candidate Name</th>
                                    <th>Student ID</th>
                                    <th>Vote</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($candidates as $index => $candidate): ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td><?php echo htmlspecialchars($candidate['candidate_name']); ?></td>
                                        <td><?php echo htmlspecialchars($candidate['candidate_student_id']); ?></td>
                                        <td><input type="radio" name="candidate-id" value="<?php echo htmlspecialchars($candidate['candidate_student_id']); ?>" onclick="displayCandidateInfo('<?php echo htmlspecialchars($candidate['candidate_student_id']); ?>', '<?php echo htmlspecialchars($candidate['candidate_name']); ?>')"></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <div id="selected-candidate-info"></div>
                        <div id="connected-account-info"></div>
                        <button type="submit" class="big-button">Vote</button>
                        <div id="msg"><?php if ($alert_message) echo "<script>alert('$alert_message');</script>"; ?></div>
                    </form>
                <?php endif; ?>
            <?php endif; ?>
            <span hidden id="readonly-contract-address"><?php echo htmlspecialchars($contract_address); ?></span>
        </div>
    </div>
</div>

<script>
    async function displayConnectedAccount(account) {
        const accountInfoDiv = document.getElementById('connected-account-info');
        accountInfoDiv.innerHTML = `<p>Your account: ${account}</p>`;
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

    async function fetchAndDisplayResults() {
        const contractAddressValue = document.getElementById('readonly-contract-address').innerHTML;
        const instance = await createTruffleContractInstance(contractAddressValue);

        const blockchainCandidatesDiv = document.getElementById('blockchain-candidates');
        blockchainCandidatesDiv.innerHTML = `<h3>Voting Results:</h3>`;

        const candidates = <?php echo json_encode($candidates); ?>;

        const table = document.createElement('table');
        table.innerHTML = `
            <thead>
                <tr>
                    <th>#</th>
                    <th>Candidate Name</th>
                    <th>Candidate ID</th>
                    <th>Vote Count</th>
                </tr>
            </thead>
            <tbody></tbody>
        `;

        const tbody = table.querySelector('tbody');

        for (let i = 0; i < candidates.length; i++) {
            const candidate = candidates[i];
            const candidateId = candidate.candidate_student_id;
            const candidateName = candidate.candidate_name;

            try {
                const voteCount = await instance.getCandidateVoteCount.call(candidateId);

                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${i + 1}</td>
                    <td>${candidateName}</td>
                    <td>${candidateId}</td>
                    <td>${voteCount}</td>
                `;
                tbody.appendChild(row);
            } catch (error) {
                console.error(`Error fetching candidate details for ID ${candidateId}:`, error);
            }
        }

        blockchainCandidatesDiv.appendChild(table);
    }

    document.addEventListener('DOMContentLoaded', async function() {
        if (typeof window.ethereum !== 'undefined') {
            try {
                await ethereum.request({ method: 'eth_requestAccounts' });
                const account = ethereum.selectedAddress;
                
                displayConnectedAccount(account);
                const hiddenAccountElement = document.getElementById('hidden-account');
                if (hiddenAccountElement) {
                    hiddenAccountElement.value = account;
                }
                
                fetchAndDisplayResults();
            } catch (error) {
                console.error('Error fetching accounts', error);
            }
        } else {
            alert('MetaMask is not installed. Please install it to use this feature.');
        }

        const voteForm = document.getElementById('voteForm');

        voteForm.addEventListener('submit', async function(event) {
            event.preventDefault();

            const candidateId = document.querySelector('input[name="candidate-id"]:checked');
            if (!candidateId) {
                alert('Please select a candidate to vote.');
                return;
            }

            const candidateIdValue = candidateId.value;
            console.log("Selected candidate ID:", candidateIdValue);
            try {
                const account = document.getElementById('hidden-account').value;
                if (!account) {
                    alert('MetaMask account not detected. Please ensure MetaMask is connected.');
                    return;
                }

                const contractAddressValue = document.getElementById('readonly-contract-address').innerHTML;
                const instance = await createTruffleContractInstance(contractAddressValue);
                const receipt = await instance.castVote(candidateIdValue, { from: account, gas: 300000 });
                console.log("Transaction Receipt:", receipt);

                alert('Vote cast successfully!');
                voteForm.submit();
            } catch (error) {
                console.error('Error casting vote:', error);
                if (error.message.includes('Invalid candidate ID')) {
                    alert('Invalid candidate ID. Please select a valid candidate.');
                } else if (error.code === 4001) {
                    alert('Transaction denied. Please approve the transaction in MetaMask.');
                } else {
                    alert('Error casting vote. Please check the console for details.');
                }
            }
        });
    });
</script>
</body>
</html>
