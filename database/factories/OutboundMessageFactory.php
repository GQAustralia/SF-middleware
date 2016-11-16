<?php

$factory->define(App\OutboundMessage::class, function (Faker\Generator $faker) {
    return [
        'message_id' => $faker->word,
        'message_body' => $faker->sentence,
        'message_attributes' => $faker->sentence,
        'status' => 'sent'
    ];
});
