<?php

/**
 * @file
 * Contains Drupal\clutch\Tests\ClutchTest.
 */

namespace Drupal\clutch\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Tests Clutch installation profile expectations.
 *
 * @group clutch
 */
class ClutchTest extends WebTestBase {

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
  function testClutch() {
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
