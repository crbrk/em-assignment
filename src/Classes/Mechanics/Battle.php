<?php


namespace App\Classes\Mechanics;


use App\Classes\Elements\Monster;
use App\Classes\Elements\Player;
use App\Classes\Generators\RandomRollGenerator;
use App\Classes\Logger\Logger;
use SplObserver;

class Battle implements \SplSubject
{
    private $battle_phases = [
        'INIT:ROLL',
        'HIT:CHECK',
        'DAMAGE:CALC',
        'DAMAGE:APPLY',
    ];

    public $player_stats;
    public $monster_stats;

    public $player_skills;
    public $monster_skills;

    private $logger;

    public $current_attacker;
    public $current_defender;

    private $initiative;

    private $current_battle_phase;

    private $battle_status;

    private $battle_turn_counter = 1;

    private $observers = [];

    public function __construct(Player $player, Monster $monster)
    {
        $this->observers = [];

        $this->player_stats = $this->prepareStatData($player->getElements('stats'));
        $this->monster_stats = $this->prepareStatData($monster->getElements('stats'));

        $this->player_skills = $this->prepareSkillData($player->getElements('skills'));
        $this->monster_skills = $this->prepareSkillData($monster->getElements('skills'));

        $this->logger = new Logger();

    }

    public function attach(SplObserver $observer)
    {
        $this->observers[] = $observer;
    }

    public function detach(SplObserver $observer)
    {
        unset($this->observers);
    }

    public function notify($event = '', $data = null)
    {
        foreach ($this->observers as $observer) {
            $observer->update($this, $event, $data);
        }
    }

    private function prepareStatData($stat_data)
    {
        $stat_values = [];
        foreach($stat_data as $key => $value) {
            $stat_values[$key] = $value['value'];

        }
        return $stat_values;
    }

    private function prepareSkillData($skill_data)
    {
        $skill_values = [];
        foreach($skill_data as $key => $skill) {
                $skill_values[$key] = $skill;
            }

        return $skill_values;
    }

    public function startBattle() {
        $this->notify('BATTLE:START');
        $this->cycleTurnEvents();
    }

    public function cycleTurnEvents()
    {
        $this->rollForInitiative();

        while ($this->battle_status !== 'ENDED' || $this->battle_turn_counter > 21) {
            $this->notify('TURN:PRE');
            $this->notify('TURN:CURRENT', $this->battle_turn_counter);

            if ($this->rollForHitConnection()) {
                $this->resolveDamage();
            } else {
                $this->reverseBattleRoles();
            }
            $this->battle_turn_counter++;

            if ($this->battle_turn_counter == 21) {
                $this->battle_status = 'ENDED';
                if ($this->getCurrentDefenderStats()['health'] > 0) {
                    $this->notify('BATTLE:END:DRAW');
                }
            }
        }
    }

    public function rollForInitiative()
    {
        $this->notify('INIT:START');
        $player = $this->player_stats;
        $monster = $this->monster_stats;

        $player_wins_speed = $player['speed'] > $monster['speed'];
        $monster_wins_speed = $player['speed'] < $monster['speed'];
        $speed_draw = $player['speed'] == $monster['speed'];

        if($player_wins_speed) {
            $this->initiative = 'PLAYER';
            $this->setPlayerAsAttacker();
            $this->notify('INIT:SPEED_PLAYER');
        } else if ($monster_wins_speed) {
            $this->initiative = 'MONSTER';
            $this->setPlayerAsDefender();
            $this->notify('INIT:SPEED_MONSTER');
        } else if ($speed_draw) {
            $this->notify('INIT:SPEED_EQUAL');
            $player_wins_luck = $player['luck'] > $monster['speed'];
            $monster_wins_luck = $player['luck'] < $monster['luck'];

            if ($player_wins_luck) {
                $this->initiative = 'PLAYER';
                $this->setPlayerAsAttacker();
                $this->notify('INIT:LUCK_PLAYER');

            } else if ($monster_wins_luck) {
                $this->initiative = 'MONSTER';
                $this->setPlayerAsDefender();
                $this->notify('INIT:LUCK_MONSTER');
            } else {
                $this->initiative = array_rand(array_flip(['PLAYER', 'MONSTER']));
                $this->initiative == 'PLAYER' ? $this->setPlayerAsAttacker() : $this->setPlayerAsDefender();
                $this->notify('INIT:LUCK_EQUAL');
            }
        }
    }

