<?php


namespace App\Classes;


class EntityDirector
{
    public function build(EntityBuilder $builder)
    {
        $builder->createEntity();
        $builder->addStats();
        $builder->addSkills();
        return $builder->getEntity();
    }
}