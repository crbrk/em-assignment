<?php


namespace App\Classes\Elements\Stats;


class SpeedStat
{
    public $value;

    public function __construct($low, $high)
    {
        $this->value = rand($low, $high);
    }
}