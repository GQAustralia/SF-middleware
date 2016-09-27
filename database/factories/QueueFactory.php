<?php

$factory->define(App\Queue::class, function (Faker\Generator $faker) {
    return [
        'queue_name' => $faker->title,
        'aws_queue_name' => $faker->title,
        'arn' => $faker->shuffleString('abcdefghijklmnopqrstuvwxyz1234567890.'),
    ];
});
