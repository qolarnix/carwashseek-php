<?php declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../bootstrap.php';

use Illuminate\Database\Capsule\Manager;

Manager::schema()->drop('cws');
Manager::schema()->create('cws', function($t) {
    $t->increments('id');
    $t->string('name');
    $t->enum('status', ['inactive', 'active', 'featured']);
    $t->enum('price', ['$', '$$', '$$$']);
    $t->string('location_state')->nullable();
    $t->timestamp('created_at')->useCurrent();
    $t->timestamp('updated_at')->nullable();
});

$faker = Faker\Factory::create();

$carwashes = [];
for($i = 0; $i < 10; $i++) {
    $carwash = [
        'name' => $faker->name(),
        'status' => $faker->randomElement(['inactive', 'active', 'featured']),
        'price' => $faker->randomElement(['$', '$$', '$$$']),
        'location_state' => $faker->word(),
    ];
    $carwashes[] = $carwash;
}

Manager::table('cws')->insert($carwashes);