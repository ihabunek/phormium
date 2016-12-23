<?php

namespace Phormium\Tests;

use Phormium\Config\Configuration;

/**
 * @group config
 * @group unit
 */
class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testConfiguration()
    {
        $conf = new Configuration();
        $builder = $conf->getConfigTreeBuilder();

        $expected = 'Symfony\Component\Config\Definition\Builder\TreeBuilder';
        $this->assertInstanceOf($expected, $builder);
    }
}
