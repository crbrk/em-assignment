<?php


namespace App\Classes;


interface EntityBuilder
{
    public function createEntity();

    public function getEntity();

    public function addStats();

    public function addSkills();

}