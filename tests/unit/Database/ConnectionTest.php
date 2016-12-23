<?php

namespace Phormium\Tests\Unit\Database;

use Mockery as m;
use PDO;
use Phormium\Database\Connection;
use Phormium\Database\Driver;
use Phormium\Query\QuerySegment;

/**
 * @group unit
 * @group database
 */
class ConnectionTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function testConstructor()
    {
        $driver = Driver::PGSQL;
        $emitter = m::mock("Evenement\\EventEmitter");
        $pdo = m::mock("PDO");

        $pdo->shouldReceive('getAttribute')
            ->with(PDO::ATTR_DRIVER_NAME)
            ->once()
            ->andReturn($driver);

        $conn = new Connection($pdo, $emitter);

        $this->assertSame($driver, $conn->getDriver());
        $this->assertSame($emitter, $conn->getEmitter());
        $this->assertSame($pdo, $conn->getPDO());
    }
}
