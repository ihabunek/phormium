<?php

namespace Phormium\Tests;

use \Phormium\Tests\Models\Person;

use \Phormium\Log;
use \Phormium\NullLogger;

/**
 * @group logger
 */
class LoggerTest extends \PHPUnit_Framework_TestCase
{
    public function testNullLoggerHasNoOutput()
    {
        ob_start();

        $nl = new NullLogger();
        $nl->trace("Is this the real life?");
        $nl->debug("Is this just fantasy?");
        $nl->info("Caught in a landslide");
        $nl->warn("No escape from reality");
        $nl->error("Open your eyes");
        $nl->fatal("Look up to the skies and see...");

        $actual = ob_get_clean();
        $expected = "";

        self::assertSame($expected, $actual);
    }

    public function testLoggingWithoutLog4php()
    {
        ob_start();

        Log::trace("Is this the real life?");
        Log::debug("Is this just fantasy?");
        Log::info("Caught in a landslide");
        Log::warn("No escape from reality");
        Log::error("Open your eyes");
        Log::fatal("Look up to the skies and see...");

        $actual = ob_get_clean();
        $expected = "";

        self::assertSame($expected, $actual);
     }
}

