<?php


namespace App\Classes\Logger;


class Logger implements \SplObserver
{

    const MESSAGES = [
        'BATTLE:START' => "The battle is starting!",
        'BATTLE:END:DRAW'   => "The battle ended in draw!",
        'TURN:PRE' => '',
        'TURN:CURRENT' => "CURRENT TURN: ",
        'INIT:START'   => '',
        'INIT:SPEED_PLAYER' => "You are faster than monster, you attack as first.",
        'INIT:SPEED_MONSTER' => "You are slower than monster, it will attack as first.",
        'INIT:SPEED_EQUAL' => "You are as fast as monster. Do you feel lucky ?",
        'INIT:LUCK_PLAYER' => "It seems You are, you attack as first.",
        'INIT:LUCK_MONSTER' => "It seems You are not, monster is attacking first.",
        'INIT:LUCK_EQUAL' => 'Luck is really blind today. Attack order will truly be random.',
        'ATTACK:HIT' => 'HIT ON ',
        'ATTACK:MISS' => 'MISSED ',
        'ATTACK:DMG' => "Inflicted ⚔⚔⚔ ",
        'ATTACK:MORTAL' => 'The attack was mortal. ',
        'ATTACK:MORTAL:P' => 'You find yourself riding in green fields with the sun on your face.',
        'ATTACK:MORTAL:M' => 'The monster has been slayed.',
        'BATTLE:ATTACKER' => 'Attacker: ',
        'DEFENDER:CURRENT' => 'Defender: ',
        'DEFENDER:HP' => ' current ❤❤❤ ',
        'DEFENDER:HP_LEFT' => ' ❤❤❤ - ⚔⚔⚔ ',
        'SKILL:PROC' => '',
    ];

    private static $foreground = array(
        'black' => '0;30',
        'dark_gray' => '1;30',
        'red' => '0;31',
        'bold_red' => '1;31',
        'green' => '0;32',
        'bold_green' => '1;32',
        'brown' => '0;33',
        'yellow' => '1;33',
        'blue' => '0;34',
        'bold_blue' => '1;34',
        'purple' => '0;35',
        'bold_purple' => '1;35',
        'cyan' => '0;36',
        'bold_cyan' => '1;36',
        'white' => '1;37',
        'bold_gray' => '0;37',
        'bold' => '0;1',
        'blink' => '0;5'
    );

    private static $background = array(
        'black' => '40',
        'red' => '41',
        'magenta' => '45',
        'yellow' => '43',
        'green' => '42',
        'blue' => '44',
        'cyan' => '46',
        'light_gray' => '47',
        'light_blue' => '104',
    );


    public function update(\SplSubject $battle, $event = '', $data=null)
    {
        echo $this->prepareEventMessage($event, $data);

    }


    public function prepareEventMessage($event, $data)
    {
        $e_msg = self::MESSAGES[$event];
        switch($event) {
            case 'BATTLE:START':
                return self::bg_color('light_blue', $e_msg);
            case 'ATTACK:DMG':
                return self::fg_color('red', $e_msg . $data);
            case 'TURN:CURRENT':
                return self::bg_color('green', $e_msg . $data);
            case 'INIT:SPEED_PLAYER':
            case 'INIT:SPEED_MONSTER':
                return self::fg_color('yellow', $e_msg);
            case 'INIT:SPEED_EQUAL':
            case 'INIT:LUCK_PLAYER':
            case 'INIT:LUCK_MONSTER':
            case 'INIT:LUCK_EQUAL':
            case 'DEFENDER:HP':
                return self::fg_color('bold_green', $data[0] . $e_msg . $data[1]);
            case 'DEFENDER:HP_LEFT':
                return self::fg_color('yellow', $data[0] . $e_msg . $data[1]);
            case 'DEFENDER:CURRENT':
            case 'BATTLE:ATTACKER':
                if ($data == "player") {
                    return self::fg_color('bold_purple', $e_msg . $data);
                } else {
                    return self::fg_color('bold_red', $e_msg . $data);
                }
                break;
            case 'ATTACK:MORTAL':
                if ($data == "player") {
                    return self::fg_color('blink', self::addDoubleNewLine($e_msg . self::MESSAGES['ATTACK:MORTAL:P']));
                } else {
                    return self::fg_color('blink', self::addDoubleNewLine($e_msg . self::MESSAGES['ATTACK:MORTAL:M']));
                }
                break;
            case 'BATTLE:END:DRAW':
                return self::fg_color('blink', self::addDoubleNewLine($e_msg));
            default:
                if ($data) {
                    return self::fg_color('blue', $e_msg . $data);
                } else {
                    return self::fg_color('blue', $e_msg);
                }

        }
    }

    public static function fg_color($color, $string, $eol = true)
    {
        if (!isset(self::$foreground[$color]))
        {
            throw new \Exception('Foreground color is not defined');
        }

        return "\033[" . self::$foreground[$color] . "m" . $string . "\033[0m "
            . ($eol === true ? PHP_EOL : '');
    }

    public static function bg_color($color, $string, $eol = true)
    {
        if (!isset(self::$background[$color]))
        {
            throw new \Exception('Background color is not defined');
        }

        return "\033[" . self::$background[$color] . 'm' . $string . "\033[0m "
            . ($eol === true ? PHP_EOL : '');
    }

    public static function addDoubleNewLine($string)
    {
        return "\n".$string."\n";
    }
}