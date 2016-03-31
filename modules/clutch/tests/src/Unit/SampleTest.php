<?php

namespace Drupal\clutch\Tests;

use Drupal\Tests\UnitTestCase;
use Drupal\clutch\ComponentBuilder;
/**
 * @group SampleTest
 * Sample PhpUnit Tests
 */
class SampleTest extends UnitTestCase {

    protected function setUp() {
        parent::setUp();

        $this->component_builder = $this->getMockBuilder('\Drupal\clutch\ComponentBuilder')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testCanCreateMockObject() {
        //arrange
        //act
        //assert
        $this->assertInstanceOf(ComponentBuilder::class, $this->component_builder);
    }
}