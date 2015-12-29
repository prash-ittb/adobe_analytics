<?php
namespace Drupal\sitecatalyst;

class siteCatalystGeneralTest extends siteCatalystBaseTest {
  /**
   * Implements getInfo().
   */
  public static function getInfo() {
    return array(
      'name' => t('SiteCatalyst General Tests'),
      'description' => t('Tests the general functionality of the SiteCatalyst module.'),
      'group' => t('SiteCatalyst'),
    );
  }

  function testSiteCatalystTrackingCode() {
    $this->drupalGet('<front>');
    $this->assertTrackingCode();
  }

  function testSiteCatalystVariables() {
    // Test that variables with valid names are added properly.
    $valid_vars = array(
      $this->randomName(8),
      $this->randomName(8) . '7',
      '$' . $this->randomName(8),
      '_' . $this->randomName(8),
    );
    foreach ($valid_vars as $name) {
      $this->assertSiteCatalystVar($name, $this->randomName(8));
    }

    // Test that invalid variable names are not allowed.
    $invalid_vars = array(
      '7' . $this->randomName(8),
      $this->randomName(8) . ' ' . $this->randomName(8),
      '#' . $this->randomName(8),
    );
    foreach ($invalid_vars as $name) {
      $this->assertInvalidSiteCatalystVar($name, $this->randomName(8));
    }
  }

  function testSiteCatalystRolesTracking() {
    \Drupal::configFactory()->getEditable('sitecatalyst.settings')->set('sitecatalyst_track_authenticated_user', 1)->save();
    \Drupal::configFactory()->getEditable('sitecatalyst.settings')->set('sitecatalyst_role_tracking_type', 'inclusive')->save();

    $this->drupalGet('<front>');
    $this->assertTrackingCode();

    \Drupal::configFactory()->getEditable('sitecatalyst.settings')->set('sitecatalyst_role_tracking_type', 'exclusive')->save();
    $this->drupalGet('<front>');
    $this->assertNoTrackingCode();
  }
}
