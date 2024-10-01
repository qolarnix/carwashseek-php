<?php declare(strict_types=1);

global $template;

$user = getSession();

$router->get('/', function() use($template) {
    echo $template->render('lander');
});

$router->get('/login', function() use($template) {
    echo $template->render('login');
});

$router->match('GET|POST', '/magic', function() {
    if(isset($_GET['token'])) {
        $token = $_GET['token'];

        $verified_user = verifyMagicLink($token);
        $client_tokens = createLoginTokens($verified_user->email);

        setLoginTokens(
            $client_tokens['access'], 
            $client_tokens['refresh']
        );

        print_r($verified_user);
    }
    else {
        revokeClientTokens();
        header('Location: ' . '/login');
    }
});

$router->get('/logout', function() {
    revokeClientTokens();
});

$router->before('GET|POST', '/account/.*', function() {
    $session = verifyClientTokens();
    if($session === false) {
        header('Location: ' . '/login');
    }
});

$router->get('/account/test', function() use($user) {
    echo 'you are authenticated! <br>';

    echo '<pre>';
    print_r($user);
    echo '</pre>';
});