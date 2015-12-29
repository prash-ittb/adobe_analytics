<?php
namespace Drupal\sitecatalyst;

/**
 * @file
 * Test file for the SiteCatalyst module.
 */
class siteCatalystBaseTest extends \Drupal\simpletest\WebTestBase {

  protected $profile = 'standard';

  public /**
   * Implementation of setUp().
   */
  function setUp() {
    // Enable a couple modules.
    parent::setUp('sitecatalyst');
    menu_rebuild();

    // Create an admin user with all the permissions needed to run tests.
    $this->admin_user = $this->drupalCreateUser([
      'administer SiteCatalyst configuration',
      'access administration pages',
    ]);
    $this->drupalLogin($this->admin_user);

    // Set some default settings.
    \Drupal::configFactory()->getEditable('sitecatalyst.settings')->set("sitecatalyst_js_file_location", 'http://www.example.com/js/s_code_remote_h.js')->save();
    \Drupal::configFactory()->getEditable('sitecatalyst.settings')->set("sitecatalyst_image_file_location", 'http://examplecom.112.2O7.net/b/ss/examplecom/1/H.20.3--NS/0')->save();
    \Drupal::configFactory()->getEditable('sitecatalyst.settings')->set("sitecatalyst_version", 'H.20.3.')->save();
  }

  public function assertTrackingCode() {
    $this->assertRaw("<!-- SiteCatalyst code version: ", 'The SiteCatalyst code was found.');
    // @FIXME
    // Could not extract the default value because it is either indeterminate, or
    // not scalar. You'll need to provide a default value in
    // config/install/sitecatalyst.settings.yml and config/schema/sitecatalyst.schema.yml.
    $this->assertRaw(\Drupal::config('sitecatalyst.settings')->get("sitecatalyst_js_file_location"), 'The SiteCatalyst js file was properly referenced.');
    // @FIXME
    // Could not extract the default value because it is either indeterminate, or
    // not scalar. You'll need to provide a default value in
    // config/install/sitecatalyst.settings.yml and config/schema/sitecatalyst.schema.yml.
    $this->assertRaw(\Drupal::config('sitecatalyst.settings')->get("sitecatalyst_image_file_location"), 'The SiteCatalyst backup image was properly referenced.');
    // @FIXME
    // Could not extract the default value because it is either indeterminate, or
    // not scalar. You'll need to provide a default value in
    // config/install/sitecatalyst.settings.yml and config/schema/sitecatalyst.schema.yml.
    $this->assertRaw(\Drupal::config('sitecatalyst.settings')->get("sitecatalyst_version"), 'The correct SiteCatalyst version was found.');
  }

  public function assertNoTrackingCode() {
    $this->assertNoRaw("<!-- SiteCatalyst code version: ", 'The SiteCatalyst code was not found.');
    // @FIXME
    // Could not extract the default value because it is either indeterminate, or
    // not scalar. You'll need to provide a default value in
    // config/install/sitecatalyst.settings.yml and config/schema/sitecatalyst.schema.yml.
    $this->assertNoRaw(\Drupal::config('sitecatalyst.settings')->get("sitecatalyst_js_file_location"), 'The SiteCatalyst js file was properly omitted.');
    // @FIXME
    // Could not extract the default value because it is either indeterminate, or
    // not scalar. You'll need to provide a default value in
    // config/install/sitecatalyst.settings.yml and config/schema/sitecatalyst.schema.yml.
    $this->assertNoRaw(\Drupal::config('sitecatalyst.settings')->get("sitecatalyst_image_file_location"), 'The SiteCatalyst backup image was properly omitted.');
    // @FIXME
    // Could not extract the default value because it is either indeterminate, or
    // not scalar. You'll need to provide a default value in
    // config/install/sitecatalyst.settings.yml and config/schema/sitecatalyst.schema.yml.
    $this->assertNoRaw(\Drupal::config('sitecatalyst.settings')->get("sitecatalyst_version"), 'The SiteCatalyst version was omitted.');
  }

  public function assertSiteCatalystVar($name, $value, $message = '') {
    $message = empty($message) ? 'The SiteCatalyst variable was correctly included.' : $message;

    $edit = [
      'sitecatalyst_variables[0][name]' => $name,
      'sitecatalyst_variables[0][value]' => $value,
    ];
    $this->drupalPost('admin/config/system/sitecatalyst', $edit, t('Save configuration'));
    $this->drupalGet('node');
    $this->assertRaw($name . '="' . $value . '";', $message);
  }

  public function assertInvalidSiteCatalystVar($name, $value, $message = '') {
    $message = empty($message) ? 'The SiteCalalyst variable was correctly reported as invalid.' : $message;
    $edit = [
      'sitecatalyst_variables[0][name]' => $name,
      'sitecatalyst_variables[0][value]' => $value,
    ];
    $this->drupalPost('admin/config/system/sitecatalyst', $edit, t('Save configuration'));
    $this->assertText(t('This is not a valid variable name. It must start with a letter, $ or _ and cannot contain spaces.'), $message);
  }

}
