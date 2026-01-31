<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;

class AuthController extends Controller
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function showLogin()
    {
        $this->render('auth/login');
    }

    public function login()
    {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $user = $this->userModel->findByEmail($email);

        if ($user && password_verify($password, $user['password_hash'])) {
            if ($user['status'] === 'suspended') {
                $this->render('auth/login', ['error' => 'Your account has been suspended.']);
                return;
            }

            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role'] = $user['role'];
            $this->userModel->updateLastLogin($user['user_id']);
            
            $this->redirect('/');
        } else {
            $this->render('auth/login', ['error' => 'Invalid email or password.']);
        }
    }

    public function showRegister()
    {
        $this->render('auth/register');
    }

    public function register()
    {
        $role = $_POST['role'] ?? 'client';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $password = $_POST['password'] ?? '';

        if ($this->userModel->findByEmail($email)) {
            $this->render('auth/register', ['error' => 'Email already exists.']);
            return;
        }

        $userId = $this->userModel->create([
            'role' => $role,
            'email' => $email,
            'phone' => $phone,
            'password' => $password,
            'status' => 'active' // For now, auto-activate
        ]);

        if ($userId) {
            $_SESSION['user_id'] = $userId;
            $_SESSION['role'] = $role;
            $this->redirect('/');
        } else {
            $this->render('auth/register', ['error' => 'Registration failed.']);
        }
    }

    public function logout()
    {
        session_destroy();
        $this->redirect('/login');
    }
}
