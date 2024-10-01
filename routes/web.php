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
        try {
            $token = $_GET['token'];
            
            $verified_user = verifyMagicLink($token);
            if($verified_user === false) {
                revokeClientTokens();
                header('Location: ' . '/login');
            }

            $client_tokens = createLoginTokens($verified_user->email);
            setLoginTokens(
                $client_tokens['access'], 
                $client_tokens['refresh']
            );
            header('Location: ' . '/account');
        }
        catch(Exception $e) {
            error_log('Magic link failed: '.$e);
            revokeClientTokens();
            header('Location: ' . '/login');
        }
    }
    else {
        revokeClientTokens();
        header('Location: ' . '/login');
    }
});

$router->get('/logout', function() {
    revokeClientTokens();
});

$router->before('GET|POST', '/account(/.*)?', function() {
    $session = verifyClientTokens();
    if($session === false) {
        header('Location: ' . '/login');
    }
});

$router->get('/account', function() use($user) {
    echo 'you are authenticated! <br>';

    echo '<pre>';
    print_r($user);
    echo '</pre>';
});