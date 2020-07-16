<?php


namespace App\Classes\Elements\Skills;


class MagicShieldSkill
{
    public $name;
    public $description;
    public $type;
    public $chance;
    public $modifier;

    public function __construct()
    {
        $this->name = 'Magic Shield';
        $this->description = 'prevents half damage';
        $this->type = 'DAMAGE:APPLY';
        $this->chance = 20;
        $this->modifier = 0.5;
    }
}