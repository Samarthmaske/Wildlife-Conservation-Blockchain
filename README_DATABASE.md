# MySQL Database Setup Guide

## Quick Start

### Step 1: Start XAMPP Services
1. Open XAMPP Control Panel
2. Start **Apache** and **MySQL** services

### Step 2: Create Database
1. Open **phpMyAdmin** at: `http://localhost/phpmyadmin`
2. Click on **SQL** tab
3. Copy and paste the contents from `config/schema.sql`
4. Click **Go** to execute

Alternatively, use the command line:
```bash
mysql -u root < config/schema.sql
```

### Step 3: Verify Installation
Open this URL to test: `http://localhost/Projects/BCTL/db-api.php?action=statistics`

You should see a JSON response with statistics.

## Database Credentials
- **Host:** localhost
- **User:** root
- **Password:** (empty)
- **Database:** bctl_animal_db

> ⚠️ Change these in `config/database.php` if you use different credentials

## Database Schema

### Tables Created:

**animals** - Store individual animal records
- id, species, common_name, scientific_name, habitat, status, location, notes, reporter

**conservation_actions** - Track actions taken for animals
- id, animal_id, action_type, description, action_date, officer_name, location

**blockchain_ledger** - Store blockchain records
- id, animal_id, action_id, transaction_hash, block_index, timestamp, data

**habitats** - Track habitat information
- id, name, habitat_type, location, area_sq_km, animal_count

**reports** - Aggregate conservation reports
- id, title, report_date, total_animals, total_actions, content

## API Endpoints

### Animals Management

**Get all animals:**
```
GET db-api.php?action=getAnimals&limit=50&offset=0
```

**Get animal by ID:**
```
GET db-api.php?action=getAnimal&id=1
```

**Get animals by species:**
```
GET db-api.php?action=getBySpecies&species=Lion
```

**Get animals by status:**
```
GET db-api.php?action=getByStatus&status=Healthy
```

**Add new animal:**
```
POST db-api.php?action=addAnimal
Body: {
  "species": "Tiger",
  "common_name": "Bengal Tiger",
  "scientific_name": "Panthera tigris",
  "habitat": "Forest",
  "status": "Healthy",
  "location": "India",
  "notes": "Spotted near village",
  "reporter": "John Doe"
}
```

**Update animal:**
```
PUT db-api.php?action=updateAnimal&id=1
Body: {
  "status": "Released",
  "notes": "Updated notes"
}
```

**Delete animal:**
```
DELETE db-api.php?action=deleteAnimal&id=1
```

### Conservation Actions

**Add action to animal:**
```
POST db-api.php?action=addAction&animal_id=1
Body: {
  "action_type": "Rescue",
  "description": "Rescued from trap",
  "officer_name": "Jane Smith",
  "location": "Forest Area A"
}
```

**Get actions for animal:**
```
GET db-api.php?action=getActions&animal_id=1
```

### Statistics

**Get statistics:**
```
GET db-api.php?action=statistics
```

Returns: total animals, total actions, breakdown by status and species

## Example Usage

### Add Animal via JavaScript:
```javascript
const animal = {
  species: "Elephant",
  common_name: "African Elephant",
  habitat: "Savanna",
  location: "Kenya",
  reporter: "Wildlife Team"
};

fetch('db-api.php?action=addAnimal', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify(animal)
})
.then(r => r.json())
.then(data => console.log(data));
```

### Get Animals via JavaScript:
```javascript
fetch('db-api.php?action=getAnimals')
  .then(r => r.json())
  .then(data => console.log(data.data));
```

## Troubleshooting

**Connection Refused Error:**
- Ensure MySQL is running in XAMPP
- Check XAMPP MySQL port (default 3306)

**Database Not Found:**
- Run the schema.sql file again in phpMyAdmin
- Check the database name in `config/database.php`

**Table Already Exists:**
- This is normal, schema.sql uses `IF NOT EXISTS`
- Tables won't be recreated if they already exist

## Next Steps

1. Update your frontend forms to save to database
2. Integrate with blockchain ledger
3. Add user authentication for reporters
4. Create reporting dashboard with statistics
