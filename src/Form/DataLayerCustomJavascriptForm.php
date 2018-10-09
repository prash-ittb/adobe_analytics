<?php

namespace Drupal\adobe_analytics\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class DataLayerCustomJavascriptForm.
 */
class DataLayerCustomJavascriptForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'adobe_analytics.data_layer_custom_javascript',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'data_layer_custom_javascript_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('adobe_analytics.data_layer_custom_javascript');

    $form['data_layer_custom_javascript'] = [
      '#required' => TRUE,
      '#description' => t('Enter the path of a JS file on amazon s3. It will be placed below JSON object in footer, Use jQuery.extend to add custom elements in existing json. e.g jQuery(document).ready(function() { window.segment_str = window.location.pathname; jQuery.extend(pfAnalyticsData, { "webinar": { "webinarID": window.segment_str, } });});'),
      '#weight' => '0',
      '#maxlength' => 500,
      '#type' => 'textfield',
      '#title' => t('Data layer custom Javascript file path'),
      '#default_value' => $config->get('data_layer_custom_javascript'),
    ];
    return parent::buildForm($form, $form_state);
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('adobe_analytics.validation_config');
    if (!strstr($form_state->getValue('data_layer_custom_javascript'), $config->get('cloud_domain'))) {
      $form_state->setErrorByName('data_layer_custom_javascript', "Scripts can 
          only be hosted at authorized locations, such as " . $config->get('cloud_provider') . " e.g " . $config->get('cloud_domain_validator') . " or on "
        . $config->get('tag_manager_provider') . " e.g " . $config->get('tag_manager_domain') . ". Please correct the path 
            or request assistance to authorize your domain.");
    }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('adobe_analytics.data_layer_custom_javascript')
      ->set('data_layer_custom_javascript', $form_state->getValue('data_layer_custom_javascript'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
