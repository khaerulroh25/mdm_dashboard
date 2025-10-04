<?php

require_once __DIR__ . '/../../config/Database.php';

class Aplikasi
{
    private $db;

    public function __construct($db){
         $this->db = $db;
    }
    public function getAll()
    {
        $sql = "SELECT * FROM apps ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC); // ganti fetch_all jadi fetchAll
    }

    public function create($name, $package)
    {
        $sql = "INSERT INTO apps (app_name, package_name) VALUES (:name, :package)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':name' => $name,
            ':package' => $package
        ]);
    }

    public function delete($id)
    {
        $sql = "DELETE FROM apps WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}
