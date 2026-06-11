<?php
/**
 * Database API
 * RESTful endpoints for animal conservation database operations
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/classes/Database.php';

$action = $_GET['action'] ?? null;
$db = new Database();

try {
    // Get all animals
    if ($action === 'getAnimals') {
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
        $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
        
        echo json_encode([
            'success' => true,
            'data' => $db->getAnimals($limit, $offset)
        ]);
        return;
    }

    // Get animal by ID
    if ($action === 'getAnimal') {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Animal ID is required']);
            return;
        }
        
        $animal = $db->getAnimalById($id);
        if (!$animal) {
            http_response_code(404);
            echo json_encode(['error' => 'Animal not found']);
            return;
        }
        
        $actions = $db->getAnimalActions($id);
        echo json_encode([
            'success' => true,
            'data' => $animal,
            'actions' => $actions
        ]);
        return;
    }

    // Get animal with all related data (actions, blockchain, habitat)
    if ($action === 'getAnimalFull') {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Animal ID is required']);
            return;
        }
        
        $related = $db->getAnimalWithRelated($id);
        if (!$related) {
            http_response_code(404);
            echo json_encode(['error' => 'Animal not found']);
            return;
        }
        
        echo json_encode([
            'success' => true,
            'data' => $related
        ]);
        return;
    }

    // Get animals by species
    if ($action === 'getBySpecies') {
        $species = $_GET['species'] ?? null;
        if (!$species) {
            http_response_code(400);
            echo json_encode(['error' => 'Species is required']);
            return;
        }
        
        echo json_encode([
            'success' => true,
            'data' => $db->getAnimalsBySpecies($species)
        ]);
        return;
    }

    // Get animals by status
    if ($action === 'getByStatus') {
        $status = $_GET['status'] ?? null;
        if (!$status) {
            http_response_code(400);
            echo json_encode(['error' => 'Status is required']);
            return;
        }
        
        echo json_encode([
            'success' => true,
            'data' => $db->getAnimalsByStatus($status)
        ]);
        return;
    }

    // Add new animal
    if ($action === 'addAnimal') {
        $payload = json_decode(file_get_contents('php://input'), true);
        if (!is_array($payload)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON payload']);
            return;
        }

        if (empty($payload['species'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Species is required']);
            return;
        }

        $animal_id = $db->addAnimal($payload);
        echo json_encode([
            'success' => true,
            'message' => 'Animal added to database',
            'animal_id' => $animal_id
        ]);
        return;
    }

    // Update animal
    if ($action === 'updateAnimal') {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Animal ID is required']);
            return;
        }

        $payload = json_decode(file_get_contents('php://input'), true);
        if (!is_array($payload)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON payload']);
            return;
        }

        $updated = $db->updateAnimal($id, $payload);
        echo json_encode([
            'success' => true,
            'message' => 'Animal updated',
            'data' => $updated
        ]);
        return;
    }

    // Delete animal
    if ($action === 'deleteAnimal') {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Animal ID is required']);
            return;
        }

        $db->deleteAnimal($id);
        echo json_encode([
            'success' => true,
            'message' => 'Animal deleted'
        ]);
        return;
    }

    // Add conservation action
    if ($action === 'addAction') {
        $animal_id = $_GET['animal_id'] ?? null;
        if (!$animal_id) {
            http_response_code(400);
            echo json_encode(['error' => 'Animal ID is required']);
            return;
        }

        $payload = json_decode(file_get_contents('php://input'), true);
        if (!is_array($payload)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON payload']);
            return;
        }

        if (empty($payload['action_type'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Action type is required']);
            return;
        }

        $action_id = $db->addAction($animal_id, $payload);
        
        // Create blockchain ledger entry for this action
        $data_json = json_encode([
            'animal_id' => $animal_id,
            'action_type' => $payload['action_type'],
            'description' => $payload['description'] ?? null,
            'officer_name' => $payload['officer_name'] ?? null,
            'location' => $payload['location'] ?? null,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        $previous_hash = $db->getPreviousHash();
        $db->addLedgerEntry($animal_id, $action_id, $data_json, $previous_hash);

        echo json_encode([
            'success' => true,
            'message' => 'Action added and ledger updated',
            'action_id' => $action_id
        ]);
        return;
    }

    // Get animal actions
    if ($action === 'getActions') {
        $animal_id = $_GET['animal_id'] ?? null;
        if (!$animal_id) {
            http_response_code(400);
            echo json_encode(['error' => 'Animal ID is required']);
            return;
        }

        echo json_encode([
            'success' => true,
            'data' => $db->getAnimalActions($animal_id)
        ]);
        return;
    }

    // Get all reports
    if ($action === 'getReports') {
        $reports = $db->getAllReports();
        echo json_encode([
            'success' => true,
            'data' => $reports
        ]);
        return;
    }

    // Get statistics
    if ($action === 'statistics') {
        echo json_encode([
            'success' => true,
            'data' => $db->getStatistics()
        ]);
        return;
    }

    // Create report from animal data
    if ($action === 'createReport') {
        $animal_id = $_GET['animal_id'] ?? null;
        if (!$animal_id) {
            http_response_code(400);
            echo json_encode(['error' => 'Animal ID is required']);
            return;
        }

        $report_id = $db->createReportFromAnimal($animal_id);
        echo json_encode([
            'success' => true,
            'message' => 'Report created for animal',
            'report_id' => $report_id
        ]);
        return;
    }

    http_response_code(404);
    echo json_encode([
        'error' => 'Action not found',
        'available_actions' => [
            'getAnimals', 'getAnimal', 'getAnimalFull', 'getBySpecies', 'getByStatus',
            'addAnimal', 'updateAnimal', 'deleteAnimal',
            'addAction', 'getActions', 'createReport', 'statistics'
        ]
    ]);
} catch (Throwable $error) {
    http_response_code(500);
    echo json_encode(['error' => $error->getMessage()]);
}
?>
