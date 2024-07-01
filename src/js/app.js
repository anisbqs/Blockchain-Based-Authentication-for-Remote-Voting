App = {
  web3Provider: null,
  contracts: {},
  account: '0x0',

  // Initialize the application
  init: async function() {
    return await App.initWeb3();
  },

  // Initialize Web3 provider
  initWeb3: async function() {
    if (window.ethereum) {
      // Modern dapp browsers
      App.web3Provider = window.ethereum;
      try {
        // Request account access
        await window.ethereum.enable();
        web3 = new Web3(window.ethereum);
      } catch (error) {
        console.error("User denied account access");
      }
    } else if (window.web3) {
      // Legacy dapp browsers
      App.web3Provider = window.web3.currentProvider;
      web3 = new Web3(window.web3.currentProvider);
    } else {
      // If no injected web3 instance is detected, fall back to Ganache
      App.web3Provider = new Web3.providers.HttpProvider('http://localhost:7545');
      web3 = new Web3(App.web3Provider);
    }
    return await App.initContract();
  },

  // Initialize the contract
  initContract: async function() {
    // Fetch the contract artifact
    const response = await fetch('../build/contracts/VotingSystem.json');
    const votingSystem = await response.json();
    
    // Instantiate a new truffle contract from the artifact
    App.contracts.VotingSystem = TruffleContract(votingSystem);
    console.log(App.contracts.VotingSystem);
    // Connect provider to interact with contract
    App.contracts.VotingSystem.setProvider(App.web3Provider);

    return await App.render();
  },

  // Render the election results
  render: async function() {
    var votingInstance;
    var loader = $('#loader');
    var content = $('#content');

    loader.show();
    content.hide();

    // Load account data
    web3.eth.getCoinbase(function(err, account) {
      if (err === null) {
        App.account = account;
        $('#accountAddress').html('Your Account: ' + account);
      } else {
        console.error("Error fetching account:", err);
      }
    });

    // Load contract data
    App.contracts.VotingSystem.deployed().then(function(instance) {
      votingInstance = instance;
      return votingInstance.candidatesCount();
    }).then(function(candidatesCount) {
      var candidatesResults = $('#candidatesResults');
      candidatesResults.empty();
      var candidatesSelect = $('#candidatesSelect');
      candidatesSelect.empty();
      candidatesSelect.append('<option value="">Select a candidate</option>');

      App.fetchAndDisplayResults(candidatesResults, candidatesSelect, candidatesCount);
      
      loader.hide();
      content.show();
    }).catch(function(error) {
      console.warn("Error loading candidates:", error);
    });
  },

  // Fetch and display candidates and results from the blockchain
  fetchAndDisplayResults: async function(candidatesResults, candidatesSelect, candidatesCount) {
    const response = await fetch('../src/fetch_candidates.php'); 
    const databaseCandidates = await response.json();

    App.contracts.VotingSystem.deployed().then(async function(instance) {
      const uniqueCandidates = new Set();
      
      for (let i = 1; i <= candidatesCount; i++) {
        const candidateDetails = await instance.getCandidate(i);
        const candidateId = candidateDetails[0];
        const voteCount = candidateDetails[1];

        // Find the matching candidate in the database
        const matchedCandidate = databaseCandidates.find(candidate => candidate.candidate_student_id == candidateId);

        if (matchedCandidate) {
          const candidateName = matchedCandidate.candidate_name;

          // Render candidate Result
          var candidateTemplate = `<tr><th>${candidateId}</th><td>${candidateName}</td><td>${voteCount}</td></tr>`;
          candidatesResults.append(candidateTemplate);

          // Add candidates to dropdown
          var candidateOption = `<option value="${candidateId}">${candidateName}</option>`;
          candidatesSelect.append(candidateOption);
        }
      }
    }).catch(function(error) {
      console.error("Error deploying contract:", error);
    });
  },

  // Cast a vote for a candidate
  castVote: function() {
    var candidateId = $('#candidatesSelect').val();
    if (!candidateId) {
      console.error("Invalid candidate ID");
      alert("Invalid candidate ID. Please select a valid candidate.");
      return;
    }
    App.contracts.VotingSystem.deployed().then(function(instance) {
      console.log("Casting vote for candidate:", candidateId, "from account:", App.account);
      return instance.castVote(candidateId, { from: App.account });
    }).then(function(result) {
      console.log("Vote cast successfully:", result);
      $('#content').hide();
      $('#loader').show();
    }).catch(function(err) {
      console.error("Error casting vote:", err);
    });
  }
};

// Initialize the application when the window loads
$(function() {
  $(window).load(function() {
    App.init();
  });
});
