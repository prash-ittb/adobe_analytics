<?php

namespace Drupal\Tests\adobe_analytics\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Test access permission for the Adobe Analytics configuration form.
 *
 * @group adobe_analytics
 */
class AdobeAnalyticsPermissionTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'user',
    'adobe_analytics',
  ];

  /**
   * An administrative user with permission to administer adobe analytics.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * Test the Adobe analytics access.
   */
  public function testAdministerAdobeAnalyticsAccess() {
    // Create test admin user.
    $this->adminUser = $this->drupalCreateUser([
      'access administration pages',
      'administer adobe analytics configuration',
    ]);

    // Login as admin user.
    $this->drupalLogin($this->adminUser);
    // Access the Adobe analytics form.
    $this->drupalGet('/admin/config/system/adobe_analytics');
    $this->assertSession()->statusCodeEquals(200);
    $this->drupalLogout();

    // Create a normal user.
    $user = $this->drupalCreateUser([
      'access content',
    ]);
    // Login as normal user.
    $this->drupalLogin($user);
    // Try to access the Adobe analytics page.
    $this->drupalGet('/admin/config/system/adobe_analytics');
    $this->assertSession()->statusCodeEquals(403);
    $this->drupalLogout();
  }

  /**
   * Test the Data layer access.
   */
  public function testAdobeDataLayerAccess() {
    // Create test admin user.
    $this->adminUser = $this->drupalCreateUser([
      'access administration pages',
      'administer adobe analytics configuration',
    ]);

    // Login as admin user.
    $this->drupalLogin($this->adminUser);
    // Access the Adobe analytics data layer form.
    $this->drupalGet('/admin/config/system/adobe_analytics/data_layer');
    $this->assertSession()->statusCodeEquals(200);

    // Create a normal user.
    $user = $this->drupalCreateUser([
      'access content',
    ]);
    // Login as normal user.
    $this->drupalLogin($user);
    // Try to access the Adobe analytics data layer page.
    $this->drupalGet('/admin/config/system/adobe_analytics/data_layer');
    $this->assertSession()->statusCodeEquals(403);
  }

  /**
   * Test the Data layer custom javascript access.
   */
  public function testAdobeDataLayerCustomJavaScriptAccess() {
    // Create test admin user.
    $this->adminUser = $this->drupalCreateUser([
      'access administration pages',
      'administer adobe analytics configuration',
    ]);

    // Login as admin user.
    $this->drupalLogin($this->adminUser);
    // Access the Adobe analytics data layer form.
    $this->drupalGet('/admin/config/system/adobe_analytics/data_layer_custom_javascript');
    $this->assertSession()->statusCodeEquals(200);

    // Create a normal user.
    $user = $this->drupalCreateUser([
      'access content',
    ]);
    // Login as normal user.
    $this->drupalLogin($user);
    // Try to access the Adobe analytics data layer page.
    $this->drupalGet('/admin/config/system/adobe_analytics/data_layer_custom_javascript');
    $this->assertSession()->statusCodeEquals(403);
  }

  /**
   * Test the Data layer custom javascript access.
   */
  public function testAdobeReportSuiteAccess() {
    // Create test admin user.
    $this->adminUser = $this->drupalCreateUser([
      'access administration pages',
      'administer adobe analytics configuration',
    ]);

    // Login as admin user.
    $this->drupalLogin($this->adminUser);
    // Access the Adobe analytics data layer form.
    $this->drupalGet('/admin/config/system/adobe_analytics/report_suite');
    $this->assertSession()->statusCodeEquals(200);

    // Create a normal user.
    $user = $this->drupalCreateUser([
      'access content',
    ]);
    // Login as normal user.
    $this->drupalLogin($user);
    // Try to access the Adobe analytics data layer page.
    $this->drupalGet('/admin/config/system/adobe_analytics/report_suite');
    $this->assertSession()->statusCodeEquals(403);
  }

}
