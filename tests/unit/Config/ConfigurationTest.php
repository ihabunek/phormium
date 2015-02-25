<?php

namespace Phormium\Tests\Config;

use Phormium\Config\Configuration;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * @group config
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