    public function rollForHitConnection() {
        $this->notify('BATTLE:ATTACKER', strtolower($this->current_attacker));
        $d100_roll = RandomRollGenerator::rollD100();
        $defender_luck = $this->getCurrentDefenderStats()['luck'];

        $this->setCurrentBattlePhase('HIT:CHECK');
        if ($defender_luck >= $d100_roll ) {
            $this->notify('ATTACK:MISS', strtolower($this->current_defender));
            return false;
        } else {
            $this->notify('ATTACK:HIT', strtolower($this->current_defender));
            return true;
        }
    }

    public function calcDamage($attack, $defence) {
        return $attack - $defence;
    }

    public function calcHealthAfterAttack($current, $dealt) {
        $final_mod = $this->checkDamageReductionSkills();
        $new_dealt = $dealt * $final_mod;

        return [
            'calc_result' => ceil($current - $new_dealt),
            'dealt_after_reduction' => ceil($new_dealt)
        ];
    }

    public function resolveDamage() {
        $attacker_strength = $this->getCurrentAttackerStats()['strength'];
        $defender_defence = $this->getCurrentDefenderStats()['defence'];

        $this->setCurrentBattlePhase('DAMAGE:CALC');
        $damage_dealt = $this->calcDamage($attacker_strength, $defender_defence);

        $current_defender_health = $this->getCurrentDefenderStats()['health'];
        $this->notify('DEFENDER:HP',
            [strtolower($this->current_defender), $current_defender_health]);

        $this->setCurrentBattlePhase('DAMAGE:APPLY');

        list(
            'calc_result' => $health_after_attack,
            'dealt_after_reduction' => $reduced_dealt
        ) = $this->calcHealthAfterAttack($current_defender_health, $damage_dealt);


        $this->getCurrentDefenderStats()['health'] = $health_after_attack;


        $this->notify('ATTACK:DMG', $reduced_dealt);

        $this->notify('DEFENDER:HP_LEFT',
            [strtolower($this->current_defender), $health_after_attack]);

        if ($this->checkForMortalDamage($health_after_attack)) {
                $this->battle_status = 'ENDED';
                $this->notify('ATTACK:MORTAL', strtolower($this->current_defender));
        } else {
            $this->reverseBattleRoles();
        }
    }

    public function checkDamageReductionSkills() {
        $final_modifier = 1;
        $proced_skill_names = [];
        foreach($this->getCurrentDefenderSkills() as $skill) {

            if ($skill['type'] === "DAMAGE:APPLY") {

                $random_roll = RandomRollGenerator::rollD100();
                $skill_proc_rate = $skill['chance'];
                $skill_proc = $skill_proc_rate > $random_roll;
                if ($skill_proc) {
                    $proced_skill_names[] = $skill['name'] . ' ' . $skill['description'];
                    $final_modifier = $final_modifier * $skill['modifier'];
                }
            }
        }
        foreach ($proced_skill_names as $proc) {
            $this->notify("SKILL:PROC", $proc);
        }
        return $final_modifier;

    }

    public function checkForMortalDamage($value)
    {
        return $value <= 0 ? true : false;
    }

    public function reverseBattleRoles()
    {
        if ($this->current_attacker == "PLAYER") {
            $this->setPlayerAsDefender();
        } else {
            $this->setPlayerAsAttacker();
        }
    }

    public function setPlayerAsAttacker()
    {
        $this->current_attacker = 'PLAYER';
        $this->current_defender = 'MONSTER';
    }

    public function setPlayerAsDefender()
    {
        $this->current_attacker = 'MONSTER';
        $this->current_defender = 'PLAYER';
    }

    public function &getCurrentAttackerStats()
    {
        switch($this->current_attacker) {
            case 'PLAYER':
                return $this->player_stats;
            case 'MONSTER':
                return $this->monster_stats;
        }
    }

    public function &getCurrentAttackerSkills()
    {
        switch($this->current_attacker) {
            case 'PLAYER':
                return $this->player_skills;
            case 'MONSTER':
                return $this->monster_skills;
        }
    }

    public function &getCurrentDefenderStats()
    {
        switch ($this->current_defender) {
            case 'PLAYER':
                return $this->player_stats;
            case 'MONSTER':
                return $this->monster_stats;
        }
    }

    public function &getCurrentDefenderSkills()
    {
        switch ($this->current_defender) {
            case 'PLAYER':
                return $this->player_skills;
            case 'MONSTER':
                return $this->monster_skills;
        }
    }

    public function setCurrentBattlePhase($new_battle_phase)
    {
        $this->current_battle_phase = $new_battle_phase;
    }
    public function getCurrentBattlePhase()
    {
        return $this->current_battle_phase;
    }
}