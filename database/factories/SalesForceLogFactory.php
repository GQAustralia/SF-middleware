<?php

$factory->define(App\SalesForceLog::class, function (Faker\Generator $faker) {
    return [
        'object_name' => $faker->word,
        'message' => $faker->sentence,
        'response_body' => $faker->sentence
    ];
});
