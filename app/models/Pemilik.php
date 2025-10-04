<?php

class Pemilik
{
    private $conn;
    private $table_name = "pemilik";

    public $id;
    public $device;
    public $nama;
    public $id_anggota; // tambahin biar ga error
    public $email;
    public $telepon;
    public $created_at;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Ambil semua pemilik
    public function getAll()
    {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getById($device)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE device = :device LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":device", $device);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    // Tambah pemilik
    public function create()
    {
        $query = "INSERT INTO " . $this->table_name . " (device,nama, id_anggota, telepon) 
                  VALUES (:device, :nama, :id_anggota, :telepon)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":device", $this->device);
        $stmt->bindParam(":nama", $this->nama);
        $stmt->bindParam(":id_anggota", $this->id_anggota);
        $stmt->bindParam(":telepon", $this->telepon);

        return $stmt->execute();
    }

    // Edit pemilik
    public function edit()
    {
        $query = "UPDATE " . $this->table_name . " 
                  SET device = :device, 
                      nama = :nama, 
                      id_anggota = :id_anggota, 
                      telepon = :telepon
                  WHERE device = :device";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":device", $this->device);
        $stmt->bindParam(":nama", $this->nama);
        $stmt->bindParam(":id_anggota", $this->id_anggota);
        $stmt->bindParam(":telepon", $this->telepon);


        return $stmt->execute();
    }
}
