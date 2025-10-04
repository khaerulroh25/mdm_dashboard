<?php

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../models/User.php';

class AuthController
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new User();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function login()
    {
        include __DIR__ . '/../views/login.php';
    }

    public function loginAction($data)
    {
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';

        // langsung pakai login() di model
        $user = $this->userModel->login($username, $password);

        if ($user) {
            // simpan session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            header("Location: index.php?page=dashboard");
            exit;
        } else {
            $_SESSION['error'] = "Username atau password salah.";
            header("Location: index.php?page=login");
            exit;
        }
    }

    public function logout()
    {
        session_destroy();
        header("Location: index.php?page=login");
        exit;
    }
}
