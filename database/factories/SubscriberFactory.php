<?php

$factory->define(App\Subscriber::class, function (Faker\Generator $faker) {
    return [
        'platform_name' => $faker->title,
        'url' => $faker->url,
        'filename' => $faker->text
    ];
});
