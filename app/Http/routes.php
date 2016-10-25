<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/
//date_default_timezone_set('Australia/Sydney');

// Outbound Aws Management
 


$app->get('/', function () {
   return $this->app->version();
});



