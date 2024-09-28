<?php

declare(strict_types=1);

use Firebase\JWT\ExpiredException;
use PHPMailer\PHPMailer\PHPMailer;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function createMagicToken(string $email): string {
    $key = getenv('MAGIC_SECRET');

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
    $mail->Body = "Click here to login: " . $link;

    $mail->send();
}

function verifyMagicLink(string $token): string|object {
    $key = getenv('MAGIC_SECRET');

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
    $key = getenv('SESSION_SECRET');
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
    setcookie('access_token', $access, time() + (60 * 60), '/', '', true, true);
    setcookie('refresh_token', $refresh, time() + (24 * 60 * 60), '/', '', true, true);
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

    $key = getenv('SESSION_SECRET');
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
    }
    catch(Exception $e) {
        error_log('Unable to validate tokens, redirect to login: ' . $e);
    }
}