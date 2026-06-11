<?php
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Wildlife Conservation Blockchain</title>
  <link rel="stylesheet" href="public/style.css" />
</head>
<body>
  <div class="app-shell">
    <header>
      <h1>Animal Wildlife Conservation System</h1>
      <p>Record and secure conservation actions with a PHP-powered blockchain ledger.</p>
    </header>

    <section class="panel panel-actions">
      <div class="card">
        <h2>Submit Conservation Report</h2>
        <form id="report-form">
          <label>Species<div><input id="species" type="text" placeholder="Species name" required /></div></label>
          <label>Location<div><input id="location" type="text" placeholder="Location" required /></div></label>
          <label>Habitat<div><input id="habitat" type="text" placeholder="Habitat (forest, river, etc.)" /></div></label>
          <label>Action<div>
            <select id="action" required>
              <option value="Rescue">Rescue</option>
              <option value="Release">Release</option>
              <option value="Patrol">Patrol</option>
              <option value="Education">Education</option>
              <option value="Habitat Restoration">Habitat Restoration</option>
            </select>
          </div></label>
          <label>Notes<div><textarea id="notes" rows="3" placeholder="Details or observations"></textarea></div></label>
          <label>Reporter<div><input id="reporter" type="text" placeholder="Field officer or NGO" required /></div></label>
          <button type="submit">Submit Report</button>
        </form>
      </div>

      <div class="card card-status">
        <h2>Ledger Controls</h2>
        <button id="refresh-reports">Refresh Reports</button>
        <button id="mine-pending">Mine Pending Reports</button>
        <button id="verify-chain">Verify Ledger</button>
        <div class="status-summary">Pending reports: <span id="pending-count">0</span></div>
        <div id="status-message" class="status-message"></div>
      </div>
    </section>

    <section class="panel panel-data">
      <div class="card">
        <h2>On-Chain Conservation Ledger</h2>
        <div id="reports-list">Loading on-chain reports...</div>
      </div>
    </section>
  </div>
  <script src="public/app.js"></script>
</body>
</html>
