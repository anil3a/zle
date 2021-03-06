<?php

/**
 * Zend Library Extension
 *
 * PHP version 5
 *
 * @category Zle
 * @package  Zle_Test
 * @author   Fabio Napoleoni <f.napoleoni@gmail.com>
 * @license  http://framework.zend.com/license/new-bsd New BSD License
 * @link     http://framework.zend.com/
 */

/**
 * DateTest
 *
 * @category Zle
 * @package  Zle_Test
 * @author   Fabio Napoleoni <f.napoleoni@gmail.com>
 * @license  http://framework.zend.com/license/new-bsd New BSD License
 * @link     http://framework.zend.com/
 */
class DateTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Zle_View_Helper_Date
     */
    private $_helper = null;

    protected function setUp()
    {
        $this->_helper = new Zle_View_Helper_Date();
        $this->_helper->setView(new Zend_View());
    }

    public function testHelperWorksWithStrings()
    {
        $date = date('Y-M-d');
        $expected = new Zend_Date();
        $this->assertEquals(
            $expected->get(Zend_Date::DATE_LONG),
            $this->_helper->date($date, Zend_Date::DATE_LONG),
            'Date should be auto parsed with standard format'
        );
    }

    public function testHelperWorksWithNonStandardFormats()
    {
        $format = 'd/Y/M';
        $date = date($format);
        $expected = new Zend_Date($date, $format);
        $this->assertEquals(
            $expected->get(Zend_Date::DATE_LONG),
            $this->_helper->date($date, Zend_Date::DATE_LONG, $format),
            'Date should be parsed with non standard format'
        );
    }

    public function testHelperWorksWithDate()
    {
        $date = new Zend_Date();
        $this->assertEquals(
            $date->get(Zend_Date::DATE_LONG),
            $this->_helper->date($date),
            'Date should be given using the long format'
        );
    }

    public function testHelperRecognizesOutputFormatArgument()
    {
        $date = new Zend_Date();
        $outputFormat = Zend_Date::DATE_SHORT;
        $this->assertEquals(
            $date->get($outputFormat),
            $this->_helper->date($date, $outputFormat),
            'Date should be given using the short format'
        );
    }

    public function testHelperUsesDbFormatIfNotGiven()
    {
        $dbDate = '2010-03-02';
        $this->assertEquals(
            '03', $this->_helper->date($dbDate, Zend_Date::MONTH),
            "Helper should recognize a date in database format by default"
        );
    }

    public function testHelperThrowsIfNonStringIsGiven()
    {
        try {
            $this->_helper->date(array());
            $this->fail("Expected exception not raised");
        } catch (InvalidArgumentException $e) {
            $this->assertEquals(
                "Input date must be string or Zend_Date", $e->getMessage()
            );
        }
    }
}
