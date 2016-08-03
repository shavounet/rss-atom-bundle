<?php

namespace Debril\RssAtomBundle\Protocol\Parser;

use Debril\RssAtomBundle\Protocol\Filter\ModifiedSince;
use Debril\RssAtomBundle\Tests\Protocol\ParserAbstract;

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.0 on 2013-01-27 at 00:26:56.
 */
class RssParserTest extends ParserAbstract
{
    /**
     * @var RssParser
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new RssParser();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers Debril\RssAtomBundle\Protocol\Parser\RssParser::canHandle
     */
    public function testCannotHandle()
    {
        $file = dirname(__FILE__).'/../../../Resources/sample-atom.xml';
        $xmlBody = new \SimpleXMLElement(file_get_contents($file));
        $this->assertFalse($this->object->canHandle($xmlBody));
    }

    /**
     * @covers Debril\RssAtomBundle\Protocol\Parser\RssParser::canHandle
     */
    public function testCanHandle()
    {
        $file = dirname(__FILE__).'/../../../Resources/sample-rss.xml';
        $xmlBody = new \SimpleXMLElement(file_get_contents($file));
        $this->assertTrue($this->object->canHandle($xmlBody));
    }

    /**
     * @covers Debril\RssAtomBundle\Protocol\Parser\RssParser::checkBodyStructure
     * @expectedException \Debril\RssAtomBundle\Exception\ParserException
     */
    public function testParseError()
    {
        $file = dirname(__FILE__).'/../../../Resources/truncated-rss.xml';
        $xmlBody = new \SimpleXMLElement(file_get_contents($file));
        $filters = array(new ModifiedSince(new \DateTime()));
        $this->object->parse($xmlBody, new FeedContent(), $filters);
    }

    /**
     * @covers Debril\RssAtomBundle\Protocol\Parser\RssParser::parseBody
     */
    public function testParse()
    {
        $file = dirname(__FILE__).'/../../../Resources/sample-rss.xml';
        $xmlBody = new \SimpleXMLElement(file_get_contents($file));

        $date = \DateTime::createFromFormat('Y-m-d', '2005-10-10');
        $filters = array(new ModifiedSince($date));
        $feed = $this->object->parse($xmlBody, new FeedContent(), $filters);

        $this->assertInstanceOf('Debril\RssAtomBundle\Protocol\FeedInInterface', $feed);

        $this->assertNotNull($feed->getPublicId(), 'feed->getPublicId() should not return an empty value');

        $this->assertGreaterThan(0, $feed->getItemsCount());
        $this->assertInstanceOf('\DateTime', $feed->getLastModified());
        $this->assertInternalType('string', $feed->getLink());
        $this->assertInternalType('string', $feed->getTitle());
        $this->assertNotNull($feed->getLink());
        $this->assertNotNull($feed->getTitle());

        $item = current($feed->getItems());
        $this->assertInternalType('string', $item->getAuthor());
        $this->assertEquals('john.doe@mail.com', $item->getAuthor());

        $medias = $item->getMedias();
        $count = 0;
        foreach ($medias as $media) {
            $this->assertInstanceOf('Debril\RssAtomBundle\Protocol\Parser\Media', $media);
            ++$count;
        }

        $this->assertEquals(1, $count);

        $categories = $item->getCategories();
        $this->assertCount(2, $categories);
        $this->assertInstanceOf('Debril\RssAtomBundle\Protocol\Parser\Category', $categories[0]);
        $this->assertEquals('Category1', $categories[0]->getName());
        $this->assertEquals('Category2', $categories[1]->getName());
    }

