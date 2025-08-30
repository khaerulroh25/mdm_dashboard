<?php

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../models/Pemilik.php';

class PemilikController
{
    private $pemilik;

    public function __construct()
    {
        $database = new Database();
        $db = $database->getConnection();
        $this->pemilik = new Pemilik($db);
    }

    public function index()
    {
        return $this->pemilik->getAll();
    }

    public function create()
    {
        // device dikirim via query param
        $deviceName = $_GET['device'] ?? null;
        include __DIR__ . '/../views/pemilikAdd.php';
    }

    public function edit()
    {
        // device dikirim via query param
        $deviceName = $_GET['device'] ?? null;
        include __DIR__ . '/../views/pemilikEdit.php';
    }

    public function store($data)
    {
        $this->pemilik->nama = $data['nama'];
        $this->pemilik->id_anggota = $data['id_anggota'];
        $this->pemilik->telepon = $data['telepon'];
        $this->pemilik->device = $data['device'] ?? null; // kalau mau simpan relasi ke device
        if ($this->pemilik->create()) {
            header("Location: index.php?page=devices"); // redirect ke daftar pemilik
            exit;
        } else {
            echo "❌ Gagal tambah pemilik!";
        }
    }

    public function edits($data)
    {
        $this->pemilik->nama = $data['nama'];
        $this->pemilik->id_anggota = $data['id_anggota'];
        $this->pemilik->telepon = $data['telepon'];
        $this->pemilik->device = $data['device'] ?? null; // kalau mau simpan relasi ke device
        if ($this->pemilik->edit()) {
            header("Location: index.php?page=devices"); // redirect ke daftar pemilik
            exit;
        } else {
            echo "❌ Gagal Edit pemilik!";
        }
    }

}
