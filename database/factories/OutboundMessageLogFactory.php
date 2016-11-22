<?php

$factory->define(App\OutboundMessageLog::class, function (Faker\Generator $faker) {
    return [
        'outbound_message_id' => $faker->numberBetween(1, 10000),
        'operation' => $faker->word,
        'request_object' => $faker->sentence,
        'object_name' => $faker->sentence
    ];
});
