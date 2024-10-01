<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../database/bootstrap.php';
require __DIR__ . '/../plugins/auth.php';
require __DIR__ . '/../plugins/template.php';

global $template;

$user_email = $_POST['email'];

if(empty($user_email)) {
    echo $template->render('auth-error', [
        'title' => 'Error',
        'desc' => 'Please prove an email before logging in'
    ]);
}
else {
    try {
        user_create([
            'email' => $user_email,
            'username' => strtok($user_email, '@'),
        ]);
    
        sendMagicLink($user_email);
    }
    catch(Exception $e) {
        error_log('Unable to complete login: ' . $e);
    }
}