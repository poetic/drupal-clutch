<?php

/**
 * @file
 * Contains Drupal\houston\Tests\ClutchTest.
 */

namespace Drupal\houston\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Tests Clutch installation profile expectations.
 *
 * @group houston
 */
class HoustonTest extends WebTestBase {

  protected $profile = 'houston';

  /**
   * The admin user.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * Tests Clutch installation profile.
   */
  function testHouston() {
    // Go to the home page and make sure we get a 200 response
    $this->drupalGet('');
    $this->assertResponse(200);

    // Create a user with the administrator role
    $user = $this->drupalCreateUser();
    $user->roles[] = 'administrator';
    $user->save();

    // Login with the administrator user account and make sure the toolbar is
    // visible
    $this->drupalLogin($user);
    $this->drupalGet('');
    $this->assertText(t('Manage'));
  }
}
