<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\ExpiredException;
use PHPMailer\PHPMailer\PHPMailer;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function createMagicToken(string $uuid): string {
    $key = 'e4245ab365b60a312b0f73e821f97532ec1afa330926a0d001a065779a6129b0';

    $issued = time();
    $expires = $issued + (5 * 60);

    $payload = [
        'uuid' => $uuid,
        'iat' => $issued,
        'exp' => $expires
    ];

    return JWT::encode($payload, $key, 'HS256');
}

function sendMagicLink(string $email, string $uuid) {
    $token = createMagicToken($uuid);
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

function verifyMagicLink(string $token): object {
    $key = 'e4245ab365b60a312b0f73e821f97532ec1afa330926a0d001a065779a6129b0';

    try {
        $decode = JWT::decode($token, new Key($key, 'HS256'));
        return $decode->uuid;
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

function testMagicLink() {
    $data = random_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
    $test_uuid = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));

    $test_token = createMagicToken($test_uuid);
    sendMagicLink('test@email.com', $test_uuid);

    $result = verifyMagicLink($test_token);
    print_r($result);
}
testMagicLink();