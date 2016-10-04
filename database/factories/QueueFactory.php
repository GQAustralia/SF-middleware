<?php

$factory->define(App\Queue::class, function (Faker\Generator $faker) {
    return [
        'queue_name' => $faker->name,
        'aws_queue_name' => $faker->name,
        'arn' => $faker->shuffleString('abcdefghijklmnopqrstuvwxyz1234567890.'),
    ];
});
