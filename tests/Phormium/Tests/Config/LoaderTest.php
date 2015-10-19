<?php

namespace Phormium\Tests\Config;

use Phormium\Config\ArrayLoader;
use Phormium\Config\JsonLoader;
use Phormium\Config\YamlLoader;

use Symfony\Component\Yaml\Yaml;

/**
 * @group config
 */
class LoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testArrayLoader()
    {
        $config = ['foo' => 'bar'];

        $loader = new ArrayLoader();

        $this->assertSame($config, $loader->load($config));

        $this->assertTrue($loader->supports([]));
        $this->assertFalse($loader->supports(""));
        $this->assertFalse($loader->supports(123));
        $this->assertFalse($loader->supports(new \stdClass));
    }

    public function testJsonLoader()
    {
        $config = ['foo' => 'bar'];
        $json = json_encode($config);

        $tempFile = tempnam(sys_get_temp_dir(), "pho") . ".json";
        file_put_contents($tempFile, $json);

        $loader = new JsonLoader();

        $this->assertSame($config, $loader->load($tempFile));

        $this->assertTrue($loader->supports("foo.json"));
        $this->assertFalse($loader->supports("foo.yaml"));
        $this->assertFalse($loader->supports(123));
        $this->assertFalse($loader->supports([]));
        $this->assertFalse($loader->supports(new \stdClass));

        unlink($tempFile);
    }

    public function testYamlLoader()
    {
        $config = ['foo' => 'bar'];
        $yaml = Yaml::dump($config);

        $tempFile = tempnam(sys_get_temp_dir(), "pho") . ".yaml";
        file_put_contents($tempFile, $yaml);

        $loader = new YamlLoader();

        $this->assertSame($config, $loader->load($tempFile));

        $this->assertTrue($loader->supports("foo.yaml"));
        $this->assertFalse($loader->supports("foo.json"));
        $this->assertFalse($loader->supports(123));
        $this->assertFalse($loader->supports([]));
        $this->assertFalse($loader->supports(new \stdClass));

        unlink($tempFile);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Config file not found at "doesnotexist.yaml".
     */
    public function testLoadFileFailed()
    {
        $loader = new YamlLoader();
        $loader->load("doesnotexist.yaml");
    }
}
