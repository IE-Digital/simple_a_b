<?php

namespace Drupal\Tests\simple_a_b\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests that the Simple A/B UI pages are reachable.
 *
 * @group simple_a_b_ui
 */
class UiPageTest extends BrowserTestBase {


  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['views','simple_a_b'];


  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
  }

  /**
   * Tests that a/b listing page works.
   */
  public function testSimpleABListingPage() {
    // create a user with permission to create ab tests
    $account = $this->drupalCreateUser(['create ab tests']);
    // login the drupal user
    $this->drupalLogin($account);

    // navigate to simple-a-b listing page
    $this->drupalGet('/admin/config/user-interface/simple-a-b');
    // check we have permission to view the page
    $this->assertSession()->statusCodeEquals(200);

    // check that we can find the view the output on A/B listing page
    $this->assertSession()->pageTextContains('No A/B tests have been found.');
  }


  /**
   * Tests a/b listing without permission.
   */
  public function testSimpleABListingPageWithoutPermission(){
    // create a user with the wrong permissions to edit settings
    $account = $this->drupalCreateUser(['configure ab tests']);
    // login the drupal user
    $this->drupalLogin($account);

    // navigate to simple-a-b settings page
    $this->drupalGet('/admin/config/user-interface/simple-a-b');
    // check we don't have permission to view the page
    $this->assertSession()->statusCodeEquals(403);
  }


  /**
   * Tests that a/b settings page works.
   */
  public function testSimpleABSettingsPage() {
    // create a user with permission to configure ab tests
    $account = $this->drupalCreateUser(['configure ab tests']);
    // login the drupal user
    $this->drupalLogin($account);

    // navigate to simple-a-b settings page
    $this->drupalGet('/admin/config/user-interface/simple-a-b/settings');
    // check we have permission to view the page
    $this->assertSession()->statusCodeEquals(200);

    // check that we can find the view the output on A/B settings
    $this->assertSession()->pageTextContains('Settings to configure how simple a/b handles reporting.');

    // try to save the settings config
    $this->getSession()->getPage()->pressButton('Save configuration');

    // check if we get a response from pressing the save button
    $this->assertSession()->pageTextContains('The configuration options have been saved.');
  }

  /**
   * Tests a/b settings without permission.
   */
  public function testSimpleABSettingsPageWithoutPermission(){
    // create a user with the wrong permissions to edit settings
    $account = $this->drupalCreateUser(['create ab tests']);
    // login the drupal user
    $this->drupalLogin($account);

    // navigate to simple-a-b settings page
    $this->drupalGet('/admin/config/user-interface/simple-a-b/settings');
    // check we don't have permission to view the page
    $this->assertSession()->statusCodeEquals(403);
  }

}
