<?php
// database.php
class Database
{
    private $pdo;

    public function __construct()
    {
        try {
            $this->pdo = new PDO("mysql:host=localhost;dbname=families_tracking", "root", "", [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }

    public function addFamily($data)
    {
        $sql = "INSERT INTO families (name, city, phone, latitude, longitude, image_path) 
                VALUES (:name, :city, :phone, :latitude, :longitude, :image_path)";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($data);
    }

    public function getAllFamilies()
    {
        $stmt = $this->pdo->query("SELECT * FROM families");
        return $stmt->fetchAll();
    }
}

// create_tables.php
function createTables($pdo)
{
    $sql = "CREATE TABLE IF NOT EXISTS families (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        city VARCHAR(255) NOT NULL,
        phone VARCHAR(20),
        latitude DECIMAL(10, 8) NOT NULL,
        longitude DECIMAL(11, 8) NOT NULL,
        image_path VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";

    $pdo->exec($sql);
}
