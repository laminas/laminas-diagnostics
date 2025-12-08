<?php

use Doctrine\DBAL\Migrations\Configuration\Configuration;

if (
    class_exists(Configuration::class)
    && ! class_exists(Doctrine\Migrations\Configuration\Configuration::class)
) {
    class_alias(
        Configuration::class,
        Doctrine\Migrations\Configuration\Configuration::class
    );
}
