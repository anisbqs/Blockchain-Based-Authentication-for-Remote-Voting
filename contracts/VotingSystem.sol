// SPDX-License-Identifier: MIT
pragma solidity ^0.8.0;

contract VotingSystem {
    struct Candidate {
        uint voteCount;
    }

    struct Voter {
        bool hasVoted;
    }

    mapping(address => Voter) public voters;
    mapping(uint => Candidate) public Candidates;
    uint public CandidatesCount;

    event votedEvent(
        uint indexed CandidateId
    );

    function addCandidates(uint[] memory _CandidateIds, uint _CandidatesCount) public {
        CandidatesCount+=_CandidatesCount;
            for (uint i=0; i <_CandidatesCount; i++) {
                Candidates[_CandidateIds[i]].voteCount = 0;
            }
    }

    function castVote(uint _CandidateId) public{
        require(!voters[msg.sender].hasVoted, "You have already voted.");

        voters[msg.sender].hasVoted = true;
        Candidates[_CandidateId].voteCount++;

        emit votedEvent(_CandidateId);
    }

    function getCandidateVoteCount(uint _CandidateId) public view returns (uint) {
        return Candidates[_CandidateId].voteCount;
    }
}