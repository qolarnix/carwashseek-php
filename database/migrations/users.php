<?php declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../bootstrap.php';

use Illuminate\Database\Capsule\Manager;

Manager::schema()->drop('users');
Manager::schema()->create('users', function($t) {
    $t->increments('id');
    $t->string('username')->unique()->nullable();
    $t->string('email')->unique();
    $t->timestamp('created_at')->useCurrent();
    $t->timestamp('updated_at')->useCurrent();
});