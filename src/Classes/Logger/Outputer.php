<?php


namespace App\Classes\Logger;


class Outputer implements \SplObserver
{
    public $entry_prompt = "Welcome to Emagia, a land where magic does happen.";
    public $question = "Would You like to start battle ? (y/n)";
    public $continue = "Continue ? (y/n)";
    public $bye = 'Indecisiveness. Bye.';

    private $logger;

    public function __construct()
    {
        $this->logger = new Logger();
    }

    public function update(\SplSubject $battle, $event = '', $data=null)
    {
        if ($event == 'TURN:PRE') {
            echo Logger::fg_color('bold', Logger::addDoubleNewLine($this->continue));
            $this->resolveYesNoInput();
        }
    }

    public function getInput() {
        return trim(fgets(STDIN));
    }

    public function resolveYesNoInput() {
        return $this->getInput() == strtolower('y') ?
            true : exit(Logger::bg_color('black', $this->bye));
    }

    public function echoEntryPrompt() {
        echo Logger::fg_color('cyan', Logger::addDoubleNewLine($this->entry_prompt));
        echo Logger::fg_color('cyan', Logger::addDoubleNewLine($this->question));
        $this->resolveYesNoInput();
    }
}