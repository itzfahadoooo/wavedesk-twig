<?php

namespace App\Controllers;

use App\Services\AuthService;

class AuthController {
    private AuthService $authService;

    public function __construct(AuthService $authService) {
        $this->authService = $authService;
    }

    public function login(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $email = trim($_POST['email'] ?? '');
                $password = $_POST['password'] ?? '';

                if (empty($email) || empty($password)) {
                    throw new \Exception('Email and password are required.');
                }

                $this->authService->login($email, $password);
                
                // Start fresh session
                session_regenerate_id(true);
                
                $_SESSION['flash'] = [
                    'type' => 'success',
                    'message' => 'Welcome back! You have successfully logged in.'
                ];

                header('Location: /?page=dashboard');
                exit;
            } catch (\Exception $e) {
                // Preserve the entered email but not password
                $_SESSION['flash'] = [
                    'type' => 'error',
                    'message' => $e->getMessage()
                ];
                $_SESSION['form_email'] = trim($_POST['email'] ?? '');
                
                header('Location: /?page=login');
                exit;
            }
        }
    }

    public function signup(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $email = trim($_POST['email'] ?? '');
                $password = $_POST['password'] ?? '';
                $confirmPassword = $_POST['confirm_password'] ?? '';

                if (empty($email) || empty($password)) {
                    throw new \Exception('Email and password are required.');
                }

                if ($password !== $confirmPassword) {
                    throw new \Exception('Passwords do not match.');
                }

                // Basic password validation
                if (strlen($password) < 8) {
                    throw new \Exception('Password must be at least 8 characters long.');
                }

                $this->authService->signup($email, $password);
                
                // Start fresh session
                session_regenerate_id(true);
                
                $_SESSION['flash'] = [
                    'type' => 'success',
                    'message' => 'Account created successfully! Welcome to WaveDesk.'
                ];

                header('Location: /?page=dashboard');
                exit;
            } catch (\Exception $e) {
                // Preserve the entered email but not passwords
                $_SESSION['flash'] = [
                    'type' => 'error',
                    'message' => $e->getMessage()
                ];
                $_SESSION['form_email'] = trim($_POST['email'] ?? '');
                
                header('Location: /?page=signup');
                exit;
            }
        }
    }

    public function logout(): void {
        $this->authService->logout();
        
        session_start(); // Restart session to store flash message
        $_SESSION['flash'] = [
            'type' => 'success',
            'message' => 'You have been logged out successfully.'
        ];

        header('Location: /?page=landing');
        exit;
    }
}