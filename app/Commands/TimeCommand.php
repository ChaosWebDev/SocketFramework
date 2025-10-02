<?php

namespace App\Commands;

class TimeCommand {
    public static function run() {
        return date('m/d/Y H:i', time());
    }
}