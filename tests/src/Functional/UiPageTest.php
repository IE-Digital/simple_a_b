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
  protected static $modules = ['simple_a_b'];


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
    $account = $this->drupalCreateUser(['create ab tests']);
    $this->drupalLogin($account);

    $this->drupalGet('/admin/config/user-interface/simple-a-b');
    $this->assertSession()->statusCodeEquals(200);

//    $this->assertSession()->pageTextContains('This is a list of available A/B tests.');
  }

}
