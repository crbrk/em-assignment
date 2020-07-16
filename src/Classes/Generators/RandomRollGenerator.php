<?php


namespace App\Classes\Generators;


class RandomRollGenerator
{
    public static function rollD100() {
        return rand(0, 100);
    }
}