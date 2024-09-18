<?php declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../bootstrap.php';

use Illuminate\Database\Capsule\Manager;

Manager::schema()->drop('users');
Manager::schema()->create('users', function($t) {
    $t->increments('id');
    $t->string('email')->unique();
    $t->string('username')->unique();
    $t->string('fullname')->nullable();
    $t->timestamps();
});

$faker = Faker\Factory::create();

$users = [];
for($i = 0; $i < 10; $i++) {
    $user = [
        'email' => $faker->email(),
        'username' => $faker->userName(),
        'fullname' => $faker->firstName().' '.$faker->lastName(),
    ];
    $users[] = $user;
}

Manager::table('users')->insert($users);