<?php

namespace App\Controllers;

use App\Services\TicketService;
use App\Services\AuthService;

class TicketController {
    private TicketService $ticketService;
    private AuthService $authService;

    public function __construct(TicketService $ticketService, AuthService $authService) {
        $this->ticketService = $ticketService;
        $this->authService = $authService;
    }

    /**
     * Create a new ticket
     */
    public function create(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /?page=tickets');
            exit;
        }

        if (!$this->authService->isAuthenticated()) {
            $_SESSION['flash'] = [
                'type' => 'error',
                'message' => 'Please log in to create tickets.'
            ];
            header('Location: /?page=login');
            exit;
        }

        try {
            $user = $this->authService->getUser();
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $status = $_POST['status'] ?? 'open';
            $priority = $_POST['priority'] ?? 'medium';

            if (empty($title)) {
                throw new \Exception('Title is required.');
            }

            if (empty($description)) {
                throw new \Exception('Description is required.');
            }

            $ticket = $this->ticketService->createTicket(
                $user['id'],
                $title,
                $description,
                $status,
                $priority
            );

            $_SESSION['flash'] = [
                'type' => 'success',
                'message' => 'Ticket created successfully!'
            ];

            header('Location: /?page=tickets');
            exit;
        } catch (\Exception $e) {
            $_SESSION['flash'] = [
                'type' => 'error',
                'message' => $e->getMessage()
            ];
            header('Location: /?page=tickets');
            exit;
        }
    }

    /**
     * Update a ticket
     */
    public function update(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /?page=tickets');
            exit;
        }

        if (!$this->authService->isAuthenticated()) {
            $_SESSION['flash'] = [
                'type' => 'error',
                'message' => 'Please log in to update tickets.'
            ];
            header('Location: /?page=login');
            exit;
        }

        try {
            $user = $this->authService->getUser();
            $ticketId = (int)($_POST['ticket_id'] ?? 0);

            if ($ticketId <= 0) {
                throw new \Exception('Invalid ticket ID.');
            }

            $updates = [];
            
            if (isset($_POST['title']) && !empty(trim($_POST['title']))) {
                $updates['title'] = trim($_POST['title']);
            }

            if (isset($_POST['description']) && !empty(trim($_POST['description']))) {
                $updates['description'] = trim($_POST['description']);
            }

            if (isset($_POST['status'])) {
                $updates['status'] = $_POST['status'];
            }

            if (isset($_POST['priority'])) {
                $updates['priority'] = $_POST['priority'];
            }

            if (empty($updates)) {
                throw new \Exception('No fields to update.');
            }

            $ticket = $this->ticketService->updateTicket($ticketId, $user['id'], $updates);

            if (!$ticket) {
                throw new \Exception('Ticket not found or you do not have permission to update it.');
            }

            $_SESSION['flash'] = [
                'type' => 'success',
                'message' => 'Ticket updated successfully!'
            ];

            header('Location: /?page=tickets');
            exit;
        } catch (\Exception $e) {
            $_SESSION['flash'] = [
                'type' => 'error',
                'message' => $e->getMessage()
            ];
            header('Location: /?page=tickets');
            exit;
        }
    }

    /**
     * Delete a ticket
     */
    public function delete(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /?page=tickets');
            exit;
        }

        if (!$this->authService->isAuthenticated()) {
            $_SESSION['flash'] = [
                'type' => 'error',
                'message' => 'Please log in to delete tickets.'
            ];
            header('Location: /?page=login');
            exit;
        }

        try {
            $user = $this->authService->getUser();
            $ticketId = (int)($_POST['ticket_id'] ?? 0);

            if ($ticketId <= 0) {
                throw new \Exception('Invalid ticket ID.');
            }

            $deleted = $this->ticketService->deleteTicket($ticketId, $user['id']);

            if (!$deleted) {
                throw new \Exception('Ticket not found or you do not have permission to delete it.');
            }

            $_SESSION['flash'] = [
                'type' => 'success',
                'message' => 'Ticket deleted successfully!'
            ];

            header('Location: /?page=tickets');
            exit;
        } catch (\Exception $e) {
            $_SESSION['flash'] = [
                'type' => 'error',
                'message' => $e->getMessage()
            ];
            header('Location: /?page=tickets');
            exit;
        }
    }
}