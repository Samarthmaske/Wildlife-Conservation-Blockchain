<?php
define('IS_PRODUCTION', true);
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/contract.php';

$action = $_GET['action'] ?? null;
$contract = new ConservationContract();

try {
    if ($action === 'getReports') {
        echo json_encode([
            'blocks' => $contract->getReports(),
            'pendingCount' => $contract->getPendingCount(),
            'valid' => $contract->verifyChain(),
        ]);
        return;
    }

    if ($action === 'minePending') {
        $block = $contract->minePending();
        echo json_encode([
            'message' => 'Pending reports mined into a new block.',
            'block' => $block,
        ]);
        return;
    }

    if ($action === 'addReport') {
        $payload = json_decode(file_get_contents('php://input'), true);
        if (!is_array($payload)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON payload.']);
            return;
        }

        $required = ['species', 'location', 'action', 'reporter'];
        foreach ($required as $field) {
            if (empty(trim($payload[$field] ?? ''))) {
                http_response_code(400);
                echo json_encode(['error' => "$field is required."]);
                return;
            }
        }

        $hash = $contract->addReport($payload);
        echo json_encode([
            'message' => 'Report queued for mining in the conservation ledger.',
            'transactionHash' => $hash,
        ]);
        return;
    }

    http_response_code(404);
    echo json_encode(['error' => 'Action not found.']);
} catch (Throwable $error) {
    http_response_code(500);
    $errorMessage = defined('IS_PRODUCTION') && IS_PRODUCTION ? 'Internal Server Error' : $error->getMessage();
    echo json_encode(['error' => $errorMessage]);
}
