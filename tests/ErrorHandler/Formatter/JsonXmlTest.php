<?php namespace Phprest\ErrorHandler\Formatter;

use Phprest\Application;
use Phprest\Config;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Phprest\Exception\BadRequest;
use Symfony\Component\HttpFoundation\Request;

class JsonXmlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Config
     */
    protected $config;

    public function setUp()
    {
        $this->config = new Config('phprest', 1);
        $this->setContainerElements($this->config);
    }

    public function testFormatWithSimpleException()
    {
        $jsonXmlFormatter = new JsonXml($this->config);

        $this->assertEquals(
            '{"code":9,"message":"test","details":[]}',
            $jsonXmlFormatter->format(new \LogicException('test', 9))
        );
    }

    public function testFormatWithDetailedException()
    {
        $jsonXmlFormatter = new JsonXml($this->config);

        $this->assertEquals(
            '{"code":11,"message":"Bad Request","details":[1,2,3,["a","b"]]}',
            $jsonXmlFormatter->format(new BadRequest(11, [1,2,3,['a','b']]))
        );
    }

    public function testFormatWithNotAcceptable()
    {
        $request = Request::createFromGlobals();
        $request->headers->set('Accept', 'yaml');

        $jsonXmlFormatter = new JsonXml($this->config, $request);

        $this->assertEquals(
            '{"code":0,"message":"Not Acceptable","details":["yaml is not supported"]}',
            $jsonXmlFormatter->format(new \Exception())
        );
    }

    /**
     * @param Config $config
     */
    protected function setContainerElements(Config $config)
    {
        AnnotationRegistry::registerLoader('class_exists');

        $config->getHateoasService()->register(
            $config->getContainer(),
            $config->getHateoasConfig()
        );

        $config->getContainer()->add(Application::CNTRID_VENDOR, $config->getVendor());
        $config->getContainer()->add(Application::CNTRID_API_VERSION, $config->getApiVersion());
        $config->getContainer()->add(Application::CNTRID_DEBUG, $config->isDebug());
    }
}
