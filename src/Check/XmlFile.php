<?php

/**
 * @see       https://github.com/laminas/laminas-diagnostics for the canonical source repository
 * @copyright https://github.com/laminas/laminas-diagnostics/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-diagnostics/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Diagnostics\Check;

use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\ResultInterface;
use Laminas\Diagnostics\Result\Success;
use XMLReader;

/**
 * Checks if an XML file is available and valid
 */
class XmlFile extends AbstractFileCheck
{
    /**
     * @param string $file
     * @return ResultInterface
     */
    protected function validateFile($file)
    {
        $xmlReader = new XMLReader();

        if (! $xmlReader->open($file)) {
            return new Failure(sprintf('Could not open "%s" with XMLReader!', $file));
        }

        $xmlReader->setParserProperty(XMLReader::VALIDATE, true);

        if (! $xmlReader->isValid()) {
            return new Failure(sprintf('File "%s" is not valid XML!', $file));
        }

        if (! @simplexml_load_file($file)) {
            return new Failure(sprintf('File "%s" is not well-formed XML!', $file));
        }

        return new Success();
    }
}
