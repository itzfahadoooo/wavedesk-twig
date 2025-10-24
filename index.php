<?php
require_once __DIR__ . '/vendor/autoload.php';

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use App\Services\AuthService;
use App\Services\TicketService;
use App\Controllers\AuthController;
use App\Controllers\TicketController;

// Initialize services
$authService = new AuthService();
$ticketService = new TicketService();

// Initialize controllers
$authController = new AuthController($authService);
$ticketController = new TicketController($ticketService, $authService);

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        // Auth actions
        case 'login':
            $authController->login();
            break;
        case 'signup':
            $authController->signup();
            break;
        case 'logout':
            $authController->logout();
            break;
        
        // Ticket actions
        case 'create_ticket':
            $ticketController->create();
            break;
        case 'update_ticket':
            $ticketController->update();
            break;
        case 'delete_ticket':
            $ticketController->delete();
            break;
    }
}

// Load templates directory
$loader = new FilesystemLoader(__DIR__ . '/templates');
$twig = new Environment($loader, [
    'cache' => false,
    'debug' => true
]);

// Get the requested page from URL
$page = $_GET['page'] ?? 'landing';

// Define available pages
$pages = [
    'landing' => 'pages/landing.twig',
    'login' => 'pages/login.twig',
    'signup' => 'pages/signup.twig',
    'dashboard' => 'pages/dashboard.twig',
    'tickets' => 'pages/tickets.twig',
];

// Protected pages that require authentication
$protectedPages = ['dashboard', 'tickets'];

// Check authentication for protected pages
if (in_array($page, $protectedPages) && !$authService->isAuthenticated()) {
    $_SESSION['flash'] = [
        'type' => 'error',
        'message' => 'Please log in to access this page.'
    ];
    header('Location: /?page=login');
    exit;
}

// Redirect authenticated users away from login/signup pages
if (in_array($page, ['login', 'signup']) && $authService->isAuthenticated()) {
    header('Location: /?page=dashboard');
    exit;
}

// Get flash message and clear it
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// Get form data (for preserving email on error)
$formEmail = $_SESSION['form_email'] ?? '';
unset($_SESSION['form_email']);

// Get tickets and stats for authenticated users
$tickets = [];
$stats = ['total' => 0, 'open' => 0, 'in_progress' => 0, 'closed' => 0];

if ($authService->isAuthenticated()) {
    $user = $authService->getUser();
    $tickets = $ticketService->getTicketsByUserId($user['id']);
    $stats = $ticketService->getStats($user['id']);
}

// Prepare global template variables
$globalVars = [
    'isAuthenticated' => $authService->isAuthenticated(),
    'user' => $authService->getUser(),
    'flash' => $flash,
    'formEmail' => $formEmail,
    'tickets' => $tickets,
    'stats' => $stats
];

// Check if the requested page exists
if (array_key_exists($page, $pages)) {
    echo $twig->render($pages[$page], $globalVars);
} else {
    http_response_code(404);
    echo $twig->render('pages/landing.twig', $globalVars);
}