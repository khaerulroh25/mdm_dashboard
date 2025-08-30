<?php

require_once __DIR__ . '/../models/User.php';

class UserController
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new User();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    public function index()
    {
        if ($_SESSION['role'] !== 'admin') {
            die("Akses ditolak!");
        }
        $users = $this->userModel->getAll();
        include __DIR__ . '/../views/list_user.php';
    }

    public function addForm()
    {
        if ($_SESSION['role'] !== 'admin') {
            die("Akses ditolak!");
        }
        include __DIR__ . '/../views/user_add.php';
    }

    public function addAction()
    {
        if ($_SESSION['role'] !== 'admin') {
            die("Akses ditolak!");
        }

        $username = $_POST['username'];
        $password = $_POST['password'];
        $role     = $_POST['role'];

        if ($this->userModel->create($username, $password, $role)) {
            $_SESSION['success'] = "✅ User berhasil ditambahkan.";
        } else {
            $_SESSION['error'] = "❌ Gagal menambahkan user.";
        }

        header("Location: index.php?page=user_add");
        exit;
    }
    public function editForm($id)
    {
        if ($_SESSION['role'] !== 'admin') {
            die("Akses ditolak!");
        }
        $user = $this->userModel->getById($id);
        include __DIR__ . '/../views/edit_user.php';
    }

    public function update($data)
    {
        if ($_SESSION['role'] !== 'admin') {
            die("Akses ditolak!");
        }

        $id       = $data['id'];
        $username = $data['username'];
        $password = $data['password']; // boleh kosong
        $role     = $data['role'];

        if ($this->userModel->update($id, $username, $password, $role)) {
            $_SESSION['success'] = "User berhasil diperbarui.";
        } else {
            $_SESSION['error'] = "Gagal memperbarui user.";
        }

        header("Location: index.php?page=user_list");
        exit;
    }

    public function delete($id)
    {
        if ($_SESSION['role'] !== 'admin') {
            die("Akses ditolak!");
        }

        if ($this->userModel->delete($id)) {
            $_SESSION['success'] = "User berhasil dihapus.";
        } else {
            $_SESSION['error'] = "Gagal menghapus user.";
        }

        header("Location: index.php?page=user_list");
        exit;
    }
}
