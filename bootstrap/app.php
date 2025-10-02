<?php
use Chaos\Dotenv\Dotenv;
use Chaos\Core\Support\Config;

new Dotenv();

if (env('APP_ENV') == "development" || env("APP_DEBUG") == true) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

Config::loadFromPath(__DIR__ . '/../config');

date_default_timezone_set(config('app.timezone'));