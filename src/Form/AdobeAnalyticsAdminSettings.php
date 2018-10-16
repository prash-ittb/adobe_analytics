<?php

namespace Drupal\adobe_analytics\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;

/**
 * Build the configuration form.
 */
class AdobeAnalyticsAdminSettings extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {

    return 'adobe_analytics_settings';
  }

  /**
   * Get Editable configuratons.
   *
   * @return array
   *   Gets the configuration names that will be editable
   */
  protected function getEditableConfigNames() {

    return ['adobe_analytics.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $config = $this->config('adobe_analytics.settings');

    $form['installation_mode'] = [
      '#type' => 'select',
      '#title' => $this->t('Installation mode'),
      '#default_value' => $config->get('installation_mode') ? $config->get('installation_mode') : 'general',
      '#options' => [
        'general' => $this->t('Basic'),
        'cdn' => $this->t('CDN'),
      ],
      '#weight' => '-100',
    ];
    // CDN form elements.
    $form['cdn_install_type'] = [
      '#type' => 'radios',
      '#title' => t('Installation Type'),
      '#required' => '1',
      '#default_value' => $config->get('cdn_install_type') ? $config->get('cdn_install_type') : 'amazon',
      '#options' => [
        'amazon' => t('Amazon S3 hosted'),
        'tag' => t('Tag Manager Tool'),
      ],
      '#weight' => '-10',
      '#states' => [
        'visible' => [
          ':input[name="installation_mode"]' => ['value' => 'cdn'],
        ],
      ],
    ];

    $form['amazon_s_code_config'] = [
      '#weight' => '-9',
      '#description' => t('Enter the Amazon S3 hosted S code Configuration Path (s_code_config.js) for development and production environments.'),
      '#type' => 'fieldset',
      '#title' => $this->t('Adobe analytics S code Configuration Path'),
      '#collapsible' => '1',
      '#collapsed' => '0',
      '#states' => [
        'visible' => [
          ':input[name="installation_mode"]' => ['value' => 'cdn'],
          'input[name="cdn_install_type"]' => ['value' => 'amazon'],
        ],
      ],
    ];
    $form['amazon_s_code_config']['development_s_code_config'] = [
      '#description' => t('Enter your development Adobe analytics tracking S code configuration path (s_code_config.js).'),
      '#weight' => '0',
      '#type' => 'textfield',
      '#maxlength' => 500,
      '#title' => t('Development'),
      '#default_value' => $config->get('development_s_code_config'),
      '#states' => [
        'required' => [
          'input[name="cdn_install_type"]' => ['value' => 'amazon'],
        ],
      ],
    ];
    $form['amazon_s_code_config']['production_s_code_config'] = [
      '#description' => t('Enter your production Adobe analytics tracking S code configuration path (s_code_config.js).'),
      '#weight' => '1',
      '#type' => 'textfield',
      '#maxlength' => 500,
      '#title' => t('Production'),
      '#default_value' => $config->get('production_s_code_config'),
      '#states' => [
        'required' => [
          'input[name="cdn_install_type"]' => ['value' => 'amazon'],
        ],
      ],
    ];

    $form['amazon_s_code'] = [
      '#weight' => '-8',
      '#description' => t('Enter the Amazon S3 hosted S code Path (s_code.js) for development and production environments.'),
      '#type' => 'fieldset',
      '#title' => t('Adobe analytics S code Path'),
      '#collapsible' => '1',
      '#collapsed' => '0',
      '#states' => [
        'visible' => [
          ':input[name="installation_mode"]' => ['value' => 'cdn'],
          'input[name="cdn_install_type"]' => ['value' => 'amazon'],
        ],
      ],
    ];
    $form['amazon_s_code']['development_s_code'] = [
      '#description' => t('Enter your development Adobe analytics tracking S code path (s_code.js).'),
      '#weight' => '0',
      '#type' => 'textfield',
      '#maxlength' => 500,
      '#title' => t('Development'),
      '#default_value' => $config->get('development_s_code'),
      '#states' => [
        'required' => [
          'input[name="cdn_install_type"]' => ['value' => 'amazon'],
        ],
      ],
    ];
    $form['amazon_s_code']['production_s_code'] = [
      '#description' => t('Enter your production Adobe analytics tracking S code path (s_code.js).'),
      '#weight' => '3',
      '#type' => 'textfield',
      '#maxlength' => 500,
      '#title' => t('Production'),
      '#default_value' => $config->get('production_s_code'),
      '#states' => [
        'required' => [
          'input[name="cdn_install_type"]' => ['value' => 'amazon'],
        ],
      ],
    ];

    $form['amazon_footer_code'] = [
      '#weight' => '-7',
      '#type' => 'fieldset',
      '#title' => t('Footer Code'),
      '#collapsible' => '1',
      '#collapsed' => '0',
      '#states' => [
        'visible' => [
          ':input[name="installation_mode"]' => ['value' => 'cdn'],
          'input[name="cdn_install_type"]' => ['value' => 'amazon'],
        ],
      ],
    ];
    $form['amazon_footer_code']['development_footer_js_code'] = [
      '#description' => t('Enter the path of footer code JS file on Amazon S3.'),
      '#weight' => '0',
      '#title' => t('Development'),
      '#type' => 'textfield',
      '#maxlength' => 500,
      '#default_value' => $config->get('development_footer_js_code'),
      '#states' => [
        'required' => [
          'input[name="cdn_install_type"]' => ['value' => 'amazon'],
        ],
      ],
    ];
    $form['amazon_footer_code']['production_footer_js_code'] = [
      '#description' => t('Enter the path of footer code JS file on Amazon S3.'),
      '#weight' => '0',
      '#title' => t('Production'),
      '#type' => 'textfield',
      '#maxlength' => 500,
      '#default_value' => $config->get('production_footer_js_code'),
      '#states' => [
        'required' => [
          'input[name="cdn_install_type"]' => ['value' => 'amazon'],
        ],
      ],
    ];

    $form['amazon_custom_tracking'] = [
      '#weight' => '-6',
      '#type' => 'fieldset',
      '#title' => t('Adobe analytics custom tracking Javascript'),
      '#collapsible' => '1',
      '#collapsed' => '0',
      '#states' => [
        'visible' => [
          ':input[name="installation_mode"]' => ['value' => 'cdn'],
          'input[name="cdn_install_type"]' => ['value' => 'amazon'],
        ],
      ],
    ];
    $form['amazon_custom_tracking']['development_amazon_custom_tracking'] = [
      '#weight' => '-6',
      '#type' => 'fieldset',
      '#title' => t('Development'),
      '#collapsible' => '1',
      '#collapsed' => '0',
      '#states' => [
        'visible' => [
          ':input[name="installation_mode"]' => ['value' => 'cdn'],
          'input[name="cdn_install_type"]' => ['value' => 'amazon'],
        ],
      ],
    ];
    $form['amazon_custom_tracking']['development_amazon_custom_tracking']['development_cdn_custom_tracking_js_before'] = [
      '#required' => '0',
      '#description' => t('Enter the path for custom JS file on Amazon S3. This JS will be added to all pages just before page view call i.e. s(t).'),
      '#weight' => '0',
      '#type' => 'textfield',
      '#maxlength' => 500,
      '#title' => t('Custom tracking javascript path (loaded before s.t())'),
      '#default_value' => $config->get('development_cdn_custom_tracking_js_before'),
    ];
    $form['amazon_custom_tracking']['development_amazon_custom_tracking']['development_cdn_custom_tracking_js_after'] = [
      '#required' => '0',
      '#description' => t('Enter the path gor custom JS file on Amazon S3. This JS will be added to all pages just after page view call i.e. s(t).'),
      '#weight' => '1',
      '#type' => 'textfield',
      '#maxlength' => 500,
      '#title' => t('Custom tracking javascript path (loaded after s.t())'),
      '#default_value' => $config->get('development_cdn_custom_tracking_js_after'),
    ];
    $form['amazon_custom_tracking']['production_amazon_custom_tracking'] = [
      '#weight' => '-6',
      '#type' => 'fieldset',
      '#title' => t('Production'),
      '#collapsible' => '1',
      '#collapsed' => '0',
      '#states' => [
        'visible' => [
          ':input[name="installation_mode"]' => ['value' => 'cdn'],
          'input[name="cdn_install_type"]' => ['value' => 'amazon'],
        ],
      ],
    ];
    $form['amazon_custom_tracking']['production_amazon_custom_tracking']['production_cdn_custom_tracking_js_before'] = [
      '#required' => '0',
      '#description' => t('Enter the path for custom JS file on Amazon S3. This JS will be added to all pages just before page view call i.e. s(t).'),
      '#weight' => '0',
      '#type' => 'textfield',
      '#maxlength' => 500,
      '#title' => t('Custom tracking javascript path (loaded before s.t())'),
      '#default_value' => $config->get('production_cdn_custom_tracking_js_before'),
    ];
    $form['amazon_custom_tracking']['production_amazon_custom_tracking']['production_cdn_custom_tracking_js_after'] = [
      '#required' => '0',
      '#description' => t('Enter the path gor custom JS file on Amazon S3. This JS will be added to all pages just after page view call i.e. s(t).'),
      '#weight' => '1',
      '#type' => 'textfield',
      '#maxlength' => 500,
      '#title' => t('Custom tracking javascript path (loaded after s.t())'),
      '#default_value' => $config->get('production_cdn_custom_tracking_js_after'),
    ];

    $form['tag_manager_container_path'] = [
      '#weight' => '-5',
      '#type' => 'fieldset',
      '#title' => t('Tag manager tool container path'),
      '#collapsible' => '1',
      '#collapsed' => '0',
      '#states' => [
        'visible' => [
          ':input[name="installation_mode"]' => ['value' => 'cdn'],
          'input[name="cdn_install_type"]' => ['value' => 'tag'],
        ],
      ],
    ];
    $form['tag_manager_container_path']['development_tag_manager_container_path'] = [
      '#description' => t('Enter your development tag manager tool container path.'),
      '#weight' => '0',
      '#type' => 'textfield',
      '#maxlength' => 500,
      '#title' => t('Development'),
      '#default_value' => $config->get('development_tag_manager_container_path'),
      '#states' => [
        'required' => [
          'input[name="cdn_install_type"]' => ['value' => 'tag'],
        ],
      ],
    ];
    $form['tag_manager_container_path']['production_tag_manager_container_path'] = [
      '#description' => t('Enter your production tag manager tool container path.'),
      '#weight' => '1',
      '#type' => 'textfield',
      '#maxlength' => 500,
      '#title' => t('Production'),
      '#default_value' => $config->get('production_tag_manager_container_path'),
      '#states' => [
        'required' => [
          'input[name="cdn_install_type"]' => ['value' => 'tag'],
        ],
      ],
    ];
    $form['tag_manager_footer_code'] = [
      '#weight' => '-4',
      '#type' => 'fieldset',
      '#title' => t('Footer Code'),
      '#collapsible' => '1',
      '#collapsed' => '0',
      '#states' => [
        'visible' => [
          ':input[name="installation_mode"]' => ['value' => 'cdn'],
          'input[name="cdn_install_type"]' => ['value' => 'tag'],
        ],
      ],
    ];
    $form['tag_manager_footer_code']['development_tag_manager_footer_js'] = [
      '#required' => '0',
      '#description' => t('Enter the path of footer code JS file on Amazon S3.'),
      '#weight' => '0',
      '#type' => 'textfield',
      '#title' => t('Development'),
      '#maxlength' => 500,
      '#default_value' => $config->get('development_tag_manager_footer_js'),
    ];
    $form['tag_manager_footer_code']['production_tag_manager_footer_js'] = [
      '#required' => '0',
      '#description' => t('Enter the path of footer code JS file on Amazon S3.'),
      '#weight' => '0',
      '#type' => 'textfield',
      '#title' => t('Production'),
      '#maxlength' => 500,
      '#default_value' => $config->get('production_tag_manager_footer_js'),
    ];
    // General form elements.
    $form['general_warning'] = [
      '#type' => 'item',
      '#markup' => "<div class='messages messages--warning'>" . $this->t("Please use CDN Installation Mode for Analytics setup. Do not use Basic Installation mode, it is provided for backwards compatibility only.") . "</div>",
      '#weight' => '-11',
      '#states' => [
        'visible' => [
          ':input[name="installation_mode"]' => ['value' => 'general'],
        ],
      ],
    ];
    $form['general'] = [
      '#type' => 'details',
      '#title' => $this->t('General settings'),
      '#open' => TRUE,
      '#weight' => '-10',
      '#states' => [
        'visible' => [
          ':input[name="installation_mode"]' => ['value' => 'general'],
        ],
      ],
    ];

    $form['general']['js_file_location'] = [
      '#type' => 'textfield',
      '#maxlength' => 500,
      '#title' => $this->t('Complete path to AdobeAnalytics Javascript file'),
      '#default_value' => $config->get('js_file_location'),
    ];

    $form['general']['image_file_location'] = [
      '#type' => 'textfield',
      '#maxlength' => 500,
      '#title' => $this->t('Complete path to AdobeAnalytics Image file'),
      '#default_value' => $config->get('image_file_location'),
    ];

    $form['general']['version'] = [
      '#type' => 'textfield',
      '#title' => $this->t('AdobeAnalytics version (used by adobe_analytics for debugging)'),
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

    $form['variables'] = [
      '#type' => 'details',
      '#title' => $this->t('Custom Variables'),
      '#open' => FALSE,
      '#description' => $this->t('You can define tracking variables here.'),
      '#weight' => '-3',
      '#prefix' => '<div id="variables-details-wrapper">',
      '#suffix' => '</div>',
      '#states' => [
        'visible' => [
          ':input[name="installation_mode"]' => ['value' => 'general'],
        ],
      ],
    ];
    $this->adobeAnalyticsExtraVariablesForm($form, $form_state);

    $form['variables']['actions'] = [
      '#type' => 'actions',
    ];
    $form['variables']['actions']['add_variable'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add variable'),
      '#submit' => ['::addVariable'],
      '#ajax' => [
        'callback' => '::addVariableCallback',
        'wrapper' => 'variables-details-wrapper',
      ],
    ];

    $form['advanced'] = [
      '#type' => 'details',
      '#title' => $this->t('Advanced'),
      '#description' => $this->t('You can add custom AdobeAnalytics code here.'),
      '#open' => FALSE,
      '#weight' => '-2',
      '#states' => [
        'visible' => [
          ':input[name="installation_mode"]' => ['value' => 'general'],
        ],
      ],
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

    $form['roles'] = [
      '#type' => 'details',
      '#title' => $this->t('User role tracking'),
      '#open' => TRUE,
      '#description' => $this->t('Define which user roles should, or should not be tracked by AdobeAnalytics.'),
      '#weight' => '0',
    ];

    $default_value = ($config->get("role_tracking_type")) ? $config->get("role_tracking_type") : 'inclusive';
    $form['roles']['role_tracking_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Add tracking for specific roles'),
      '#options' => [
        'exclusive' => $this->t('Add to all roles except the ones selected'),
        'inclusive' => $this->t('Add to the selected roles only'),
      ],
      '#default_value' => $default_value,
    ];

    $roles = [];
    foreach (user_roles() as $role) {
      $roles[$role->id()] = $role->label();
    }

    $form['roles']['track_roles'] = [
      '#type' => 'checkboxes',
      '#options' => $roles,
      '#default_value' => $config->get('track_roles'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * Form for getting extra variables.
   */
  public function adobeAnalyticsExtraVariablesForm(&$form, FormStateInterface $form_state) {

    $config = $this->config('adobe_analytics.settings');
    $existing_vars = $config->get('extra_variables');

    if (empty($existing_vars)) {
      $existing_vars = [];
    }

    $values = $form_state->get('variables');
    $existing_variables = isset($values) ? $values : $existing_vars;

    $headers = [$this->t('Name'), $this->t('Value')];

    $form['variables']['variables'] = [
      '#type' => 'table',
      '#header' => $headers,
    ];

    foreach ($existing_variables as $key => $data) {
      $form = $this->adobeAnalyticsExtraVariableInputs($form, $key, $data);
    }

    // Always add a blank line at the end.
    $form = $this->adobeAnalyticsExtraVariableInputs($form, count($existing_variables));

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
  public function adobeAnalyticsExtraVariableInputs($form, $index, $data = []) {

    $form['variables']['variables'][$index]['name'] = [
      '#type' => 'textfield',
      '#size' => 40,
      '#maxlength' => 40,
      '#title_display' => 'invisible',
      '#title' => $this->t('Name'),
      '#default_value' => isset($data['name']) ? $data['name'] : '',
      '#attributes' => ['class' => ['field-variable-name']],
      '#element_validate' => [[$this, 'validateVariableName']],
    ];
    $form['variables']['variables'][$index]['value'] = [
      '#type' => 'textfield',
      '#size' => 40,
      '#maxlength' => 40,
      '#title_display' => 'invisible',
      '#title' => $this->t('Value'),
      '#default_value' => isset($data['value']) ? $data['value'] : '',
      '#attributes' => ['class' => ['field-variable-value']],
    ];

    if (empty($data)) {
      $form['variables']['variables'][$index]['name']['#description'] = $this->t('Example: prop1');
      $form['variables']['variables'][$index]['value']['#description'] = $this->t('Example: [current-page:title]');
    }
    return $form;
  }

  /**
   * Callback for both ajax-enabled buttons.
   *
   * Selects and returns the fieldset with the names in it.
   */
  public function addVariableCallback(array &$form, FormStateInterface $form_state) {

    // Leave the fieldset open.
    $form['variables']['#open'] = TRUE;
    return $form['variables'];
  }

  /**
   * Submit handler for the "add-one-more" button.
   *
   * Increments the max counter and causes a rebuild.
   */
  public function addVariable(array &$form, FormStateInterface $form_state) {

    $input = $form_state->getUserInput();
    $form_state->set('variables', $input['variables']);
    $form_state->setRebuild();
  }

  /**
   * Element validate callback to ensure that variable names are valid.
   *
   * @param array $element
   *   An associative array containing the properties and children of the
   *   generic form element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param array $complete_form
   *   The complete form structure.
   */
  public function validateVariableName(array &$element, FormStateInterface $form_state, array &$complete_form) {
    $variable_name = $element['#value'];

    // Variable names must follow the rules defined by javascript syntax.
    if (!empty($variable_name) && !preg_match("/^[A-Za-z_$]{1}\S*$/", $variable_name)) {
      $form_state->setError($element, $this->t('This is not a valid variable name. It must start with a letter, $ or _ and cannot contain spaces.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('adobe_analytics.validation_config');
    $cloud_fields = [
      'development_s_code_config',
      'production_s_code_config',
      'development_s_code',
      'production_s_code',
      'development_footer_js_code',
      'production_footer_js_code',
      'development_cdn_custom_tracking_js_before',
      'development_cdn_custom_tracking_js_after',
      'production_cdn_custom_tracking_js_before',
      'production_cdn_custom_tracking_js_after',
      'development_tag_manager_footer_js',
      'production_tag_manager_footer_js',
    ];

    $tag_manager_fields = [
      'development_tag_manager_container_path',
      'production_tag_manager_container_path',
    ];

    if ($form_state->getValue('installation_mode') == 'cdn') {
      foreach ($cloud_fields as $field) {
        if (!$config->get('cloud_domain') || empty($config->get('cloud_domain'))) {
          $form_state->setErrorByName($field, t("No validation criteria found. Please go to %link to set a validation criteria for the fields.", [
            '%link' => Link::createFromRoute('Validation settings', 'adobe_analytics.validation_config_form')
              ->toString()
          ]));
        }
        elseif ($form_state->getValue($field) && !strstr($form_state->getValue($field), $config->get('cloud_domain'))) {
          $form_state->setErrorByName($field, "Scripts can 
          only be hosted at authorized locations, such as " . $config->get('cloud_provider') . " e.g " . $config->get('cloud_domain') . " or on "
            . $config->get('tag_manager_provider') . " e.g " . $config->get('tag_manager_domain') . ". Please correct the path 
            or request assistance to authorize your domain.");
        }
      }
      foreach ($tag_manager_fields as $field) {
        if (!$config->get('tag_manager_domain') || empty($config->get('tag_manager_domain'))) {
          $form_state->setErrorByName($field, t("No validation criteria found. Please go to %link to set a validation criteria for the fields.", [
            '%link' => Link::createFromRoute('Validation settings', 'adobe_analytics.validation_config_form')
              ->toString()
          ]));
        }
        elseif ($form_state->getValue($field) && !strstr($form_state->getValue($field), $config->get('tag_manager_domain'))) {
          $form_state->setErrorByName($field, "Scripts can only be hosted at authorized locations, such as " . $config->get('cloud_provider') . " e.g " . $config->get('cloud_domain') . " or on " . $config->get('tag_manager_provider') . " e.g " . $config->get('tag_manager_domain') . ". Please correct the path or request assistance to authorize your domain.");
        }
      }
    }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $config = $this->config('adobe_analytics.settings');

    // Save extra variables.
    $extra_vars = [];
    foreach ($form_state->getValue('variables') as $variable) {
      if (!empty($variable['name']) && !empty($variable['value'])) {
        $extra_vars[] = [
          'name' => $variable['name'],
          'value' => $variable['value'],
        ];
      }
    }

    // Save all the config variables.
    $config
      ->set('installation_mode', $form_state->getValue('installation_mode'))
      ->set('cdn_install_type', $form_state->getValue('cdn_install_type'))
      ->set('development_s_code_config', $form_state->getValue('development_s_code_config'))
      ->set('production_s_code_config', $form_state->getValue('production_s_code_config'))
      ->set('development_s_code', $form_state->getValue('development_s_code'))
      ->set('production_s_code', $form_state->getValue('production_s_code'))
      ->set('development_footer_js_code', $form_state->getValue('development_footer_js_code'))
      ->set('production_footer_js_code', $form_state->getValue('production_footer_js_code'))
      ->set('development_cdn_custom_tracking_js_before', $form_state->getValue('development_cdn_custom_tracking_js_before'))
      ->set('development_cdn_custom_tracking_js_after', $form_state->getValue('development_cdn_custom_tracking_js_after'))
      ->set('production_cdn_custom_tracking_js_before', $form_state->getValue('production_cdn_custom_tracking_js_before'))
      ->set('production_cdn_custom_tracking_js_after', $form_state->getValue('production_cdn_custom_tracking_js_after'))
      ->set('development_tag_manager_container_path', $form_state->getValue('development_tag_manager_container_path'))
      ->set('production_tag_manager_container_path', $form_state->getValue('production_tag_manager_container_path'))
      ->set('development_tag_manager_footer_js', $form_state->getValue('development_tag_manager_footer_js'))
      ->set('production_tag_manager_footer_js', $form_state->getValue('production_tag_manager_footer_js'))
      ->set('extra_variables', $extra_vars)
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
