<?php


namespace App\Classes;


use App\Classes\Elements\Player;
use App\Classes\Elements\Skills\MagicShieldSkill;
use App\Classes\Elements\Skills\RapidStrikeSkill;
use App\Classes\Elements\Stats\DefenceStat;
use App\Classes\Elements\Stats\HealthStat;
use App\Classes\Elements\Stats\LuckStat;
use App\Classes\Elements\Stats\SpeedStat;
use App\Classes\Elements\Stats\StrengthStat;

class PlayerBuilder implements EntityBuilder
{
    private $player;

    public function createEntity()
    {
        $this->player = new Player();
    }

    public function getEntity()
    {
        return $this->player;
    }

    public function addStats()
    {
        $stat_array = [
          'health'   => new HealthStat(70, 100),
          'strength' => new StrengthStat(70, 80),
          'defence'  => new DefenceStat(45, 55),
          'speed'    => new SpeedStat(40, 50),
          'luck'     => new LuckStat(10, 30)
        ];
        $this->player->setElement('stats', $stat_array);

    }

    public function addSkills()
    {
        $skill_array = [
            'MagicShieldSkill' => new MagicShieldSkill(),
            'RapidStrikeSkill' => new RapidStrikeSkill()
        ];
        $this->player->setElement('skills', $skill_array);
    }
}