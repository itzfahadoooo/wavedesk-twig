<?php
namespace Src\Controllers;

use Src\Services\TicketService;

class DashboardController {
    private $twig;
    private $ticketService;

    public function __construct($twig) {
        $this->twig = $twig;
        $this->ticketService = new TicketService();
        session_start();
    }

    public function index() {
        if (!isset($_SESSION['user'])) {
            header('Location: /auth/login');
            exit;
        }

        $stats = $this->ticketService->getStats();
        echo $this->twig->render('pages/dashboard.twig', [
            'stats' => $stats,
            'user' => $_SESSION['user']
        ]);
    }
}
