<?php


namespace App\Classes;


use App\Classes\Elements\Monster;
use App\Classes\Elements\Skills\AccelerateSkill;
use App\Classes\Elements\Stats\DefenceStat;
use App\Classes\Elements\Stats\HealthStat;
use App\Classes\Elements\Stats\LuckStat;
use App\Classes\Elements\Stats\SpeedStat;
use App\Classes\Elements\Stats\StrengthStat;

class MonsterBuilder implements EntityBuilder
{
    private $monster;

    public function createEntity()
    {
        $this->monster = new Monster();
    }

    public function getEntity()
    {
        return $this->monster;
    }

    public function addStats()
    {
        $stat_array = [
            'health'   => new HealthStat(60, 90),
            'strength' => new StrengthStat(60, 90),
            'defence'  => new DefenceStat(40, 60),
            'speed'    => new SpeedStat(40, 60),
            'luck'     => new LuckStat(25, 40)
        ];

        $this->monster->setElement('stats', $stat_array);
    }

    public function addSkills()
    {
        $skill_array = [
            'AccelerateSkill' => new AccelerateSkill()
        ];
        $this->monster->setElement('skills', $skill_array);
    }
}