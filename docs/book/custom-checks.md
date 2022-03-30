# Writing Custom Checks

A Check class MUST implement [CheckInterface](https://github.com/laminas/laminas-diagnostics/tree/master/src/Check/CheckInterface.php)
and provide the following methods:

```php
<?php
namespace Laminas\Diagnostics\Check;

use Laminas\Diagnostics\Result\ResultInterface;

interface CheckInterface
{
    /**
     * @return ResultInterface
     */
    public function check();

    /**
     * Return a label describing this test instance.
     *
     * @return string
     */
    public function getLabel();
}
```

The main `check()` method is responsible for performing the actual check, and is
expected to return a [ResultInterface](https://github.com/laminas/laminas-diagnostics/tree/master/src/Result/ResultInterface.php)
instance. It is recommended to use the built-in result classes for
compatibility with the diagnostics Runner and other checks.

Below is an example class that checks if the PHP default timezone is set to UTC.

```php
<?php
namespace MyApp\Diagnostics\Check;

use Laminas\Diagnostics\Check\CheckInterface;
use Laminas\Diagnostics\Result\Success;
use Laminas\Diagnostics\Result\Failure;

class TimezoneSetToUTC implements CheckInterface
{
    public function check()
    {
        $tz = date_default_timezone_get();

        if ($tz === 'UTC') {
            return new Success('Default timezone is UTC');
        }

        return new Failure('Default timezone is not UTC! It is actually ' . $tz);
    }

    public function getLabel()
    {
        return 'Check if PHP default timezone is set to UTC';
    }
}
```
