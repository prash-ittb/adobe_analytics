<?php

namespace Drupal\Tests\adobe_analytics\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Test whether the forms can be submitted.
 *
 * @group adobe_analytics
 */
class AdobeAnalyticsFormSubmitTest extends BrowserTestBase {

  private $dummyValue = 'https://s3.amazonaws.com/pfe_im/dummy/path.js';

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'user',
    'adobe_analytics',
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

    // Test form submit.
    $edit = [
      'mode' => 'cdn',
      'cdn_install_type' => 'amazon',
      'development_s_code_config' => $this->dummyValue,
      'production_s_code_config' => $this->dummyValue,
      'development_s_code' => $this->dummyValue,
      'production_s_code' => $this->dummyValue,
      'footer_js_code' => $this->dummyValue,
      'cdn_custom_tracking_js_before' => $this->dummyValue,
      'cdn_custom_tracking_js_after' => $this->dummyValue,
    ];
    // Save settings form.
    $this->submitForm($edit, t('Save configuration'));
    $this->assertSession()
      ->pageTextContains('The configuration options have been saved.');

    // Test form retains the values after saving.
    $formIds = [
      'edit-development-s-code-config',
      'edit-production-s-code-config',
      'edit-development-s-code',
      'edit-production-s-code',
      'edit-footer-js-code',
      'edit-cdn-custom-tracking-js-before',
      'edit-cdn-custom-tracking-js-after',
    ];
    foreach ($formIds as $formId) {
      $codeElement = $this->xpath('//input[@id=:id]', [':id' => $formId]);
      $this->assertEquals($this->dummyValue, $codeElement[0]->getValue());
    }

    // Test Tag manager submission.
    $edit = [
      'mode' => 'cdn',
      'cdn_install_type' => 'tag',
      'development_tag_manager_container_path' => $this->dummyValue,
      'production_tag_manager_container_path' => $this->dummyValue,
      'tag_manager_footer_js' => $this->dummyValue,
    ];
    // Save settings form.
    $this->submitForm($edit, t('Save configuration'));
    $this->assertSession()
      ->pageTextContains('The configuration options have been saved.');

    // Test form retains the values after saving.
    $formIds = [
      'edit-development-tag-manager-container-path',
      'edit-production-tag-manager-container-path',
      'edit-tag-manager-footer-js',
    ];
    foreach ($formIds as $formId) {
      $codeElement = $this->xpath('//input[@id=:id]', [':id' => $formId]);
      $this->assertEquals($this->dummyValue, $codeElement[0]->getValue());
    }
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
      'data_layer_root_field' => $this->dummyValue,
      'data_layer_json_object' => '[]',
    ];
    // Save settings form.
    $this->submitForm($edit, t('Save configuration'));
    $this->assertSession()
      ->pageTextContains('The configuration options have been saved.');

    // Test form retains the values after saving.
    $codeElement = $this->xpath('//input[@id="edit-data-layer-enabled-1"]');
    $this->assertTrue(isset($codeElement[0]));
    $codeElement = $this->xpath('//input[@id=:id]', [':id' => 'edit-data-layer-root-field']);
    $this->assertEquals($this->dummyValue, $codeElement[0]->getValue());
  }

  /**
   * Test the Data layer custom javascript form submission.
   */
  public function testDataLayerCustomJavascriptFormSubmit() {
    // Access the Adobe analytics form.
    $this->drupalGet('/admin/config/system/adobe_analytics/data_layer_custom_javascript');
    $this->assertSession()->statusCodeEquals(200);

    $edit = [
      'data_layer_custom_javascript' => $this->dummyValue,
    ];
    // Save settings form.
    $this->submitForm($edit, t('Save configuration'));
    $this->assertSession()
      ->pageTextContains('The configuration options have been saved.');

    $codeElement = $this->xpath('//input[@id=:id]', [':id' => 'edit-data-layer-custom-javascript']);
    $this->assertEquals($this->dummyValue, $codeElement[0]->getValue());
  }

  /**
   * Test the Data layer custom javascript form submission.
   */
  public function testReportSuiteFormSubmit() {
    // Access the Adobe analytics form.
    $this->drupalGet('/admin/config/system/adobe_analytics/report_suite');
    $this->assertSession()->statusCodeEquals(200);

    $edit = [
      'mode' => 'dev',
      'development_report_suites' => $this->randomMachineName(),
      'development_domains' => $this->randomMachineName(),
      'production_report_suites' => $this->randomMachineName(),
      'development_report_suites' => $this->randomMachineName(),
      'site_section_prefix' => $this->randomMachineName(5),
      'site_section_delimiter' => $this->randomMachineName(1),
      'page_name_base' => $this->randomMachineName(),
      'page_name_prefix' => $this->randomMachineName(5),
      'page_name_delimiter' => $this->randomMachineName(1),
      'page_name_homepage' => $this->randomMachineName(),
    ];
    // Save settings form.
    $this->submitForm($edit, t('Save configuration'));
    $this->assertSession()
      ->pageTextContains('The configuration options have been saved.');
  }

}
