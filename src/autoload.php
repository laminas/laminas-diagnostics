<?php

if (
    class_exists(Doctrine\DBAL\Migrations\Configuration\Configuration::class)
    && ! class_exists(Doctrine\Migrations\Configuration\Configuration::class)
) {
    class_alias(
        Doctrine\DBAL\Migrations\Configuration\Configuration::class,
        Doctrine\Migrations\Configuration\Configuration::class
    );
}
