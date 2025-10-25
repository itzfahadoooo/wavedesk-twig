<?php

namespace App\Services;

use App\Config\Database;
use PDO;
use PDOException;

class AuthService {
    private PDO $db;
    private const DEMO_USER = [
        'email' => 'test@example.com',
        'password' => 'Password123!'
    ];
    private const AUTO_LOGIN = false;

    public function __construct() {
        // Initialize session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Get database connection
        $this->db = Database::getConnection();

        // Pre-seed demo user
        $this->seedDemoUser();

        // Auto-login if enabled
        if (self::AUTO_LOGIN && !$this->isAuthenticated()) {
            $this->autoLogin();
        }
    }

    private function seedDemoUser(): void {
        try {
            $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([self::DEMO_USER['email']]);
            
            if (!$stmt->fetch()) {
                // Hash password properly
                $hashedPassword = password_hash(self::DEMO_USER['password'], PASSWORD_BCRYPT);
                
                $stmt = $this->db->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
                $stmt->execute([self::DEMO_USER['email'], $hashedPassword]);
            }
        } catch (PDOException $e) {
            error_log("Failed to seed demo user: " . $e->getMessage());
        }
    }

    private function autoLogin(): void {
        try {
            $stmt = $this->db->prepare("SELECT id, email FROM users WHERE email = ?");
            $stmt->execute([self::DEMO_USER['email']]);
            $user = $stmt->fetch();
            
            if ($user) {
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'email' => $user['email']
                ];
            }
        } catch (PDOException $e) {
            error_log("Auto-login failed: " . $e->getMessage());
        }
    }

    public function login(string $email, string $password): array {
        try {
            $stmt = $this->db->prepare("SELECT id, email, password FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if (!$user) {
                throw new \Exception('No account found with this email.');
            }

            // Use password_verify for hashed passwords
            if (!password_verify($password, $user['password'])) {
                throw new \Exception('Invalid password. Please try again.');
            }

            $_SESSION['user'] = [
                'id' => $user['id'],
                'email' => $user['email']
            ];

            return $_SESSION['user'];
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            throw new \Exception('An error occurred during login. Please try again.');
        }
    }

    public function signup(string $email, string $password): array {
        try {
            // Check if user already exists
            $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                throw new \Exception('This email is already registered.');
            }

            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            // Insert new user
            $stmt = $this->db->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
            $stmt->execute([$email, $hashedPassword]);

            $userId = $this->db->lastInsertId();

            $_SESSION['user'] = [
                'id' => $userId,
                'email' => $email
            ];

            return $_SESSION['user'];
        } catch (PDOException $e) {
            error_log("Signup error: " . $e->getMessage());
            throw new \Exception('An error occurred during signup. Please try again.');
        }
    }

    public function logout(): void {
        unset($_SESSION['user']);
        session_destroy();
    }

    public function isAuthenticated(): bool {
        return isset($_SESSION['user']);
    }

    public function getUser(): ?array {
        return $_SESSION['user'] ?? null;
    }

    public function getUserById(int $userId): ?array {
        try {
            $stmt = $this->db->prepare("SELECT id, email, created_at FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            error_log("Get user error: " . $e->getMessage());
            return null;
        }
    }
}