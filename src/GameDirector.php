<?php


namespace App;

use App\Classes\Logger\Logger;
use App\Classes\Logger\Outputer;

use App\Classes\Mechanics\Battle;

use App\Classes\EntityDirector;
use App\Classes\MonsterBuilder;
use App\Classes\PlayerBuilder;


class GameDirector
{
    public $outputer;
    public $logger;

    public function __construct()
    {
        $this->outputer = new Outputer();
        $this->logger = new Logger();
    }

    public function start()
    {
        $player_builder = new PlayerBuilder();
        $player = (new EntityDirector())->build($player_builder);

        $monster_builder = new MonsterBuilder();
        $monster = (new EntityDirector())->build($monster_builder);

        $new_battle = new Battle($player, $monster);

        $new_battle->attach($this->logger);
        $new_battle->attach($this->outputer);


        $this->outputer->echoEntryPrompt();
        $new_battle->startBattle();

        $new_battle->detach($this->logger);
        $new_battle->detach($this->outputer);
    }
}