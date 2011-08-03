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
 * WidgetAreaTest
 *
 * @property Zle_View_Helper_WidgetArea helper
 * @category Zle
 * @package  Zle_Test
 * @author   Fabio Napoleoni <f.napoleoni@gmail.com>
 * @license  http://framework.zend.com/license/new-bsd New BSD License
 * @link     http://framework.zend.com/
 */
class WidgetAreaTest extends PHPUnit_Framework_TestCase
{

    protected function setUp()
    {
        $this->helper = new Zle_View_Helper_WidgetArea();
    }

    protected function tearDown()
    {
        Zle_Widget_Container::resetAllAreas();
    }

    /**
     * Test for getAreaShouldReturnTheConfiguredArea
     */
    public function testGetAreaShouldReturnTheConfiguredArea()
    {
        $areaName = 'foo';
        $this->helper->widgetArea($areaName);
        $this->assertSame(
            Zle_Widget_Container::getArea($areaName),
            $this->helper->getArea(),
            "Helper should use area configured with direct call"
        );
    }

    /**
     * Test for shouldReturnSameAreaInstance
     */
    public function testShouldReturnSameAreaInstance()
    {
        $areaName = 'foo';
        $this->helper->widgetArea($areaName);
        $configuredArea = $this->helper->getArea();
        $configuredArea->append(
            Zle_Widget::factory($this->getWidgetOptions(array('title' => 'foo')))
        );
        $this->assertSame(
            $configuredArea, $this->helper->getArea(),
            "Helper should use area configured with direct call"
        );
    }

    /**
     * Test for shouldAddUsingWidget
     */
    public function testShouldAddUsingWidget()
    {
        $widget = Zle_Widget::factory(array('title' => 'title 1'));
        $this->assertSame(
            $this->helper,
            $this->helper->append($widget),
            "Append should have a fluent interface"
        );
        $this->assertContains(
            $widget, $this->helper->getArea(),
            "Widget should be inserted in the right area"
        );
    }

    /**
     * Test for shouldAddUsingSpec
     */
    public function testShouldAddUsingWidgets()
    {
        $widget = Zle_Widget::factory(
            $this->getWidgetOptions(array('title' => 'title 1'))
        );
        $this->helper->append($widget);
        $this->assertContains('title 1', $this->helper->render());
    }

    /**
     * Test for shouldSetWidgetsWhenGivenAsArray
     */
    public function testShouldSetWidgetsWhenGivenAsArray()
    {
        $widgets = array(
            'leftSidebar' => array(
                $this->getWidgetOptions(array('title' => 'Left 1')),
                $this->getWidgetOptions(array('title' => 'Left 2')),
            ),
            'rightSidebar' => array(
                $this->getWidgetOptions(
                    array('name' => 'bar', 'title' => 'Right')
                ),
            ),
        );
        // set all widgets
        $this->helper->set($widgets);
        // check for area content
        $this->assertRegExp(
            '/Left 1.*Left 2/s', $this->helper->getArea('leftSidebar')->render(),
            "Left sidebar should contain both widgets"
        );
        $this->assertContains(
            'Right', $this->helper->getArea('rightSidebar')->render(),
            "Right sidebar should contain one widgets"
        );
    }

    /**
     * Return a configured view object
     *
     * @return Zend_View
     */
    protected function getView()
    {
        $view = new Zend_View();
        $view->setScriptPath(__DIR__ . '/../../Widget/_files');
        return $view;
    }

    /**
     * Return options to configure a widget
     *
     * @param array $options override options
     *
     * @return array
     */
    protected function getWidgetOptions(array $options = array())
    {
        return array_merge(
            array(
                 'name' => 'foo', 'model' => array(),
                 'view' => $this->getView(), 'partial' => 'foo.phtml',
            ),
            $options
        );
    }
}
