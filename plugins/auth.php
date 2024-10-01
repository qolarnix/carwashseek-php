<?php

declare(strict_types=1);

use Firebase\JWT\ExpiredException;
use PHPMailer\PHPMailer\PHPMailer;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $env = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $env->load();
}

function createMagicToken(string $email): string {
    $key = $_ENV['MAGIC_SECRET'];

    $issued = time();
    $expires = $issued + (5 * 60);

    $payload = [
        'email' => $email,
        'iat' => $issued,
        'exp' => $expires
    ];

    return JWT::encode($payload, $key, 'HS256');
}

function sendMagicLink(string $email) {
    $token = createMagicToken($email);
    $link = 'http://localhost:3000/magic?token=' . $token;

    $mail = new PHPMailer();

    // mailtrap config
    $mail->isSMTP();
    $mail->Host = 'sandbox.smtp.mailtrap.io';
    $mail->SMTPAuth = true;
    $mail->Port = 2525;
    $mail->Username = 'c16530b1e91b2a';
    $mail->Password = '0bc76fc41cd373';

    // setup test email
    $mail->addAddress($email);
    $mail->isHTML(true);
    $mail->Subject = "Your one-click login link";
    $mail->Body = <<<HTML
        <p>Click here to login: </p>
        <a href="$link" target="_block">$link</a>
    HTML;

    $mail->send();
}

function verifyMagicLink(string $token) {
    $key = $_ENV['MAGIC_SECRET'];

    try {
        $decode = JWT::decode($token, new Key($key, 'HS256'));
        return $decode;
    }
    catch(ExpiredException $e) {
        error_log("Magic link failed: " . $e);
        return false;
    }
    catch(Exception $e) {
        error_log("Magic link failed: " . $e);
        return false;
    }
}

function createLoginTokens(string $email): array {
    $key = $_ENV['SESSION_SECRET'];
    $issued = time();

    return [
        'access' => JWT::encode([
            'email' => $email,
            'iat' => $issued,
            'exp' => $issued + (60 * 60)
        ], $key, 'HS256'),
        'refresh' => JWT::encode([
            'email' => $email,
            'iat' => $issued,
            'exp' => $issued + (24 * 60 * 60)
        ], $key, 'HS256'),
    ];
}

function setLoginTokens(string $access, string $refresh) {
    $session = verifyClientTokens();

    if($session === false) {
        setcookie('access_token', $access, time() + (60 * 60), '/', '', true, true);
        setcookie('refresh_token', $refresh, time() + (24 * 60 * 60), '/', '', true, true);
    }
    else {
        error_log('User already logged in, refusing to set tokens again.');
    }
}

/**
 * Logout
 */
function revokeClientTokens() {
    if(isset($_COOKIE['access_token'])) {
        unset($_COOKE['access_token']);
        setcookie('access_token', '', -1, '/');
    }

    if(isset($_COOKIE['refresh_token'])) {
        unset($_COOKE['refresh_token']);
        setcookie('refresh_token', '', -1, '/');
    }
}

/**
 * Auth middleware
 */
function verifyClientTokens() {
    if(!isset($_COOKIE['access_token']) || !isset($_COOKIE['refresh_token'])) {
        error_log('User is not logged in, redirect to login.');
        return false;
    }

    $key = $_ENV['SESSION_SECRET'];
    $tokens = [
        'access' => $_COOKIE['access_token'],
        'refresh' => $_COOKIE['refresh_token']
    ];

    try {
        $decoded = JWT::decode($tokens['access'], new Key($key, 'HS256'));
        return $decoded->email;
    }
    catch(ExpiredException $e) {
        error_log('Tokens expired, redirect to login: ' . $e);
        return false;
    }
    catch(Exception $e) {
        error_log('Unable to validate tokens, redirect to login: ' . $e);
        return false;
    }
}

function getSession() {
    if(!isset($_COOKIE['access_token']) || !isset($_COOKIE['refresh_token'])) {
        error_log('Unable to get user session. Is the user logged in?');
        return false;
    }

    $key = $_ENV['SESSION_SECRET'];
    $token = $_COOKIE['access_token'];

    try {
        $decoded = JWT::decode($token, new Key($key, 'HS256'));
        $user = user_by_email($decoded->email);
        return $user;
    }
    catch(ExpiredException $e) {
        error_log('Tokens expired, is the user logged in?' . $e);
        return false;
    }
    catch(Exception $e) {
        error_log('Unable to validate tokens, is the user logged in?' . $e);
        return false;
    }
}

/**
 * Util
 */
function validateEmail(string $email) {
    $pattern = '/^(?:[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+)*|"(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21\x23-\x5b\x5d-\x7f]|\\\\[\x01-\x09\x0b\x0c\x0e-\x7f])*")@(?:(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?|\[(?:(?:(2(5[0-5]|[0-4][0-9])|1[0-9][0-9]|[1-9]?[0-9]))\.){3}(?:(2(5[0-5]|[0-4][0-9])|1[0-9][0-9]|[1-9]?[0-9])|[a-z0-9-]*[a-z0-9]:(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21-\x5a\x53-\x7f]|\\\\[\x01-\x09\x0b\x0c\x0e-\x7f])+)\])$/i';

    return preg_match($pattern, $email) === 1;
}