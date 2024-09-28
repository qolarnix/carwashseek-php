<?php declare(strict_types=1);

global $template;

$router->get('/', function() use($template) {
    echo $template->render('lander');
});

$router->get('/login', function() use($template) {
    echo $template->render('login');
});

$router->before('GET|POST', '/account/.*', function() {
    $session = verifyClientTokens();
    print_r($session);
});