<?php

$factory->define(App\Action::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->name
    ];
});
