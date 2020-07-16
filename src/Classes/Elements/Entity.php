<?php


namespace App\Classes\Elements;


abstract class Entity
{
    public $data = [];

    public function setElement($key, $value)
    {
        $this->data[$key] = $value;
    }

    public function getElements($key) {
        return json_decode(json_encode($this->data[$key]), true);
    }

}