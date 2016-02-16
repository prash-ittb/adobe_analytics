<?php
namespace Drupal\adobeanalytics;

/**
 * @file
 * Test file for the AdobeAnalytics module.
 */
class adobeAnalyticsBaseTest extends \Drupal\simpletest\WebTestBase {

  protected $profile = 'standard';

  public /**
   * Implementation of setUp().
   */
  function setUp() {
    // Enable a couple modules.
    parent::setUp('adobeanalytics');
    menu_rebuild();

    // Create an admin user with all the permissions needed to run tests.
    $this->admin_user = $this->drupalCreateUser([
      'administer AdobeAnalytics configuration',
      'access administration pages',
    ]);
    $this->drupalLogin($this->admin_user);

    // Set some default settings.
    \Drupal::configFactory()->getEditable('adobeanalytics.settings')->set("adobeanalytics_js_file_location", 'http://www.example.com/js/s_code_remote_h.js')->save();
    \Drupal::configFactory()->getEditable('adobeanalytics.settings')->set("adobeanalytics_image_file_location", 'http://examplecom.112.2O7.net/b/ss/examplecom/1/H.20.3--NS/0')->save();
    \Drupal::configFactory()->getEditable('adobeanalytics.settings')->set("adobeanalytics_version", 'H.20.3.')->save();
  }

  public function assertTrackingCode() {
    $this->assertRaw("<!-- AdobeAnalytics code version: ", 'The AdobeAnalytics code was found.');
    // @FIXME
    // Could not extract the default value because it is either indeterminate, or
    // not scalar. You'll need to provide a default value in
    // config/install/adobeanalytics.settings.yml and config/schema/adobeanalytics.schema.yml.
    $this->assertRaw(\Drupal::config('adobeanalytics.settings')->get("adobeanalytics_js_file_location"), 'The AdobeAnalytics js file was properly referenced.');
    // @FIXME
    // Could not extract the default value because it is either indeterminate, or
    // not scalar. You'll need to provide a default value in
    // config/install/adobeanalytics.settings.yml and config/schema/adobeanalytics.schema.yml.
    $this->assertRaw(\Drupal::config('adobeanalytics.settings')->get("adobeanalytics_image_file_location"), 'The AdobeAnalytics backup image was properly referenced.');
    // @FIXME
    // Could not extract the default value because it is either indeterminate, or
    // not scalar. You'll need to provide a default value in
    // config/install/adobeanalytics.settings.yml and config/schema/adobeanalytics.schema.yml.
    $this->assertRaw(\Drupal::config('adobeanalytics.settings')->get("adobeanalytics_version"), 'The correct AdobeAnalytics version was found.');
  }

  public function assertNoTrackingCode() {
    $this->assertNoRaw("<!-- AdobeAnalytics code version: ", 'The AdobeAnalytics code was not found.');
    // @FIXME
    // Could not extract the default value because it is either indeterminate, or
    // not scalar. You'll need to provide a default value in
    // config/install/adobeanalytics.settings.yml and config/schema/adobeanalytics.schema.yml.
    $this->assertNoRaw(\Drupal::config('adobeanalytics.settings')->get("adobeanalytics_js_file_location"), 'The AdobeAnalytics js file was properly omitted.');
    // @FIXME
    // Could not extract the default value because it is either indeterminate, or
    // not scalar. You'll need to provide a default value in
    // config/install/adobeanalytics.settings.yml and config/schema/adobeanalytics.schema.yml.
    $this->assertNoRaw(\Drupal::config('adobeanalytics.settings')->get("adobeanalytics_image_file_location"), 'The AdobeAnalytics backup image was properly omitted.');
    // @FIXME
    // Could not extract the default value because it is either indeterminate, or
    // not scalar. You'll need to provide a default value in
    // config/install/adobeanalytics.settings.yml and config/schema/adobeanalytics.schema.yml.
    $this->assertNoRaw(\Drupal::config('adobeanalytics.settings')->get("adobeanalytics_version"), 'The AdobeAnalytics version was omitted.');
  }

  public function assertAdobeAnalyticsVar($name, $value, $message = '') {
    $message = empty($message) ? 'The AdobeAnalytics variable was correctly included.' : $message;

    $edit = [
      'adobeanalytics_variables[0][name]' => $name,
      'adobeanalytics_variables[0][value]' => $value,
    ];
    $this->drupalPost('admin/config/system/adobeanalytics', $edit, t('Save configuration'));
    $this->drupalGet('node');
    $this->assertRaw($name . '="' . $value . '";', $message);
  }

  public function assertInvalidAdobeAnalyticsVar($name, $value, $message = '') {
    $message = empty($message) ? 'The AdobeAnalytics variable was correctly reported as invalid.' : $message;
    $edit = [
      'adobeanalytics_variables[0][name]' => $name,
      'adobeanalytics_variables[0][value]' => $value,
    ];
    $this->drupalPost('admin/config/system/adobeanalytics', $edit, t('Save configuration'));
    $this->assertText(t('This is not a valid variable name. It must start with a letter, $ or _ and cannot contain spaces.'), $message);
  }

}
