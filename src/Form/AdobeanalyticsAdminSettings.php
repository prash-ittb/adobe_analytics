<?php

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
   *
   * @return array
   *   Gets the configuration names that will be editable
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
      '#type' => 'details',
      '#title' => $this->t('General settings'),
      '#open' => TRUE,
      '#weight' => '-10',
    ];

    $form['general']['js_file_location'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Complete path to AdobeAnalytics Javascript file'),
      '#default_value' => $config->get('js_file_location'),
    ];

    $form['general']['image_file_location'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Complete path to AdobeAnalytics Image file'),
      '#default_value' => $config->get('image_file_location'),
    ];

    $form['general']['version'] = [
      '#type' => 'textfield',
      '#title' => $this->t('AdobeAnalytics version (used by adobeanalytics for debugging)'),
      '#default_value' => $config->get('version'),
    ];

    $form['general']['token_cache_lifetime'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Token cache lifetime'),
      '#default_value' => $config->get('token_cache_lifetime'),
      '#description' => $this->t(
        'The time, in seconds, that the AdobeAnalytics token
         cache will be valid for. The token cache will always be cleared at the
         next system cron run after this time period, or when this form is saved.'
      ),
    ];

    $form['roles'] = [
      '#type' => 'details',
      '#title' => $this->t('User role tracking'),
      '#open' => TRUE,
      '#description' => $this->t('Define which user roles should, or should not be tracked by AdobeAnalytics.'),
      '#weight' => '-6',
    ];

    $default_value = ($config->get("role_tracking_type")) ? $config->get("role_tracking_type") : 'inclusive';
    $form['roles']['role_tracking_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Add tracking for specific roles'),
      '#options' => [
        'inclusive' => $this->t('Add to the selected roles only'),
        'exclusive' => $this->t('Add to all roles except the ones selected'),
      ],
      '#default_value' => $default_value,
    ];

    $roles = [];
    foreach (user_roles() as $role) {
      $roles[$role->id()] = $role->label();
    }
    $config_track_roles = $config->get('track_roles');

    $form['roles']['track_roles'] = [
      '#type' => 'checkboxes',
      '#options' => $roles,
      '#default_value' => empty($config_track_roles) ?
        array_keys($roles) : $config->get('track_roles'),
    ];

    $form['variables'] = [
      '#type' => 'details',
      '#title' => $this->t('Custom Variables'),
      '#open' => FALSE,
      '#description' => $this->t('You can define tracking variables here.'),
      '#weight' => '-3',
    ];
    $this->adobeAnalyticsExtraVariablesForm($form);

    $form['advanced'] = [
      '#type' => 'details',
      '#title' => $this->t('Advanced'),
      '#description' => $this->t('You can add custom AdobeAnalytics code here.'),
      '#open' => FALSE,
      '#weight' => '-2',
    ];

    $description = 'Example : <br/> - if ([current-date:custom:N] >= 6) { s.prop5
         = "weekend"; }<br/>';
    $description .= '- if ("[current-page:url:path]" == "node") {s.prop9 = "homep
        age";} else {s.prop9 = "[current-page:title]";}';
    $form['advanced']['codesnippet'] = [
      '#type' => 'textarea',
      '#title' => $this->t('JavaScript Code'),
      '#default_value' => $config->get('codesnippet'),
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

    return parent::buildForm($form, $form_state);
  }

  /**
   * Form for getting extra variables.
   */
  public function adobeAnalyticsExtraVariablesForm(&$form) {

    $config = $this->config('adobeanalytics.settings');
    $existing_variables = $config->get('extra_variables');
    $headers = [$this->t('Name'), $this->t('Value')];
    $form['variables']['variables'] = [
      '#type' => 'table',
      '#header' => $headers,
    ];

    $number = 0;
    if (!empty($existing_variables)) {
      foreach ($existing_variables as $key_name => $key_value) {
        $form = $this->adobeAnalyticsExtraVariableInputs(
          $form, $number,
          $key_name, $key_value
        );
        $number++;
      }
    }
    else {
      $form = $this->adobeAnalyticsExtraVariableInputs($form, $number, '', '');
    }

    // Check if the last row empty.
    $total_extra = count($form['variables']['variables']);
    if (!isset($form['variables']['variables'][$total_extra]['name'])
    ) {
      $form = $this->adobeAnalyticsExtraVariableInputs(
        $form, $total_extra + 1,
        '', ''
      );
    }

    $form['variables']['tokens'] = [
      '#theme' => 'token_tree_link',
      '#token_types' => ['node', 'menu', 'term', 'user'],
      '#global_types' => TRUE,
      '#click_insert' => TRUE,
      '#dialog' => TRUE,
    ];
  }

  /**
   * Get inputs in the extra variables form.
   */
  public function adobeAnalyticsExtraVariableInputs($form, $index, $key_name, $key_value) {

    $form['variables']['variables'][$index]['name'] = [
      '#type' => 'textfield',
      '#size' => 40,
      '#maxlength' => 40,
      '#title_display' => 'invisible',
      '#title' => $this->t('Name'),
      '#default_value' => ($key_name != '' ? $key_name : ''),
      '#attributes' => ['class' => ['field-variable-name']],
    ];
    $form['variables']['variables'][$index]['value'] = [
      '#type' => 'textfield',
      '#size' => 40,
      '#maxlength' => 40,
      '#title_display' => 'invisible',
      '#title' => $this->t('Value'),
      '#default_value' => ($key_value != '' ? $key_value : ''),
      '#attributes' => ['class' => ['field-variable-value']],
    ];

    if (empty($key_name) && empty($key_value)) {
      $form['variables']['variables'][$index]['name']['#description'] = $this->t('Example: prop1');
      $form['variables']['variables'][$index]['value']['#description'] = $this->t('Example: [current-page:title]');
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $config = $this->config('adobeanalytics.settings');

    // Save extra variables.
    $extra_vars = [];
    foreach ($form_state->getValue('variables') as $vars) {
      if (!empty($vars['name']) && !empty($vars['value'])) {
        $extra_vars[$vars['name']] = $vars['value'];
      }
    }

    // Save all the config variables.
    $config->set('extra_variables', $extra_vars)
      ->set('js_file_location', $form_state->getValue('js_file_location'))
      ->set('image_file_location', $form_state->getValue('image_file_location'))
      ->set('version', $form_state->getValue('version'))
      ->set('token_cache_lifetime', $form_state->getValue('token_cache_lifetime'))
      ->set('codesnippet', $form_state->getValue('codesnippet'))
      ->set('role_tracking_type', $form_state->getValue('role_tracking_type'))
      ->set('track_roles', $form_state->getValue('track_roles'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
