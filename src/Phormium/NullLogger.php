<?php

namespace Phormium;

/**
 * A fake logger class used when logging is not enabled.
 */
class NullLogger
{
    public function trace()
    {
    }

    public function debug()
    {
    }

    public function info()
    {
    }

    public function warn()
    {
    }

    public function error()
    {
    }

    public function fatal()
    {
    }
}
