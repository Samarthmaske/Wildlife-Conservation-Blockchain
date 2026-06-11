// SPDX-License-Identifier: MIT
pragma solidity ^0.8.20;

contract Conservation {
    struct Report {
        string species;
        string location;
        string habitat;
        string action;
        string notes;
        string reporter;
        uint256 timestamp;
    }

    Report[] public reports;
    event ReportCreated(uint256 indexed reportId, string species, string location, string action, string reporter, uint256 timestamp);

    function addReport(
        string calldata species,
        string calldata location,
        string calldata habitat,
        string calldata action,
        string calldata notes,
        string calldata reporter
    ) external returns (uint256) {
        uint256 reportId = reports.length;
        reports.push(Report({
            species: species,
            location: location,
            habitat: habitat,
            action: action,
            notes: notes,
            reporter: reporter,
            timestamp: block.timestamp
        }));

        emit ReportCreated(reportId, species, location, action, reporter, block.timestamp);
        return reportId;
    }

    function totalReports() external view returns (uint256) {
        return reports.length;
    }

    function getReport(uint256 reportId) external view returns (Report memory) {
        require(reportId < reports.length, "Report does not exist");
        return reports[reportId];
    }
}
