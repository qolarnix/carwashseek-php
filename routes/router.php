<?php declare(strict_types=1);

$router = new \Bramus\Router\Router();

global $template;

/**
 * Web Routes
 */
require_once __DIR__ . '/web.php';

/**
 * API Routes
 */
require_once __DIR__ . '/api.php';

$router->set404(function() use($template) {
    header('HTTP/1.1 404 Not Found');
    echo $template->render('404');
});

$router->run();