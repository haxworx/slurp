<?php

// src/DataFixtures/GlobalSettingsFixtures.php

namespace App\DataFixtures;

use App\Entity\GlobalSettings;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class GlobalSettingsFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $settings = new GlobalSettings();
        $settings->setMaxRobots(5);
        $settings->setTimestamp(new \DateTime('NOW'));
        $manager->persist($settings);
        $manager->flush();
    }
}
