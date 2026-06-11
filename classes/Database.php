<?php
/**
 * Database Class
 * Handles all database operations for animal conservation system
 */

require_once __DIR__ . '/../config/database.php';

class Database {
    private $conn;
    
    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }
    
    /**
     * Add a new animal to the database and populate related tables
     */
    public function addAnimal($data) {
        $species = $this->conn->real_escape_string($data['species']);
        $common_name = isset($data['common_name']) ? $this->conn->real_escape_string($data['common_name']) : null;
        $scientific_name = isset($data['scientific_name']) ? $this->conn->real_escape_string($data['scientific_name']) : null;
        $habitat = isset($data['habitat']) ? $this->conn->real_escape_string($data['habitat']) : null;
        $status = isset($data['status']) ? $this->conn->real_escape_string($data['status']) : 'Healthy';
        $location = isset($data['location']) ? $this->conn->real_escape_string($data['location']) : null;
        $notes = isset($data['notes']) ? $this->conn->real_escape_string($data['notes']) : null;
        $reporter = isset($data['reporter']) ? $this->conn->real_escape_string($data['reporter']) : null;
        
        $sql = "INSERT INTO animals (species, common_name, scientific_name, habitat, status, location, notes, reporter) 
                VALUES ('$species', " . ($common_name ? "'$common_name'" : "NULL") . ", " . 
                ($scientific_name ? "'$scientific_name'" : "NULL") . ", " . 
                ($habitat ? "'$habitat'" : "NULL") . ", '$status', " . 
                ($location ? "'$location'" : "NULL") . ", " . 
                ($notes ? "'$notes'" : "NULL") . ", " . 
                ($reporter ? "'$reporter'" : "NULL") . ")";
        
        if ($this->conn->query($sql) === TRUE) {
            $animal_id = $this->conn->insert_id;
            
            // Automatically create a default conservation action
            $action_type = isset($data['action']) ? $this->conn->real_escape_string($data['action']) : 'Patrol';
            $description = $notes;
            $officer_name = $reporter;
            $action_location = $location;
            
            $action_sql = "INSERT INTO conservation_actions (animal_id, action_type, description, officer_name, location) 
                          VALUES ($animal_id, '$action_type', " . 
                          ($description ? "'$description'" : "NULL") . ", " . 
                          ($officer_name ? "'$officer_name'" : "NULL") . ", " . 
                          ($action_location ? "'$action_location'" : "NULL") . ")";
            
            if ($this->conn->query($action_sql) === TRUE) {
                $action_id = $this->conn->insert_id;
                
                // Create blockchain ledger entry
                $data_json = json_encode([
                    'animal_id' => $animal_id,
                    'species' => $species,
                    'action' => $action_type,
                    'status' => $status,
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
                $escapedData = $this->conn->real_escape_string($data_json);
                $transaction_hash = 'tx_' . hash('sha256', $animal_id . $action_id . time());
                $escapedTxnHash = $this->conn->real_escape_string($transaction_hash);
                $previous_hash = $this->getPreviousHash();
                $escapedPrevious = $this->conn->real_escape_string($previous_hash);
                
                $ledger_sql = "INSERT INTO blockchain_ledger (animal_id, action_id, transaction_hash, block_index, data, previous_hash) ";
                $ledger_sql .= "VALUES ($animal_id, $action_id, '$escapedTxnHash', " . $this->getNextBlockIndex() . ", '$escapedData', '$escapedPrevious')";
                $this->conn->query($ledger_sql);

                // Create summary report entry for the new animal
                try {
                    $this->createReportFromAnimal($animal_id);
                } catch (Exception $reportError) {
                    // Do not abort the main insert if report creation fails.
                }

                // Update habitat summary data
                try {
                    $this->updateHabitatRecord($habitat, $location);
                } catch (Exception $habitatError) {
                    // Do not abort the main insert if habitat update fails.
                }
            }
            
            return $animal_id;
        }
        throw new Exception("Error adding animal: " . $this->conn->error);
    }
    
    /**
     * Get next block index for blockchain
     */
    public function getNextBlockIndex() {
        $result = $this->conn->query("SELECT MAX(block_index) as max_index FROM blockchain_ledger");
        $row = $result->fetch_assoc();
        return ($row['max_index'] ?? 0) + 1;
    }
    
    /**
     * Get previous hash for blockchain
     */
    public function getPreviousHash() {
        $result = $this->conn->query("SELECT transaction_hash FROM blockchain_ledger ORDER BY block_index DESC LIMIT 1");
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['transaction_hash'];
        }
        return '0';
    }

    /**
     * Add a blockchain ledger entry
     */
    public function addLedgerEntry($animal_id, $action_id, $data_json, $previous_hash) {
        $animal_id = (int) $animal_id;
        $action_id = (int) $action_id;
        $transaction_hash = 'tx_' . hash('sha256', $animal_id . $action_id . time());
        $block_index = $this->getNextBlockIndex();
        $escapedData = $this->conn->real_escape_string($data_json);
        $escapedTxnHash = $this->conn->real_escape_string($transaction_hash);
        $escapedPrevious = $this->conn->real_escape_string($previous_hash);

        $sql = "INSERT INTO blockchain_ledger (animal_id, action_id, transaction_hash, block_index, data, previous_hash) ";
        $sql .= "VALUES ($animal_id, $action_id, '$escapedTxnHash', $block_index, '$escapedData', '$escapedPrevious')";

        if ($this->conn->query($sql) === TRUE) {
            return $this->conn->insert_id;
        }

        throw new Exception('Error adding ledger entry: ' . $this->conn->error);
    }

    /**
     * Create or update habitat summary data after new animal record
     */
    public function updateHabitatRecord($habitat, $location = null) {
        $habitatName = $habitat ? $this->conn->real_escape_string($habitat) : 'Unknown';
        $locationValue = $location ? $this->conn->real_escape_string($location) : null;

        $result = $this->conn->query("SELECT id, animal_count FROM habitats WHERE name = '$habitatName' LIMIT 1");
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $count = ((int)$row['animal_count']) + 1;
            $sql = "UPDATE habitats SET animal_count = $count";
            if ($locationValue !== null) {
                $sql .= ", location = '$locationValue'";
            }
            $sql .= " WHERE id = {$row['id']}";
            $this->conn->query($sql);
            return $row['id'];
        }

        $sql = "INSERT INTO habitats (name, location, animal_count) VALUES ('$habitatName', " .
               ($locationValue ? "'$locationValue'" : "NULL") . ", 1)";
        if ($this->conn->query($sql) === TRUE) {
            return $this->conn->insert_id;
        }

        throw new Exception('Error updating habitat record: ' . $this->conn->error);
    }

    /**
     * Get all reports
     */
    public function getAllReports() {
        $sql = "SELECT * FROM reports ORDER BY report_date DESC";
        $result = $this->conn->query($sql);
        
        if ($result->num_rows === 0) {
            return [];
        }
        
        $reports = [];
        while($row = $result->fetch_assoc()) {
            $reports[] = $row;
        }
        return $reports;
    }
    
    /**
     * Get all animals
     */
    public function getAnimals($limit = null, $offset = 0) {
        $sql = "SELECT * FROM animals ORDER BY created_at DESC";
        if ($limit) {
            $sql .= " LIMIT $limit OFFSET $offset";
        }
        
        $result = $this->conn->query($sql);
        if ($result->num_rows === 0) {
            return [];
        }
        
        $animals = [];
        while($row = $result->fetch_assoc()) {
            $animals[] = $row;
        }
        return $animals;
    }
    
    /**
     * Get animal by ID
     */
    public function getAnimalById($id) {
        $id = (int)$id;
        $sql = "SELECT * FROM animals WHERE id = $id";
        $result = $this->conn->query($sql);
        
        if ($result->num_rows === 0) {
            return null;
        }
        return $result->fetch_assoc();
    }
    
    /**
     * Get animals by species
     */
    public function getAnimalsBySpecies($species) {
        $species = $this->conn->real_escape_string($species);
        $sql = "SELECT * FROM animals WHERE species LIKE '%$species%' ORDER BY created_at DESC";
        $result = $this->conn->query($sql);
        
        if ($result->num_rows === 0) {
            return [];
        }
        
        $animals = [];
        while($row = $result->fetch_assoc()) {
            $animals[] = $row;
        }
        return $animals;
    }
    
    /**
     * Get animals by status
     */
    public function getAnimalsByStatus($status) {
        $status = $this->conn->real_escape_string($status);
        $sql = "SELECT * FROM animals WHERE status = '$status' ORDER BY created_at DESC";
        $result = $this->conn->query($sql);
        
        if ($result->num_rows === 0) {
            return [];
        }
        
        $animals = [];
        while($row = $result->fetch_assoc()) {
            $animals[] = $row;
        }
        return $animals;
    }
    
    /**
     * Update animal
     */
    public function updateAnimal($id, $data) {
        $id = (int)$id;
        $updates = [];
        
        $allowed_fields = ['species', 'common_name', 'scientific_name', 'habitat', 'status', 'location', 'notes', 'reporter'];
        foreach ($allowed_fields as $field) {
            if (isset($data[$field])) {
                $value = $this->conn->real_escape_string($data[$field]);
                $updates[] = "`$field` = '$value'";
            }
        }
        
        if (empty($updates)) {
            throw new Exception("No valid fields to update");
        }
        
        $sql = "UPDATE animals SET " . implode(", ", $updates) . " WHERE id = $id";
        if ($this->conn->query($sql) === TRUE) {
            return $this->getAnimalById($id);
        }
        throw new Exception("Error updating animal: " . $this->conn->error);
    }
    
    /**
     * Delete animal
     */
    public function deleteAnimal($id) {
        $id = (int)$id;
        $sql = "DELETE FROM animals WHERE id = $id";
        
        if ($this->conn->query($sql) === TRUE) {
            return true;
        }
        throw new Exception("Error deleting animal: " . $this->conn->error);
    }
    
    /**
     * Add conservation action
     */
    public function addAction($animal_id, $data) {
        $animal_id = (int)$animal_id;
        $action_type = $this->conn->real_escape_string($data['action_type']);
        $description = isset($data['description']) ? $this->conn->real_escape_string($data['description']) : null;
        $officer_name = isset($data['officer_name']) ? $this->conn->real_escape_string($data['officer_name']) : null;
        $location = isset($data['location']) ? $this->conn->real_escape_string($data['location']) : null;
        
        $sql = "INSERT INTO conservation_actions (animal_id, action_type, description, officer_name, location) 
                VALUES ($animal_id, '$action_type', " . 
                ($description ? "'$description'" : "NULL") . ", " . 
                ($officer_name ? "'$officer_name'" : "NULL") . ", " . 
                ($location ? "'$location'" : "NULL") . ")";
        
        if ($this->conn->query($sql) === TRUE) {
            return $this->conn->insert_id;
        }
        throw new Exception("Error adding action: " . $this->conn->error);
    }
    
    /**
     * Get actions for animal
     */
    public function getAnimalActions($animal_id) {
        $animal_id = (int)$animal_id;
        $sql = "SELECT * FROM conservation_actions WHERE animal_id = $animal_id ORDER BY action_date DESC";
        $result = $this->conn->query($sql);
        
        if ($result->num_rows === 0) {
            return [];
        }
        
        $actions = [];
        while($row = $result->fetch_assoc()) {
            $actions[] = $row;
        }
        return $actions;
    }
    
    /**
     * Get statistics
     */
    public function getStatistics() {
        $total_animals = $this->conn->query("SELECT COUNT(*) as count FROM animals")->fetch_assoc()['count'];
        $total_actions = $this->conn->query("SELECT COUNT(*) as count FROM conservation_actions")->fetch_assoc()['count'];
        $total_ledger = $this->conn->query("SELECT COUNT(*) as count FROM blockchain_ledger")->fetch_assoc()['count'];
        $by_status = $this->conn->query("SELECT status, COUNT(*) as count FROM animals GROUP BY status")->fetch_all(MYSQLI_ASSOC);
        $by_species = $this->conn->query("SELECT species, COUNT(*) as count FROM animals GROUP BY species ORDER BY count DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
        
        return [
            'total_animals' => $total_animals,
            'total_actions' => $total_actions,
            'blockchain_records' => $total_ledger,
            'by_status' => $by_status,
            'top_species' => $by_species
        ];
    }
    
    /**
     * Get animal with all related data (actions, habitats, blockchain records)
     */
    public function getAnimalWithRelated($id) {
        $id = (int)$id;
        $animal = $this->getAnimalById($id);
        
        if (!$animal) {
            return null;
        }
        
        // Get conservation actions
        $actions = $this->getAnimalActions($id);
        
        // Get blockchain records for this animal
        $ledger_result = $this->conn->query("SELECT * FROM blockchain_ledger WHERE animal_id = $id ORDER BY block_index ASC");
        $blockchain_records = [];
        if ($ledger_result) {
            while($row = $ledger_result->fetch_assoc()) {
                $blockchain_records[] = $row;
            }
        }
        
        // Get related habitat
        $habitat_result = $this->conn->query("SELECT * FROM habitats WHERE name LIKE '%$animal[habitat]%' LIMIT 1");
        $habitat = ($habitat_result && $habitat_result->num_rows > 0) ? $habitat_result->fetch_assoc() : null;
        
        return [
            'animal' => $animal,
            'actions' => $actions,
            'blockchain_records' => $blockchain_records,
            'habitat' => $habitat
        ];
    }
    
    /**
     * Create or update report from animal data
     */
    public function createReportFromAnimal($animal_id) {
        $animal_id = (int)$animal_id;
        $animal = $this->getAnimalById($animal_id);
        
        if (!$animal) {
            throw new Exception("Animal not found");
        }
        
        $action_count = $this->conn->query("SELECT COUNT(*) as count FROM conservation_actions WHERE animal_id = $animal_id")->fetch_assoc()['count'];
        
        $title = "Report: " . $animal['species'] . " - " . $animal['status'];
        $content = "Animal: " . $animal['common_name'] . " (" . $animal['species'] . ")\n";
        $content .= "Status: " . $animal['status'] . "\n";
        $content .= "Location: " . $animal['location'] . "\n";
        $content .= "Notes: " . $animal['notes'] . "\n";
        $content .= "Actions Taken: " . $action_count;
        
        $created_by = $animal['reporter'];

        $titleEscaped = $this->conn->real_escape_string($title);
        $contentEscaped = $this->conn->real_escape_string($content);
        $createdByEscaped = $this->conn->real_escape_string($created_by);
        
        $sql = "INSERT INTO reports (title, total_animals, total_actions, content, created_by) 
                VALUES ('$titleEscaped', 1, $action_count, '$contentEscaped', '$createdByEscaped')";
        
        if ($this->conn->query($sql) === TRUE) {
            return $this->conn->insert_id;
        }
        throw new Exception("Error creating report: " . $this->conn->error);
    }
}
?>
