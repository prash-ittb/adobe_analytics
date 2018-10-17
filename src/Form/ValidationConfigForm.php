<?php

namespace Drupal\adobe_analytics\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ValidationConfigForm.
 */
class ValidationConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'adobe_analytics.validation_config',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'validation_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('adobe_analytics.validation_config');
    $form['cloud_domain_validator'] = [
      '#type' => 'fieldset',
      '#title' => t('Cloud domain validator'),
    ];
    $form['cloud_domain_validator']['cloud_provider'] = [
      '#type' => 'textfield',
      '#title' => t('Provider'),
      '#required' => TRUE,
      '#default_value' => $config->get('cloud_provider'),
    ];
    $form['cloud_domain_validator']['cloud_domain'] = [
      '#type' => 'textfield',
      '#title' => t('Domain'),
      '#required' => TRUE,
      '#default_value' => $config->get('cloud_domain'),
    ];

    $form['tag_manager_validator'] = [
      '#type' => 'fieldset',
      '#title' => t('Tag manager validator'),
      '#required' => TRUE,
    ];
    $form['tag_manager_validator']['tag_manager_provider'] = [
      '#type' => 'textfield',
      '#title' => t('Provider'),
      '#required' => TRUE,
      '#default_value' => $config->get('tag_manager_provider'),
    ];
    $form['tag_manager_validator']['tag_manager_domain'] = [
      '#type' => 'textfield',
      '#title' => t('Domain'),
      '#required' => TRUE,
      '#default_value' => $config->get('tag_manager_domain'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('adobe_analytics.validation_config')
      ->set('cloud_provider', $form_state->getValue('cloud_provider'))
      ->set('cloud_domain', $form_state->getValue('cloud_domain'))
      ->set('tag_manager_provider', $form_state->getValue('tag_manager_provider'))
      ->set('tag_manager_domain', $form_state->getValue('tag_manager_domain'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
