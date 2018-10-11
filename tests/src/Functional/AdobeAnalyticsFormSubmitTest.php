<?php

namespace Drupal\Tests\adobe_analytics\Functional;

use Drupal\Core\Config\ConfigFactoryInterface;
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
    'adobe_analytics',
  ];

  /**
   * The configuration factory used in this test.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

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
    $this->configFactory = $this->container->get('config.factory');
  }

  /**
   * Test the Validator form submit.
   */
  public function testValidatorSettings() {
    // Access the Adobe analytics form.
    $this->drupalGet('/admin/config/adobe_analytics/validation_config');
    $this->assertSession()->statusCodeEquals(200);

    $edit = [
      'cloud_provider' => $this->randomGenerator->string(),
      'cloud_domain' => $this->randomGenerator->string(),
      'tag_manager_provider' => $this->randomGenerator->string(),
      'tag_manager_domain' => $this->randomGenerator->string(),
    ];

    // Save settings form.
    $this->submitForm($edit, t('Save configuration'));
    $this->assertSession()
      ->pageTextContains('The configuration options have been saved.');
  }

  /**
   * Test the Adobe analytics form submission.
   */
  public function testAdobeAnalyticsFormSubmit() {

    // Access the Adobe analytics form.
    $this->drupalGet('/admin/config/system/adobe_analytics');
    $this->assertSession()->statusCodeEquals(200);

    // Set the configurations.
    $this->configFactory->getEditable('adobe_analytics.validation_config')
      ->set('cloud_provider', 'Dummy Provider')->save();
    $this->configFactory->getEditable('adobe_analytics.validation_config')
      ->set('cloud_domain', 'https://dummy.com/domain')->save();
    $this->configFactory->getEditable('adobe_analytics.validation_config')
      ->set('tag_manager_provider', 'Dummy TM Provider')->save();
    $this->configFactory->getEditable('adobe_analytics.validation_config')
      ->set('tag_manager_domain', 'https://asset.dummy.com/domain')->save();

    // Test form validation.
    $edit = [
      'installation_mode' => 'cdn',
      'cdn_install_type' => 'amazon',
      'development_s_code_config' => $this->randomMachineName(),
      'production_s_code_config' => $this->randomMachineName(),
      'development_s_code' => $this->randomMachineName(),
      'production_s_code' => $this->randomMachineName(),
      'development_footer_js_code' => $this->randomMachineName(),
      'production_footer_js_code' => $this->randomMachineName(),
      'development_cdn_custom_tracking_js_before' => $this->randomMachineName(),
      'development_cdn_custom_tracking_js_after' => $this->randomMachineName(),
      'production_cdn_custom_tracking_js_before' => $this->randomMachineName(),
      'production_cdn_custom_tracking_js_after' => $this->randomMachineName(),
    ];
    // Save settings form.
    $this->submitForm($edit, t('Save configuration'));
    $this->assertSession()
      ->pageTextContains("Scripts can only be hosted at authorized locations, such as " . $this->configFactory->getEditable('adobe_analytics.validation_config')
          ->get('cloud_provider') . " e.g " . $this->configFactory->getEditable('adobe_analytics.validation_config')
          ->get('cloud_domain') . " or on " . $this->configFactory->getEditable('adobe_analytics.validation_config')
          ->get('tag_manager_provider') . " e.g " . $this->configFactory->getEditable('adobe_analytics.validation_config')
          ->get('tag_manager_domain') . ". Please correct the path or request assistance to authorize your domain.");

    $edit = [
      'installation_mode' => 'cdn',
      'cdn_install_type' => 'amazon',
      'development_s_code_config' => $this->configFactory->getEditable('adobe_analytics.validation_config')
          ->get('cloud_domain') . '/dev/s_code_config.js',
      'production_s_code_config' => $this->configFactory->getEditable('adobe_analytics.validation_config')
          ->get('cloud_domain') . '/prod/s_code_config.js',
      'development_s_code' => $this->configFactory->getEditable('adobe_analytics.validation_config')
          ->get('cloud_domain') . '/dev/s_code.js',
      'production_s_code' => $this->configFactory->getEditable('adobe_analytics.validation_config')
          ->get('cloud_domain') . '/prod/s_code.js',
      'development_footer_js_code' => $this->configFactory->getEditable('adobe_analytics.validation_config')
          ->get('cloud_domain') . '/dev/footer.js',
      'production_footer_js_code' => $this->configFactory->getEditable('adobe_analytics.validation_config')
          ->get('cloud_domain') . '/prod/footer.js',
      'development_cdn_custom_tracking_js_before' => $this->configFactory->getEditable('adobe_analytics.validation_config')
          ->get('cloud_domain') . '/dev/tracking_before.js',
      'development_cdn_custom_tracking_js_after' => $this->configFactory->getEditable('adobe_analytics.validation_config')
          ->get('cloud_domain') . '/dev/tracking_after.js',
      'production_cdn_custom_tracking_js_before' => $this->configFactory->getEditable('adobe_analytics.validation_config')
          ->get('cloud_domain') . '/stage/tracking_before.js',
      'production_cdn_custom_tracking_js_after' => $this->configFactory->getEditable('adobe_analytics.validation_config')
          ->get('cloud_domain') . '/stage/tracking_after.js',
    ];
    // Save settings form.
    $this->submitForm($edit, t('Save configuration'));
    $this->assertSession()
      ->pageTextContains('The configuration options have been saved.');

    // Test Tag manager submission.
    $edit = [
      'installation_mode' => 'cdn',
      'cdn_install_type' => 'tag',
      'development_tag_manager_container_path' => $this->configFactory->getEditable('adobe_analytics.validation_config')
          ->get('tag_manager_domain') . '/dev/container.js',
      'production_tag_manager_container_path' => $this->configFactory->getEditable('adobe_analytics.validation_config')
          ->get('tag_manager_domain') . '/stage/container.js',
      'development_tag_manager_footer_js' => $this->configFactory->getEditable('adobe_analytics.validation_config')
          ->get('cloud_domain') . '/dev/footer.js',
      'production_tag_manager_footer_js' => $this->configFactory->getEditable('adobe_analytics.validation_config')
          ->get('cloud_domain') . '/stage/footer.js',
    ];
    // Save settings form.
    $this->submitForm($edit, t('Save configuration'));
    $this->assertSession()
      ->pageTextContains('The configuration options have been saved.');

//    // Test form retains the values after saving.
//    $formIds = [
//      'edit-development-tag-manager-container-path',
//      'edit-production-tag-manager-container-path',
//      'edit-tag-manager-footer-js',
//    ];
//    foreach ($formIds as $formId) {
//      $codeElement = $this->xpath('//input[@id=:id]', [':id' => $formId]);
//      $this->assertEquals($this->dummyValue, $codeElement[0]->getValue());
//    }
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
      'data_layer_root_field' => $this->randomMachineName(),
      'data_layer_json_object' => '[]',
      'data_layer_level_option' => 'select_layer',
      'data_layer_key' => $this->randomMachineName(),
      'data_layer_value' => $this->randomMachineName()
    ];
    // Save settings form.
    $this->submitForm($edit, t('Save configuration'));
    $this->assertSession()
      ->pageTextContains('The configuration options have been saved.');

    // Test form retains the values after saving.
