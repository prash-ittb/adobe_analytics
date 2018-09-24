<?php
/**
 * Created by PhpStorm.
 * User: malabya
 * Date: 24/09/18
 * Time: 6:54 PM
 */

namespace Drupal\Tests\adobe_analytics\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Test whether the forms can be submitted.
 *
 * @group adobe_analytics
 */
class AdobeAnalyticsFormSubmitTest extends BrowserTestBase {
  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'user',
    'adobe_analytics'
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $permission = [
      'access administration pages',
      'administer adobe analytics configuration',
    ];
    $adminUser = $this->drupalCreateUser($permission);
    $this->drupalLogin($adminUser);
  }

  /**
   * Test the Adobe analytics form submission.
   */
  public function testAdobeAnalyticsFormSubmit() {
    // Access the Adobe analytics form.
    $this->drupalGet('/admin/config/system/adobe_analytics');
    $this->assertSession()->statusCodeEquals(200);

    // Test form validation.
    $edit = [
      'mode' => 'cdn',
      'cdn_install_type' => 'amazon',
      'development_s_code_config' => $this->randomGenerator->string(),
      'production_s_code_config' => $this->randomGenerator->string(),
      'development_s_code' => $this->randomGenerator->string(),
      'production_s_code' => $this->randomGenerator->string(),
      'footer_js_code' => $this->randomGenerator->string(),
      'cdn_custom_tracking_js_before' => $this->randomGenerator->string(),
      'cdn_custom_tracking_js_after' => $this->randomGenerator->string(),
    ];
    // Save settings form.
    $this->submitForm($edit, t('Save configuration'));
    $this->assertSession()
      ->pageTextContains('Please enter a fully-qualified URL, such as https://s3.amazonaws.com/pfe_im/...');

    // Test form submit
    $edit = [
      'mode' => 'cdn',
      'cdn_install_type' => 'amazon',
      'development_s_code_config' => 'https://s3.amazonaws.com/pfe_im/js/dev/pcc/custom/d8test/s_code_config.js',
      'production_s_code_config' => 'https://s3.amazonaws.com/pfe_im/js/dev/pcc/custom/d8test/s_code_config.js',
      'development_s_code' => 'https://s3.amazonaws.com/pfe_im/js/dev/pcc/custom/d8test/s_code_config.js',
      'production_s_code' => 'https://s3.amazonaws.com/pfe_im/js/dev/pcc/s_code.js',
      'footer_js_code' => 'https://s3.amazonaws.com/pfe_im/js/dev/pcc/custom/d8test/footer.js',
      'cdn_custom_tracking_js_before' => 'https://s3.amazonaws.com/pfe_im/js/dev/pcc/custom/d8test/before_t.js',
      'cdn_custom_tracking_js_after' => 'https://s3.amazonaws.com/pfe_im/js/dev/pcc/custom/d8test/after_t.js',
    ];
    // Save settings form.
    $this->submitForm($edit, t('Save configuration'));
    $this->assertSession()
      ->pageTextContains('The configuration options have been saved.');

    $edit = [
      'mode' => 'cdn',
      'cdn_install_type' => 'tag',
      'development_tag_manager_container_path' => $this->randomGenerator->string(),
      'production_tag_manager_container_path' => $this->randomGenerator->string(),
      'tag_manager_footer_js' => $this->randomGenerator->string()
    ];
    // Save settings form.
    $this->submitForm($edit, t('Save configuration'));
    $this->assertSession()
      ->pageTextContains('The configuration options have been saved.');
  }

  /**
   * Test the Data layer form submission.
   */
  public function testDataLayerFormSubmit() {
    // Access the Adobe analytics form.
    $this->drupalGet('/admin/config/system/adobe_analytics/data_layer');
    $this->assertSession()->statusCodeEquals(200);

    $edit = [
      'data_layer_enabled' => '1',
      'data_layer_root_field' => $this->randomGenerator->string(),
      'data_layer_json_object' => $this->randomGenerator->string()
    ];
    // Save settings form.
    $this->submitForm($edit, t('Save configuration'));
    $this->assertSession()
      ->pageTextContains('The configuration options have been saved.');
  }

  /**
   * Test the Data layer custom javascript form submission.
   */
  public function testDataLayerCustomJavascriptFormSubmit() {
    // Access the Adobe analytics form.
    $this->drupalGet('/admin/config/system/adobe_analytics/data_layer_custom_javascript');
    $this->assertSession()->statusCodeEquals(200);

    $edit = [
      'data_layer_custom_javascript' => $this->randomGenerator->string(),
    ];
    // Save settings form.
    $this->submitForm($edit, t('Save configuration'));
    $this->assertSession()
      ->pageTextContains('The configuration options have been saved.');
  }
}