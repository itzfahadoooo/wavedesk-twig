<?php
namespace Src\Helpers;

use Twig\Loader\FilesystemLoader;
use Twig\Environment;

function loadTwig()
{
    $loader = new FilesystemLoader(__DIR__ . '/../../templates');
    $twig = new Environment($loader, [
        'cache' => false,
        'debug' => true,
    ]);
    $twig->addGlobal('session', $_SESSION ?? []);

    return $twig;
}
