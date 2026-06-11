<?php

class ConservationContract
{
    private string $storageFile;
    private string $pendingFile;
    private array $reports = [];
    private array $pendingReports = [];

    public function __construct()
    {
        $this->storageFile = __DIR__ . '/data/reports.json';
        $this->pendingFile = __DIR__ . '/data/pending.json';
        $this->loadReports();
        $this->loadPendingReports();
    }

    private function loadReports(): void
    {
        if (!file_exists($this->storageFile)) {
            $this->reports = [];
            return;
        }
        $json = file_get_contents($this->storageFile);
        $this->reports = json_decode($json, true) ?: [];
    }

    private function loadPendingReports(): void
    {
        if (!file_exists($this->pendingFile)) {
            $this->pendingReports = [];
            return;
        }
        $json = file_get_contents($this->pendingFile);
        $this->pendingReports = json_decode($json, true) ?: [];
    }

    private function saveReports(): void
    {
        if (!is_dir(dirname($this->storageFile))) {
            mkdir(dirname($this->storageFile), 0755, true);
        }
        file_put_contents($this->storageFile, json_encode($this->reports, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    private function savePendingReports(): void
    {
        if (!is_dir(dirname($this->pendingFile))) {
            mkdir(dirname($this->pendingFile), 0755, true);
        }
        file_put_contents($this->pendingFile, json_encode($this->pendingReports, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    public function getReports(): array
    {
        return $this->reports;
    }

    public function totalReports(): int
    {
        return count($this->reports);
    }

    public function getPendingCount(): int
    {
        return count($this->pendingReports);
    }

    public function addReport(array $reportData): string
    {
        $report = [
            'id' => $this->getNextPendingId(),
            'species' => trim($reportData['species'] ?? ''),
            'location' => trim($reportData['location'] ?? ''),
            'habitat' => trim($reportData['habitat'] ?? 'Unknown'),
            'action' => trim($reportData['action'] ?? ''),
            'notes' => trim($reportData['notes'] ?? ''),
            'reporter' => trim($reportData['reporter'] ?? ''),
            'timestamp' => time(),
        ];

        $report['hash'] = $this->computeReportHash($report);
        $this->pendingReports[] = $report;
        $this->savePendingReports();

        return $report['hash'];
    }

    public function minePending(): array
    {
        if (empty($this->pendingReports)) {
            throw new Exception('No pending reports to mine.');
        }

        $block = [
            'blockIndex' => $this->totalReports(),
            'timestamp' => time(),
            'transactions' => $this->pendingReports,
            'previousHash' => $this->getLatestHash(),
        ];
        $block['blockHash'] = $this->computeBlockHash($block);

        $this->reports[] = $block;
        $this->pendingReports = [];
        $this->saveReports();
        $this->savePendingReports();

        return $block;
    }

    public function verifyChain(): bool
    {
        if (empty($this->reports)) {
            return true;
        }

        $mode = $this->resolveChainMode();
        if ($mode === 'legacy') {
            return $this->verifyLegacyChain();
        }

        if ($mode === 'block') {
            return $this->verifyBlockChain();
        }

        return $this->verifyMixedChain();
    }

    private function resolveChainMode(): string
    {
        $hasLegacy = false;
        $hasBlock = false;

        foreach ($this->reports as $item) {
            if ($this->isLegacyRecord($item)) {
                $hasLegacy = true;
            } else {
                $hasBlock = true;
            }

            if ($hasLegacy && $hasBlock) {
                return 'mixed';
            }
        }

        return $hasLegacy ? 'legacy' : 'block';
    }

    private function verifyLegacyChain(): bool
    {
        $previousHash = '0';
        foreach ($this->reports as $report) {
            if (!$this->isLegacyRecord($report)) {
                return false;
            }

            $reportCopy = $report;
            unset($reportCopy['hash']);
            if (($report['previousHash'] ?? '') !== $previousHash) {
                return false;
            }
            if (($report['hash'] ?? '') !== $this->computeHash($reportCopy)) {
                return false;
            }
            $previousHash = $report['hash'];
        }

        return true;
    }

    private function verifyBlockChain(): bool
    {
        $previousHash = '0';
        foreach ($this->reports as $block) {
            if ($this->isLegacyRecord($block)) {
                return false;
            }
            if (($block['previousHash'] ?? '') !== $previousHash) {
                return false;
            }
            if (($block['blockHash'] ?? '') !== $this->computeBlockHash($block)) {
                return false;
            }
            $previousHash = $block['blockHash'];
        }

        return true;
    }

    private function verifyMixedChain(): bool
    {
        $previousHash = '0';
        $index = 0;
        $total = count($this->reports);

        while ($index < $total && $this->isLegacyRecord($this->reports[$index])) {
            $report = $this->reports[$index];
            $reportCopy = $report;
            unset($reportCopy['hash']);
            if (($report['previousHash'] ?? '') !== $previousHash) {
                return false;
            }
            if (($report['hash'] ?? '') !== $this->computeHash($reportCopy)) {
                return false;
            }
            $previousHash = $report['hash'];
            $index++;
        }

        while ($index < $total) {
            $block = $this->reports[$index];
            if ($this->isLegacyRecord($block)) {
                return false;
            }
            if (($block['previousHash'] ?? '') !== $previousHash) {
                return false;
            }
            if (($block['blockHash'] ?? '') !== $this->computeBlockHash($block)) {
                return false;
            }
            $previousHash = $block['blockHash'];
            $index++;
        }

        return true;
    }

    private function computeBlockHash(array $block): string
    {
        $transactionHashes = array_map(function ($transaction) {
            return $transaction['hash'] ?? '';
        }, $block['transactions'] ?? []);

        return hash('sha256', implode('|', [
            $block['blockIndex'],
            $block['timestamp'],
            $block['previousHash'],
            implode(',', $transactionHashes),
        ]));
    }

    private function computeReportHash(array $report): string
    {
        return hash('sha256', implode('|', [
            $report['id'],
            $report['species'],
            $report['location'],
            $report['habitat'],
            $report['action'],
            $report['notes'],
            $report['reporter'],
            $report['timestamp'],
        ]));
    }

    private function isLegacyRecord(array $item): bool
    {
        return !isset($item['transactions']);
    }

    private function getLatestHash(): string
    {
        if (empty($this->reports)) {
            return '0';
        }

        $latest = end($this->reports);
        if (isset($latest['blockHash'])) {
            return $latest['blockHash'];
        }

        return $latest['hash'] ?? '0';
    }

    private function getNextPendingId(): int
    {
        return count($this->pendingReports);
    }

    private function computeHash(array $report): string
    {
        return hash('sha256', implode('|', [
            $report['id'],
            $report['species'],
            $report['location'],
            $report['habitat'],
            $report['action'],
            $report['notes'],
            $report['reporter'],
            $report['timestamp'],
            $report['previousHash'] ?? '0',
        ]));
    }
}
