<?php

$factory->define(App\InboundMessageLog::class, function (Faker\Generator $faker) {
    return [
        'inbound_sent_message_id' => $faker->numberBetween(1, 10000),
        'response_code' => $faker->numberBetween(200, 500),
        'response_body' => $faker->sentence
    ];
});
