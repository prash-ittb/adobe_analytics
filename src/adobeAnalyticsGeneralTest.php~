<?php
namespace Drupal\adobeanalytics;

class adobeAnalyticsGeneralTest extends adobeAnalyticsBaseTest {
  /**
   * Implements getInfo().
   */
  public static function getInfo() {
    return array(
      'name' => t('AdobeAnalytics General Tests'),
      'description' => t('Tests the general functionality of the AdobeAnalytics module.'),
      'group' => t('AdobeAnalytics'),
    );
  }

  function testAdobeAnalyticsTrackingCode() {
    $this->drupalGet('<front>');
    $this->assertTrackingCode();
  }

  function testAdobeAnalyticsVariables() {
    // Test that variables with valid names are added properly.
    $valid_vars = array(
      $this->randomName(8),
      $this->randomName(8) . '7',
      '$' . $this->randomName(8),
      '_' . $this->randomName(8),
    );
    foreach ($valid_vars as $name) {
      $this->assertAdobeAnalyticsVar($name, $this->randomName(8));
    }

    // Test that invalid variable names are not allowed.
    $invalid_vars = array(
      '7' . $this->randomName(8),
      $this->randomName(8) . ' ' . $this->randomName(8),
      '#' . $this->randomName(8),
    );
    foreach ($invalid_vars as $name) {
      $this->assertInvalidAdobeAnalyticsVar($name, $this->randomName(8));
    }
  }

  function testAdobeAnalyticsRolesTracking() {
    \Drupal::configFactory()->getEditable('adobeanalytics.settings')->set('adobeanalytics_track_authenticated_user', 1)->save();
    \Drupal::configFactory()->getEditable('adobeanalytics.settings')->set('adobeanalytics_role_tracking_type', 'inclusive')->save();

    $this->drupalGet('<front>');
    $this->assertTrackingCode();

    \Drupal::configFactory()->getEditable('adobeanalytics.settings')->set('adobeanalytics_role_tracking_type', 'exclusive')->save();
    $this->drupalGet('<front>');
    $this->assertNoTrackingCode();
  }
}
