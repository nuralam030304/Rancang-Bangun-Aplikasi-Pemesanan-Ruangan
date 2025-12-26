<?php
require __DIR__ . '/app/db.php';

try {
    echo "Adding latitude and longitude columns to rooms table...\n";
    
    $sql = "ALTER TABLE rooms 
            ADD COLUMN latitude DECIMAL(10, 8) NULL AFTER image,
            ADD COLUMN longitude DECIMAL(11, 8) NULL AFTER latitude";
            
    $pdo->exec($sql);
    
    echo "Successfully added columns!\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), "Duplicate column name") !== false) {
        echo "Columns already exist. Skipping.\n";
    } else {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
