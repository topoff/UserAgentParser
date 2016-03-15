<?php
namespace UserAgentParserTest\Integration\Provider;

use BrowscapPHP\Browscap;
use UserAgentParser\Provider\BrowscapPhp;

/**
 * @coversNothing
 */
class BrowscapPhpTest extends AbstractProviderTestCase
{
    private function getParserWithWarmCache($type)
    {
        $filename = 'php_browscap.ini';
        if ($type != '') {
            $filename = $type . '_' . $filename;
        }

        $cache = new \WurflCache\Adapter\Memory();

        $browscap = new Browscap();
        $browscap->setCache($cache);
        $browscap->convertFile('tests/resources/browscap/' . $filename);

        return $browscap;
    }

    /**
     * @expectedException \UserAgentParser\Exception\NoResultFoundException
     */
    public function testNoResultFoundWithWarmCache()
    {
        $provider = new BrowscapPhp($this->getParserWithWarmCache(''));

        $result = $provider->parse('...');
    }

    public function testRealResultBot()
    {
        $provider = new BrowscapPhp($this->getParserWithWarmCache(''));

        $result = $provider->parse('Mozilla/2.0 (compatible; Ask Jeeves)');

        $this->assertInstanceOf('UserAgentParser\Model\UserAgent', $result);
        $this->assertTrue($result->getBot()
            ->getIsBot());
        $this->assertEquals('AskJeeves', $result->getBot()
            ->getName());
        // only in full!
        $this->assertNull($result->getBot()
            ->getType());

        $rawResult = $result->getProviderResultRaw();
        $this->assertInstanceOf('stdClass', $rawResult);
    }

    public function testRealResultDevice()
    {
        $provider = new BrowscapPhp($this->getParserWithWarmCache(''));

        $result = $provider->parse('Mozilla/5.0 (SMART-TV; X11; Linux armv7l) AppleWebkit/537.42 (KHTML, like Gecko) Chromium/48.0.1349.2 Chrome/25.0.1349.2 Safari/537.42');

        $this->assertInstanceOf('UserAgentParser\Model\UserAgent', $result);
        $this->assertEquals('Chromium', $result->getBrowser()
            ->getName());
        $this->assertEquals('48.0', $result->getBrowser()
            ->getVersion()
            ->getComplete());

        $this->assertEquals(null, $result->getRenderingEngine()
            ->getName());
        $this->assertEquals(null, $result->getRenderingEngine()
            ->getVersion()
            ->getComplete());

        $this->assertEquals('Linux', $result->getOperatingSystem()
            ->getName());
        $this->assertEquals(null, $result->getOperatingSystem()
            ->getVersion()
            ->getComplete());

        $this->assertEquals(null, $result->getDevice()
            ->getBrand());
        $this->assertEquals(null, $result->getDevice()
            ->getModel());
        $this->assertEquals('TV Device', $result->getDevice()
            ->getType());
    }
}
