<?php

$factory->define(App\Message::class, function (Faker\Generator $faker) {
    return [
        'message_id' => $faker->shuffleString('abcdefghijklmnopqrstuvwxyz1234567890-'),
        'action_id' => $faker->numberBetween(1, 10000),
        'message_content' => $faker->sentence,
        'completed' => $faker->randomElement(['Y', 'N'])
    ];
});
