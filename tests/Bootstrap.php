<?php

/**
 * @see       https://github.com/laminas/laminas-diagnostics for the canonical source repository
 * @copyright https://github.com/laminas/laminas-diagnostics/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-diagnostics/blob/master/LICENSE.md New BSD License
 */

/**
 * Set error reporting to the level to which Laminas code must comply.
 */
error_reporting( E_ALL | E_STRICT );

if (class_exists('PHPUnit_Runner_Version', true)) {
    $phpUnitVersion = PHPUnit_Runner_Version::id();
    if ('@package_version@' !== $phpUnitVersion && version_compare($phpUnitVersion, '3.7.0', '<')) {
        echo 'This version of PHPUnit (' .
            PHPUnit_Runner_Version::id() .
            ') is not supported for laminas-diagnostics unit tests - use v 3.7.0 or higher.'
            . PHP_EOL
        ;
        exit(1);
    }
    unset($phpUnitVersion);
}

/**
 * Setup autoloading
 */
// Try to use Composer autoloader
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    include_once __DIR__ . '/../vendor/autoload.php';
}
// If composer autoloader is missing, try to use Laminas loader from laminas-loader package.
elseif (false && file_exists( __DIR__ . '/../vendor/laminas/laminas-loader/Laminas/Loader/StandardAutoloader.php')) {
    require_once __DIR__ . '/../vendor/laminas/laminas-loader/Laminas/Loader/StandardAutoloader.php';
    $loader = new Laminas\Loader\StandardAutoloader(array(
        Laminas\Loader\StandardAutoloader::LOAD_NS => array(
            'laminas-diagnostics'     => __DIR__ . '/../src/laminas-diagnostics',
            'LaminasTest\Diagnostics' => __DIR__ . '/LaminasTest\Diagnostics',
        ),
    ));
    $loader->register();
}

// ... or main laminas package.
elseif (file_exists( __DIR__ . '/../vendor/laminas/laminas/library/Laminas/Loader/StandardAutoloader.php')) {
    require_once __DIR__ . '/../vendor/laminas/laminas/library/Laminas/Loader/StandardAutoloader.php';
    $loader = new Laminas\Loader\StandardAutoloader(array(
        Laminas\Loader\StandardAutoloader::LOAD_NS => array(
            'laminas-diagnostics'     => __DIR__ . '/../src/laminas-diagnostics',
            'LaminasTest\Diagnostics' => __DIR__ . '/LaminasTest\Diagnostics',
        ),
    ));
    $loader->register();
}

// ... or use a simple SPL autoloader
else{

    // update include path
    set_include_path(implode(PATH_SEPARATOR, array(
        __DIR__.'/../src',
        __DIR__,
        get_include_path()
    )));

    /**
     * @link https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md#example-implementation
     */
    spl_autoload_register(function ($className) {
        $className = ltrim($className, '\\');
        $fileName  = '';
        $namespace = '';
        if ($lastNsPos = strrpos($className, '\\')) {
            $namespace = substr($className, 0, $lastNsPos);
            $className = substr($className, $lastNsPos + 1);
            $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
        }
        $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
        require $fileName;
    });

}

/**
 * Code coverage option
 */
if (defined('TESTS_GENERATE_REPORT') && TESTS_GENERATE_REPORT === true) {
    $codeCoverageFilter = new PHP_CodeCoverage_Filter();

    $lastArg = end($_SERVER['argv']);
    if (is_dir($laminasCoreTests . '/' . $lastArg)) {
        $codeCoverageFilter->addDirectoryToWhitelist($laminasCoreLibrary . '/' . $lastArg);
    } elseif (is_file($laminasCoreTests . '/' . $lastArg)) {
        $codeCoverageFilter->addDirectoryToWhitelist(dirname($laminasCoreLibrary . '/' . $lastArg));
    } else {
        $codeCoverageFilter->addDirectoryToWhitelist($laminasCoreLibrary);
    }

    /*
     * Omit from code coverage reports the contents of the tests directory
     */
    $codeCoverageFilter->addDirectoryToBlacklist($laminasCoreTests, '');
    $codeCoverageFilter->addDirectoryToBlacklist(PEAR_INSTALL_DIR, '');
    $codeCoverageFilter->addDirectoryToBlacklist(PHP_LIBDIR, '');

    unset($codeCoverageFilter);
}

/*
 * Unset global variables that are no longer needed.
 */
unset($phpUnitVersion);
