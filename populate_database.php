<?php
/**
 * Populate Database Script
 * Checks if tables are empty and inserts sample data from reports.json if needed
 */

require_once __DIR__ . '/classes/Database.php';

$db = new Database();
$reportsFile = __DIR__ . '/data/reports.json';

echo "Checking database tables for existing records...\n";

// Check if tables have data
$tablesToCheck = ['animals', 'conservation_actions', 'blockchain_ledger', 'habitats', 'reports'];
$emptyTables = [];

foreach ($tablesToCheck as $table) {
    $result = $db->conn->query("SELECT COUNT(*) as count FROM $table");
    $count = $result->fetch_assoc()['count'];
    echo "Table '$table': $count records\n";

    if ($count == 0) {
        $emptyTables[] = $table;
    }
}

if (empty($emptyTables)) {
    echo "\nAll tables already have records. No data insertion needed.\n";
    exit(0);
}

echo "\nEmpty tables found: " . implode(', ', $emptyTables) . "\n";
echo "Inserting sample data from reports.json...\n";

// Read and parse JSON data
if (!file_exists($reportsFile)) {
    echo "Error: reports.json file not found at $reportsFile\n";
    exit(1);
}

$jsonData = file_get_contents($reportsFile);
$reports = json_decode($jsonData, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo "Error parsing JSON: " . json_last_error_msg() . "\n";
    exit(1);
}

$insertedCount = 0;

// Process each report
foreach ($reports as $report) {
    try {
        // Insert animal
        $animalData = [
            'species' => $report['species'],
            'habitat' => $report['habitat'],
            'location' => $report['location'],
            'notes' => $report['notes'],
            'reporter' => $report['reporter'],
            'date_recorded' => date('Y-m-d H:i:s', $report['timestamp'])
        ];

        $animalId = $db->addAnimal($animalData);
        echo "Inserted animal ID: $animalId\n";

        // Get the action that was automatically created
        $actions = $db->getAnimalActions($animalId);
        if (!empty($actions)) {
            $actionId = $actions[0]['id'];

            // Update the action with correct data
            $actionUpdate = [
                'action_type' => $report['action'],
                'description' => $report['notes'],
                'officer_name' => $report['reporter'],
                'location' => $report['location'],
                'action_date' => date('Y-m-d H:i:s', $report['timestamp'])
            ];

            $db->conn->query("UPDATE conservation_actions SET
                action_type = '{$db->conn->real_escape_string($actionUpdate['action_type'])}',
                description = '{$db->conn->real_escape_string($actionUpdate['description'])}',
                officer_name = '{$db->conn->real_escape_string($actionUpdate['officer_name'])}',
                location = '{$db->conn->real_escape_string($actionUpdate['location'])}',
                action_date = '{$actionUpdate['action_date']}'
                WHERE id = $actionId");

            // Update blockchain ledger with correct hash and data
            $dataJson = json_encode([
                'animal_id' => $animalId,
                'species' => $report['species'],
                'action' => $report['action'],
                'location' => $report['location'],
                'timestamp' => date('Y-m-d H:i:s', $report['timestamp'])
            ]);

            $db->conn->query("UPDATE blockchain_ledger SET
                transaction_hash = '{$db->conn->real_escape_string($report['hash'])}',
                block_index = {$report['id']},
                data = '{$db->conn->real_escape_string($dataJson)}',
                previous_hash = '{$db->conn->real_escape_string($report['previousHash'])}',
                timestamp = '" . date('Y-m-d H:i:s', $report['timestamp']) . "'
                WHERE animal_id = $animalId");

            echo "Updated action and blockchain record for animal ID: $animalId\n";
        }

        // Insert habitat if not exists
        $habitatName = $report['habitat'];
        $result = $db->conn->query("SELECT id FROM habitats WHERE name = '{$db->conn->real_escape_string($habitatName)}'");
        if ($result->num_rows == 0) {
            $db->conn->query("INSERT INTO habitats (name, location, conservation_status)
                VALUES ('{$db->conn->real_escape_string($habitatName)}',
                        '{$db->conn->real_escape_string($report['location'])}',
                        'Protected')");
            echo "Inserted habitat: $habitatName\n";
        }

        $insertedCount++;

    } catch (Exception $e) {
        echo "Error inserting report ID {$report['id']}: " . $e->getMessage() . "\n";
    }
}

// Create summary reports
try {
    $totalAnimals = count($reports);
    $totalActions = $totalAnimals; // One action per animal
    $content = "Sample data populated from reports.json\n";
    $content .= "Total animals: $totalAnimals\n";
    $content .= "Total actions: $totalActions\n";
    $content .= "Data source: Sample conservation reports";

    $db->conn->query("INSERT INTO reports (title, total_animals, total_actions, content, created_by)
        VALUES ('Sample Data Population Report', $totalAnimals, $totalActions,
                '{$db->conn->real_escape_string($content)}', 'System')");

    echo "Created summary report\n";

} catch (Exception $e) {
    echo "Error creating summary report: " . $e->getMessage() . "\n";
}

echo "\nData population completed!\n";
echo "Inserted $insertedCount records from " . count($reports) . " reports.\n";

?>