    /**
     * @covers Debril\RssAtomBundle\Protocol\Parser\RssParser::parseBody
     * @covers Debril\RssAtomBundle\Protocol\Parser\RssParser::setLastModified
     */
    public function testParseWithoutBuildDate()
    {
        $file = dirname(__FILE__).'/../../../Resources/sample-rss-pubdate.xml';
        $xmlBody = new \SimpleXMLElement(file_get_contents($file));

        $date = \DateTime::createFromFormat('Y-m-d', '2005-10-10');
        $filters = array(new ModifiedSince($date));
        $feed = $this->object->parse($xmlBody, new FeedContent(), $filters);

        $this->assertInstanceOf('Debril\RssAtomBundle\Protocol\FeedInInterface', $feed);

        $this->assertNotNull($feed->getPublicId(), 'feed->getPublicId() should not return an empty value');

        $this->assertGreaterThan(0, $feed->getItemsCount());
        $this->assertInstanceOf('\DateTime', $feed->getLastModified());
    }

    /**
     * @covers Debril\RssAtomBundle\Protocol\Parser\RssParser::parseBody
     */
    public function testParseWithoutDate()
    {
        $file = dirname(__FILE__).'/../../../Resources/sample-rss-nodate.xml';
        $xmlBody = new \SimpleXMLElement(file_get_contents($file));

        $date = \DateTime::createFromFormat('Y-m-d', '2005-10-10');
        $filters = array(new ModifiedSince($date));
        $feed = $this->object->parse($xmlBody, new FeedContent(), $filters);

        $this->assertInstanceOf('Debril\RssAtomBundle\Protocol\FeedInInterface', $feed);

        $this->assertNotNull($feed->getPublicId(), 'feed->getPublicId() should not return an empty value');

        $this->assertGreaterThan(0, $feed->getItemsCount());
        $this->assertInstanceOf('\DateTime', $feed->getLastModified());
        $feeds = $feed->getItems();
        $item = next($feeds);
        $this->assertEquals($item->getUpdated(), $feed->getLastModified());
    }

    /**
     * @covers Debril\RssAtomBundle\Protocol\Parser::setDateFormats
     * @covers Debril\RssAtomBundle\Protocol\Parser\RssParser::__construct
     * @dataProvider getDefaultFormats
     */
    public function testSetDateFormats($default)
    {
        $this->object->setdateFormats($default);
        $this->assertEquals($default, $this->readAttribute($this->object, 'dateFormats'));
    }

    /**
     * @covers Debril\RssAtomBundle\Protocol\Parser::guessDateFormat
     * @dataProvider getDefaultFormats
     * @expectedException \Debril\RssAtomBundle\Exception\ParserException
     */
    public function testGuessDateFormatException(array $default)
    {
        $this->object->setdateFormats($default);

        $date = '2003-13T18:30:02Z';
        $this->object->guessDateFormat($date);
    }

    /**
     * @covers Debril\RssAtomBundle\Protocol\Parser\RssParser::parseBody
     */
    public function testParseWithContentExtension()
    {
        $file = dirname(__FILE__).'/../../../Resources/sample-rss-content.xml';
        $xmlBody = new \SimpleXMLElement(file_get_contents($file));

        $date = \DateTime::createFromFormat('Y-m-d', '2005-10-10');
        $filters = array(new ModifiedSince($date));
        $feed = $this->object->parse($xmlBody, new FeedContent(), $filters);

        $this->assertGreaterThan(0, $feed->getItemsCount());

        $item = current($feed->getItems());

        $this->assertEquals('Here is a short summary...', $item->getSummary());
        $this->assertEquals('Here is the real content', $item->getDescription());
    }

    /**
     * @covers Debril\RssAtomBundle\Protocol\Parser\RssParser::parseBody
     */
    public function testParseWithDublinCoreExtension()
    {
        $file = dirname(__FILE__).'/../../../Resources/sample-rss-creator.xml';
        $xmlBody = new \SimpleXMLElement(file_get_contents($file));

        $date = \DateTime::createFromFormat('Y-m-d', '2005-10-10');
        $filters = array(new ModifiedSince($date));
        $feed = $this->object->parse($xmlBody, new FeedContent(), $filters);

        $this->assertGreaterThan(0, $feed->getItemsCount());

        $item = current($feed->getItems());

        $this->assertInternalType('string', $item->getAuthor());
        $this->assertEquals('John Doe', $item->getAuthor());
    }
}