//    $codeElement = $this->xpath('//input[@id="edit-data-layer-enabled-1"]');
//    $this->assertTrue(isset($codeElement[0]));
//    $codeElement = $this->xpath('//input[@id=:id]', [':id' => 'edit-data-layer-root-field']);
//    $this->assertEquals($this->randomMachineName(), $codeElement[0]->getValue());
  }

  /**
   * Test the Data layer custom javascript form submission.
   */
  public function testDataLayerCustomJavascriptFormSubmit() {
    // Set the configurations.
    $this->configFactory->getEditable('adobe_analytics.validation_config')
      ->set('cloud_domain', 'https://dummy.com/domain')->save();

    // Access the Adobe analytics form.
    $this->drupalGet('/admin/config/system/adobe_analytics/data_layer_custom_javascript');
    $this->assertSession()->statusCodeEquals(200);

    $edit = [
      'data_layer_custom_javascript' => $this->configFactory->getEditable('adobe_analytics.validation_config')
          ->get('cloud_domain') . '/custom.js',
    ];
    // Save settings form.
    $this->submitForm($edit, t('Save configuration'));
    $this->assertSession()
      ->pageTextContains('The configuration options have been saved.');

//    $codeElement = $this->xpath('//input[@id=:id]', [':id' => 'edit-data-layer-custom-javascript']);
//    $this->assertEquals($this->dummyValue, $codeElement[0]->getValue());
  }

  /**
   * Test the Data layer custom javascript form submission.
   */
  public function testReportSuiteFormSubmit() {
    // Access the Adobe analytics form.
    $this->drupalGet('/admin/config/system/adobe_analytics/report_suite');
    $this->assertSession()->statusCodeEquals(200);

    $edit = [
      'report_suites_mode' => 'dev',
      'development_report_suites' => $this->randomMachineName(),
      'production_report_suites' => $this->randomMachineName(),
      'development_domains' => $this->randomMachineName(),
      'production_domains' => $this->randomMachineName(),
      'site_section_prefix' => $this->randomMachineName(),
      'site_section_delimiter' => $this->randomMachineName(),
      'page_name_base' => $this->randomMachineName(),
      'page_name_prefix' => $this->randomMachineName(),
      'page_name_delimiter' => $this->randomMachineName(),
      'page_name_homepage' => $this->randomMachineName(),
    ];

    // Save settings form.
    $this->submitForm($edit, t('Save configuration'));
    $this->assertSession()
      ->pageTextContains('The configuration options have been saved.');
  }

}
