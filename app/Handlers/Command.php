<?php

namespace App\Handlers;

use App\Commands\{LinesCommand, TimeCommand};

class Command
{
    protected static array $map = [
        TimeCommand::class,
        LinesCommand::class,
    ];

    public static function handle(string $input): string
    {
        $parts = explode(' ', trim($input), 2);
        $name = strtolower($parts[0]);
        $args = $parts[1] ?? '';

        if (isset(self::$map[$name])) {
            $class = self::$map[$name];
            return $class::run($args);
        }

        return "Unknown command: {$name}";
    }
}
