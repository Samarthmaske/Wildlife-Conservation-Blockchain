/**
 * Database API Client
 * JavaScript wrapper for database API endpoints
 */

class AnimalDatabase {
    constructor(apiUrl = 'db-api.php') {
        this.apiUrl = apiUrl;
    }

    /**
     * Get all animals
     */
    async getAnimals(limit = 50, offset = 0) {
        const response = await fetch(`${this.apiUrl}?action=getAnimals&limit=${limit}&offset=${offset}`);
        return response.json();
    }

    /**
     * Get animal by ID
     */
    async getAnimal(id) {
        const response = await fetch(`${this.apiUrl}?action=getAnimal&id=${id}`);
        return response.json();
    }

    /**
     * Get animals by species
     */
    async getBySpecies(species) {
        const response = await fetch(`${this.apiUrl}?action=getBySpecies&species=${encodeURIComponent(species)}`);
        return response.json();
    }

    /**
     * Get animals by status
     */
    async getByStatus(status) {
        const response = await fetch(`${this.apiUrl}?action=getByStatus&status=${encodeURIComponent(status)}`);
        return response.json();
    }

    /**
     * Add new animal
     */
    async addAnimal(data) {
        const response = await fetch(`${this.apiUrl}?action=addAnimal`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        return response.json();
    }

    /**
     * Update animal
     */
    async updateAnimal(id, data) {
        const response = await fetch(`${this.apiUrl}?action=updateAnimal&id=${id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        return response.json();
    }

    /**
     * Delete animal
     */
    async deleteAnimal(id) {
        const response = await fetch(`${this.apiUrl}?action=deleteAnimal&id=${id}`, {
            method: 'DELETE'
        });
        return response.json();
    }

    /**
     * Add conservation action to animal
     */
    async addAction(animalId, data) {
        const response = await fetch(`${this.apiUrl}?action=addAction&animal_id=${animalId}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        return response.json();
    }

    /**
     * Get actions for animal
     */
    async getActions(animalId) {
        const response = await fetch(`${this.apiUrl}?action=getActions&animal_id=${animalId}`);
        return response.json();
    }

    /**
     * Get statistics
     */
    async getStatistics() {
        const response = await fetch(`${this.apiUrl}?action=statistics`);
        return response.json();
    }
}

// Export for use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AnimalDatabase;
}
