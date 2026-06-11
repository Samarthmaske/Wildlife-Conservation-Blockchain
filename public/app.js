const reportForm = document.getElementById('report-form');
const reportsList = document.getElementById('reports-list');
const statusMessage = document.getElementById('status-message');
const refreshButton = document.getElementById('refresh-reports');
const mineButton = document.getElementById('mine-pending');
const pendingCountElement = document.getElementById('pending-count');
const verifyButton = document.getElementById('verify-chain');

const api = {
  getReports: async () => fetch('./api.php?action=getReports').then(res => res.json()),
  addReport: async (body) => fetch('./api.php?action=addReport', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(body)
  }).then(res => res.json()),
  minePending: async () => fetch('./api.php?action=minePending', {
    method: 'POST'
  }).then(res => res.json()),
  verifyChain: async () => fetch('./api.php?action=getReports').then(res => res.json())
};

function showStatus(message, type = 'info') {
  statusMessage.textContent = message;
  statusMessage.className = `status-message ${type}`;
}

function formatDate(timestamp) {
  return new Date(timestamp * 1000).toLocaleString();
}

function renderReports(blocks) {
  if (!blocks || !blocks.length) {
    reportsList.innerHTML = '<p>No mined blocks yet. Submit a conservation report and mine pending reports to create ledger blocks.</p>';
    return;
  }

  reportsList.innerHTML = blocks.map(block => {
    const transactions = block.transactions || [block];
    const transactionHtml = transactions.map(tx => `
      <div class="transaction-card">
        <div><strong>${tx.species}</strong> &mdash; ${tx.action}</div>
        <div>${tx.location} / ${tx.habitat}</div>
        <div>${tx.reporter} <span class="timestamp">${formatDate(tx.timestamp)}</span></div>
        <div class="notes">${tx.notes || 'No extra notes.'}</div>
        <div class="hash-line">Hash: ${tx.hash || 'N/A'}</div>
      </div>
    `).join('');

    return `
      <div class="block-card">
        <div class="block-header">
          <div><strong>Block #${block.blockIndex ?? block.id}</strong></div>
          <div>Hash: ${block.blockHash ?? block.hash}</div>
          <div>Prev: ${block.previousHash}</div>
          <div>Timestamp: ${formatDate(block.timestamp ?? block.timestamp)}</div>
        </div>
        <div class="block-transactions">
          ${transactionHtml}
        </div>
      </div>
    `;
  }).join('');
}

function renderPendingCount(count) {
  pendingCountElement.textContent = count;
  mineButton.disabled = count === 0;
}

async function refresh() {
  try {
    const data = await api.getReports();
    if (data.error) {
      showStatus(data.error, 'error');
      return;
    }
    renderReports(data.blocks);
    renderPendingCount(data.pendingCount ?? 0);
    showStatus(`Loaded ${data.blocks?.length ?? 0} mined block${data.blocks?.length === 1 ? '' : 's'}. Pending reports: ${data.pendingCount}. Ledger valid: ${data.valid ? 'yes' : 'no'}.`, data.valid ? 'success' : 'error');
  } catch (error) {
    showStatus('Unable to load on-chain reports.', 'error');
  }
}

reportForm.addEventListener('submit', async event => {
  event.preventDefault();
  const body = {
    species: document.getElementById('species').value.trim(),
    location: document.getElementById('location').value.trim(),
    habitat: document.getElementById('habitat').value.trim(),
    action: document.getElementById('action').value,
    notes: document.getElementById('notes').value.trim(),
    reporter: document.getElementById('reporter').value.trim()
  };

  const response = await api.addReport(body);
  if (response.error) {
    showStatus(response.error, 'error');
    return;
  }

  showStatus('Report queued for mining successfully.', 'success');
  reportForm.reset();
  refresh();
});

refreshButton.addEventListener('click', refresh);
mineButton.addEventListener('click', async () => {
  try {
    const result = await api.minePending();
    if (result.error) {
      showStatus(result.error, 'error');
      return;
    }
    showStatus(result.message, 'success');
    refresh();
  } catch (error) {
    showStatus('Unable to mine pending reports.', 'error');
  }
});

verifyButton.addEventListener('click', async () => {
  try {
    const data = await api.verifyChain();
    if (data.error) {
      showStatus(data.error, 'error');
      return;
    }
    showStatus(data.valid ? 'Ledger integrity is valid.' : 'Ledger integrity failed.', data.valid ? 'success' : 'error');
  } catch (error) {
    showStatus('Unable to verify ledger.', 'error');
  }
});

refresh();
