<?php

namespace App\Helpers;

use App\Exception\DiceStringParseException;

class DiceHelper implements \Serializable
{
    private $multiplier;
    private $maxSide;
    private $operator;
    private $modifier;

    public function __construct(string $diceString)
    {
        return $this->parse($diceString);
    }

    /**
     * @param string $diceString
     *
     * @throws DiceStringParseException Строка с представлением кубов не соответствует требованиям
     *
     * @example xxxdxxxoxx, where x = [0-9], o = [+-]
     * @example '2d6+1' => [ 0 => "2d6+1", "multiplier" => "2", 1 => "2", "max" => "6", 2 => "6", "operator" => "+", 3 => "+", "modifier" => "1", 4 => "1" ]
     */
    private function parse(string $diceString)
    {
        if (!preg_match('/^(?<multiplier>\d{0,3})d(?<max>\d{1,3})(?:(?<operator>[\+-])(?<modifier>\d{0,2}))?$/', $diceString, $matches)) {
            throw new DiceStringParseException('Dice string does not match requirements.');
        }

        $this->multiplier = $matches['multiplier'] ? (int) $matches['multiplier'] : 1;
        $this->maxSide = (int) $matches['max'];
        $this->operator = $matches['operator'] ?? null;
        $this->modifier = $matches['modifier'] ?? null;
    }

    public function roll()
    {
        $result = 0;
        for ($i = 0; $i < $this->multiplier; $i++) {
            $result += random_int(1, $this->maxSide);
        }

        switch ($this->operator) {
            case '+':
                $result += $this->modifier;
                break;
            case '-':
                $result -= $this->modifier;
                $result = $result < 0 ? 0 : $result;
                break;
        }

        return $result;
    }

    public function serialize()
    {
        $string = $this->multiplier . 'd' . $this->maxSide;

        if ($this->operator && $this->modifier) {
            $string .= $this->operator . $this->modifier;
        }

        return $string;
    }

    public function unserialize($serialized)
    {
        return $this->parse($serialized);
    }
}
