<?php


namespace App\Classes\Elements\Skills;


class RapidStrikeSkill
{
    public $name;
    public $description;
    public $type;
    public $chance;
    public $modifier;

    public function __construct()
    {
        $this->name = 'Rapid Strike';
        $this->description = 'allows attacker to strike twice.';
        $this->type = 'DAMAGE:CALC';
        $this->chance = 10;
        $this->modifier = 2;
    }
}