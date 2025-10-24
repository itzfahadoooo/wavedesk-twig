<?php

namespace App\Services;

use App\Config\Database;
use PDO;
use PDOException;

class TicketService {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Get all tickets for a specific user
     */
    public function getTicketsByUserId(int $userId): array {
        try {
            $stmt = $this->db->prepare("
                SELECT id, user_id, title, description, status, priority, created_at, updated_at 
                FROM tickets 
                WHERE user_id = ? 
                ORDER BY created_at DESC
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Get tickets error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get a single ticket by ID
     */
    public function getTicketById(int $ticketId, int $userId): ?array {
        try {
            $stmt = $this->db->prepare("
                SELECT id, user_id, title, description, status, priority, created_at, updated_at 
                FROM tickets 
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([$ticketId, $userId]);
            $ticket = $stmt->fetch();
            return $ticket ?: null;
        } catch (PDOException $e) {
            error_log("Get ticket error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Create a new ticket
     */
    public function createTicket(int $userId, string $title, string $description, string $status = 'open', string $priority = 'medium'): ?array {
        try {
            // Validate status
            $validStatuses = ['open', 'in_progress', 'closed'];
            if (!in_array($status, $validStatuses)) {
                throw new \Exception('Invalid status. Must be: open, in_progress, or closed');
            }

            // Validate priority
            $validPriorities = ['low', 'medium', 'high'];
            if (!in_array($priority, $validPriorities)) {
                throw new \Exception('Invalid priority. Must be: low, medium, or high');
            }

            $stmt = $this->db->prepare("
                INSERT INTO tickets (user_id, title, description, status, priority) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$userId, $title, $description, $status, $priority]);

            $ticketId = $this->db->lastInsertId();

            return $this->getTicketById($ticketId, $userId);
        } catch (PDOException $e) {
            error_log("Create ticket error: " . $e->getMessage());
            throw new \Exception('Failed to create ticket. Please try again.');
        }
    }

    /**
     * Update a ticket
     */
    public function updateTicket(int $ticketId, int $userId, array $updates): ?array {
        try {
            // Build dynamic update query
            $allowedFields = ['title', 'description', 'status', 'priority'];
            $setParts = [];
            $values = [];

            foreach ($updates as $field => $value) {
                if (in_array($field, $allowedFields)) {
                    // Validate status
                    if ($field === 'status' && !in_array($value, ['open', 'in_progress', 'closed'])) {
                        throw new \Exception('Invalid status');
                    }

                    // Validate priority
                    if ($field === 'priority' && !in_array($value, ['low', 'medium', 'high'])) {
                        throw new \Exception('Invalid priority');
                    }

                    $setParts[] = "$field = ?";
                    $values[] = $value;
                }
            }

            if (empty($setParts)) {
                throw new \Exception('No valid fields to update');
            }

            // Add updated_at
            $setParts[] = "updated_at = CURRENT_TIMESTAMP";

            // Add user_id and ticket_id to values
            $values[] = $ticketId;
            $values[] = $userId;

            $sql = "UPDATE tickets SET " . implode(', ', $setParts) . " WHERE id = ? AND user_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($values);

            return $this->getTicketById($ticketId, $userId);
        } catch (PDOException $e) {
            error_log("Update ticket error: " . $e->getMessage());
            throw new \Exception('Failed to update ticket. Please try again.');
        }
    }

    /**
     * Delete a ticket
     */
    public function deleteTicket(int $ticketId, int $userId): bool {
        try {
            $stmt = $this->db->prepare("DELETE FROM tickets WHERE id = ? AND user_id = ?");
            $stmt->execute([$ticketId, $userId]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Delete ticket error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get ticket statistics for a user
     */
    public function getStats(int $userId): array {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) as open,
                    SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
                    SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed
                FROM tickets 
                WHERE user_id = ?
            ");
            $stmt->execute([$userId]);
            $stats = $stmt->fetch();

            return [
                'total' => (int)$stats['total'],
                'open' => (int)$stats['open'],
                'in_progress' => (int)$stats['in_progress'],
                'closed' => (int)$stats['closed']
            ];
        } catch (PDOException $e) {
            error_log("Get stats error: " . $e->getMessage());
            return [
                'total' => 0,
                'open' => 0,
                'in_progress' => 0,
                'closed' => 0
            ];
        }
    }
}