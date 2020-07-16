<?php


namespace App\Classes\Elements\Skills;


class AccelerateSkill
{
    public $name;
    public $description;
    public $type;
    public $chance;
    public $modifier;

    public function __construct()
    {
        $this->name = 'Accelerate';
        $this->description = 'Increases speed on init roll';
        $this->type = 'INIT:ROLL';
        $this->chance = 5;
        $this->modifier = 1.1;
    }
}