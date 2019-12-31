<?php

/**
 * @see       https://github.com/laminas/laminas-diagnostics for the canonical source repository
 * @copyright https://github.com/laminas/laminas-diagnostics/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-diagnostics/blob/master/LICENSE.md New BSD License
 */

if (class_exists(Doctrine\DBAL\Migrations\Configuration\Configuration::class)
    && ! class_exists(Doctrine\Migrations\Configuration\Configuration::class)
) {
    class_alias(
        Doctrine\DBAL\Migrations\Configuration\Configuration::class,
        Doctrine\Migrations\Configuration\Configuration::class
    );
}
