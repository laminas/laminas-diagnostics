<?php

namespace Laminas\Diagnostics\Check;

use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\ResultInterface;
use Laminas\Diagnostics\Result\Success;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Parser;

use function class_exists;
use function file_get_contents;
use function function_exists;
use function sprintf;

/**
 * Checks if a YAML file is available and valid
 */
class YamlFile extends AbstractFileCheck
{
    /**
     * @param string $file
     * @return ResultInterface
     */
    protected function validateFile($file)
    {
        if (class_exists(Parser::class)) {
            $parser = new Parser();

            try {
                $parser->parse(file_get_contents($file));
            } catch (ParseException $e) {
                return new Failure(sprintf('Unable to parse YAML file "%s"!', $file), $e->getMessage());
            }

            return new Success();
        }

        if (function_exists('yaml_parse_file')) {
            if (@yaml_parse_file($file) === false) {
                return new Failure(sprintf('Unable to parse YAML file "%s"!', $file));
            }

            return new Success();
        }

        return new Failure('No YAML-parser found! Please install "symfony/yaml" or "PECL yaml >= 0.4.0".');
    }
}
