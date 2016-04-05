<?php

namespace Drupal\clutch\Tests;

use Drupal\Tests\UnitTestCase;
use Drupal\clutch\ClutchBuilder;
use Drupal\clutch\ComponentBuilder;
use Drupal\clutch\FormBuilder;
/**
 * @group ClutchUnitTest
 * Clutch FormBuilder Tests
 */
class FormBuilderTest extends UnitTestCase {

    protected function setUp() {
        parent::setUp();

        // $this->$bundles = array('contact-us',
        //                  'footer');

        $this->form_builder = $this->getMockBuilder('\Drupal\clutch\FormBuilder')
            ->disableOriginalConstructor();
    }

    public function test_FormBuilder_Calls_ParentCreateEntitiesMethod() {
        //arrange
        $mockFormBuilder = $this->form_builder->getMock();
        $mockFormBuilder->expects($this->once())
                        ->method('createEntitiesFromTemplate')
                        ->with($this->contains('contact-us'));
        //act
        //assert
        $mockFormBuilder->createEntitiesFromTemplate(array('contact-us'));
    }
}