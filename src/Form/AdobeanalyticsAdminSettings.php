<?php

/**
 * @file
 * Contains \Drupal\adobeanalytics\Form\AdobeanalyticsAdminSettings.
 */

namespace Drupal\adobeanalytics\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Build the configuration form.
 */
class AdobeanalyticsAdminSettings extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'adobeanalytics_content';
  }

  /**
   * Get Editable configuratons.
   */
  protected function getEditableConfigNames() {
    return ['adobeanalytics.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('adobeanalytics.settings');

    $form['general'] = [
      // Fieldset changed to details in drupal 8.
      '#type' => 'details',
      '#title' => t('General settings'),
      '#open' => TRUE,
      '#weight' => '-10',
    ];

    $form['general']['adobeanalytics_js_file_location'] = [
      '#type' => 'textfield',
      '#title' => t("Complete path to AdobeAnalytics Javascript file"),
      '#default_value' => $config->get("adobeanalytics_js_file_location"),
    ];

    $form['general']['adobeanalytics_image_file_location'] = [
      '#type' => 'textfield',
      '#title' => t("Complete path to AdobeAnalytics Image file"),
      '#default_value' => $config->get("adobeanalytics_image_file_location"),
    ];

    $form['general']['adobeanalytics_version'] = [
      '#type' => 'textfield',
      '#title' => t("AdobeAnalytics version (used by adobeanalytics for debugging)"),
      '#default_value' => $config->get("adobeanalytics_version"),
    ];

    $form['general']['adobeanalytics_token_cache_lifetime'] = [
      '#type' => 'textfield',
      '#title' => t("Token cache lifetime"),
      '#default_value' => $config->get("adobeanalytics_token_cache_lifetime"),
      '#description' => t('The time, in seconds, that the AdobeAnalytics token cache will be valid for. The token cache will always be cleared at the next system cron run after this time period, or when this form is saved.'),
    ];

    $form['roles'] = [
      '#type' => 'details',
      '#title' => t('User role tracking'),
      '#open' => TURE,
      '#description' => t('Define which user roles should, or should not be tracked by AdobeAnalytics.'),
      '#weight' => '-6',
    ];

    $form['roles']['adobeanalytics_role_tracking_type'] = [
      '#type' => 'select',
      '#title' => t('Add tracking for specific roles'),
      '#options' => [
        'inclusive' => t('Add to the selected roles only'),
        'exclusive' => t('Add to all roles except the ones selected'),
      ],
      '#default_value' => $config->get("adobeanalytics_role_tracking_type", 'inclusive'),
    ];

    $roles = array();
    foreach (user_roles() as $role) {
      $roles[$role->id()] = $role->label();
    }
    $form['roles']["adobeanalytics_track_roles"] = [
      '#type' => 'checkboxes',
      '#options' => $roles,
      '#default_value' => $config->get("adobeanalytics_track_roles"),
    ];

    $form['variables'] = [
      '#type' => 'details',
      '#title' => t('Custom Variables'),
      '#open' => FALSE,
      '#description' => t('You can define tracking variables here.'),
      '#weight' => '-3',
    ];
    $this->adobeAnalyticsExtraVariablesForm($form);

    $form['advanced'] = [
      '#type' => 'details',
      '#title' => t('Advanced'),
      '#description' => t('You can add custom AdobeAnalytics code here.'),
      '#open' => FALSE,
      '#weight' => '-2',
    ];

    $description = 'Example : <br/> - if ([current-date:custom:N] >= 6) { s.prop5 = "weekend"; }<br/>';
    $description .= '- if ("[current-page:url:path]" == "node") {s.prop9 = "homepage";} else {s.prop9 = "[current-page:title]";}';
    $form['advanced']['adobeanalytics_codesnippet'] = [
      '#type' => 'textarea',
      '#title' => t('JavaScript Code'),
      '#default_value' => $config->get('adobeanalytics_codesnippet'),
      '#rows' => 15,
      '#description' => $description,
    ];

    $form['advanced']['tokens'] = [
      '#theme' => 'token_tree_link',
      '#token_types' => ['node', 'menu', 'term', 'user'],
      '#global_types' => TRUE,
      '#click_insert' => TRUE,
      '#dialog' => TRUE,
    ];
    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => 'Save configuration',
    );

    return $form;
  }


  /**
   * Get the extra variable form with some conditions.
   */
  public function adobeAnalyticsExtraVariablesForm(&$form) {
    $config = $this->config('adobeanalytics.settings');
    $existing_variables = $config->get('adobeanalytics_extra_variables');
    // $existing_variables = unserialize($extra_variables);
    $headers = array(t('Name'), t('Value'));
    $form['variables']['adobeanalytics_variables'] = [
      '#type' => 'table',
      '#header' => $headers,
    ];

    $number = 0;
    if (!empty($existing_variables)) {
      foreach ($existing_variables as $key_name => $key_value) {
        $form = $this->adobeAnalyticsExtraVariableInputs($form, $number, $key_name, $key_value);
        $number++;
      }
    }
    else {
      $form = $this->adobeAnalyticsExtraVariableInputs($form, $number, '', '');
    }

    // Check if the last row empty.
    $total_extra = count($form['variables']['adobeanalytics_variables']);
    if (!isset($form['variables']['adobeanalytics_variables'][$total_extra]['name'])) {
      $form = $this->adobeAnalyticsExtraVariableInputs($form, $total_extra + 1, '', '');
    }

    $form['variables']['tokens'] = [
      '#theme' => 'token_tree_link',
      '#token_types' => array('node', 'menu', 'term', 'user'),
      '#global_types' => TRUE,
      '#click_insert' => TRUE,
      '#dialog' => TRUE,
    ];
  }

  /**
   * Get the extra variable form inputs.
   */
  public function adobeAnalyticsExtraVariableInputs($form, $index, $key_name, $key_value) {
    $form['variables']['adobeanalytics_variables'][$index]['name'] = [
      '#type' => 'textfield',
      '#size' => 40,
      '#maxlength' => 40,
      '#title_display' => 'invisible',
      '#title' => t('Name'),
      '#default_value' => ($key_name != '' ? $key_name : ''),
      '#attributes' => ['class' => ['field-variable-name']],
    ];
    $form['variables']['adobeanalytics_variables'][$index]['value'] = [
      '#type' => 'textfield',
      '#size' => 40,
      '#maxlength' => 40,
      '#title_display' => 'invisible',
      '#title' => t('Value'),
      '#default_value' => ($key_value != '' ? $key_value : ''),
      '#attributes' => ['class' => ['field-variable-value']],
    ];

    if (empty($key_name) && empty($key_value)) {
      $form['variables']['adobeanalytics_variables'][$index]['name']['#description'] = t('Example: prop1');
      $form['variables']['adobeanalytics_variables'][$index]['value']['#description'] = t('Example: [current-page:title]');
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('adobeanalytics.settings');

    // Save extra variables.
    $extra_vars = array();
    foreach ($form_state->getValue('adobeanalytics_variables') as $vars) {
      if (!empty($vars['name']) && !empty($vars['value'])) {
        $extra_vars[$vars['name']] = $vars['value'];
      }
    }
    // Save other variables.
    $config->set('adobeanalytics_extra_variables', $extra_vars)->save();
    $config->set('adobeanalytics_js_file_location', $form_state->getValue('adobeanalytics_js_file_location'))->save();
    $config->set('adobeanalytics_image_file_location', $form_state->getValue('adobeanalytics_image_file_location'))->save();
    $config->set('adobeanalytics_version', $form_state->getValue('adobeanalytics_version'))->save();
    $config->set('adobeanalytics_token_cache_lifetime', $form_state->getValue('adobeanalytics_token_cache_lifetime'))->save();
    $config->set('adobeanalytics_codesnippet', $form_state->getValue('adobeanalytics_codesnippet'))->save();
    $config->set('adobeanalytics_role_tracking_type', $form_state->getValue('adobeanalytics_role_tracking_type'))->save();
    $config->set('adobeanalytics_track_roles', $form_state->getValue('adobeanalytics_track_roles'))->save();
  }

}
