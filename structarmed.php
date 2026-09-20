<?php

declare(strict_types=1);

use Boundwize\StructArmed\Architecture;
use Boundwize\StructArmed\Preset\Preset;

return Architecture::define()
    ->withPresets(Preset::PSR4(), Preset::CODEQUALITY())
    ->layer('Check', 'src/Check')
    ->layer('Result', 'src/Result')
    ->layer('ResultCollection', 'src/Result/Collection.php')
    ->layerPattern(
        'Runner',
        '/^Laminas\\\\Diagnostics\\\\Runner\\\\.*$/',
        '/^Laminas\\\\Diagnostics\\\\Runner\\\\Reporter\\\\.*$/'
    )
    ->layer('RunnerReporter', 'src/Runner/Reporter')
    ->ruleset([
        'Result'           => [],
        'Check'            => ['Result'],
        'ResultCollection' => ['+Check'],
        'RunnerReporter'   => ['+ResultCollection'],
        'Runner'           => ['+RunnerReporter'],
    ]);
