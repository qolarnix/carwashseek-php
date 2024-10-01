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
        if(validateEmail($user_email) === false) {
            throw new Exception('Invalid email');
            return false;
        }

        user_create([
            'email' => $user_email,
            'username' => strtok($user_email, '@'),
        ]);
    
        sendMagicLink($user_email);

        echo $template->render('auth-success', [
            'title' => 'Magic Link Sent',
            'desc' => 'Please check your email for the login link.'
        ]);
    }
    catch(Exception $e) {
        error_log('Unable to complete login: ' . $e);

        echo $template->render('auth-error', [
            'title' => 'Error',
            'desc' => 'There was an error processing your request. Please refresh the browser and try again.'
        ]);
    }
}