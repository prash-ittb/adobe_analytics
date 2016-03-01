<?php

/**
 * @file
 * Contains \Drupal\adobeanalytics\Form\AdobeanalyticsAdminSettings.
 */

namespace Drupal\adobeanalytics\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;

class AdobeanalyticsAdminSettings extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'adobeanalytics_content';
  }

  protected function getEditableConfigNames() {
    return ['adobeanalytics.settings'];
  }

  /**
   * {@inheritdoc}
   */
  function adobeanalytics_menu() {
  $items['admin/config/system/adobeanalytics'] = array(
    'title' => 'AdobeAnalytics',
    'description' => "Configure the settings used to integrate AdobeAnalytics analytics.",
    'page callback' => 'drupal_get_form',
    'page arguments' => array('buildForm'),
    'access arguments' => array('administer AdobeAnalytics configuration'),
    'type' => MENU_NORMAL_ITEM,
  );

  return $items;
}
  
  /**
   * {@inheritdoc}
   */
function adobeanalytics_context_plugins() {
  $plugins = array();
  $plugins['adobeanalytics_reaction_variables'] = array(
    'handler' => array(
      'path' => drupal_get_path('module', 'adobeanalytics') .'/plugins/context',
      'file' => 'adobeanalytics_reaction_variables.inc',
      'class' => 'adobeanalytics_reaction_variables',
      'parent' => 'context_reaction',
    ),
  );
  return $plugins;
}

function adobeanalytics_context_registry() {
  $reg = array(
    'reactions' => array(
      'adobeanalytics_vars' => array(
        'title' => t('AdobeAnalytics Variables'),
        'plugin' => 'adobeanalytics_reaction_variables',
      ),
    ),
  );
  return $reg;
}

/**
 * Implementation of hook_adobeanalytics_variables().
 */
function adobeanalytics_adobeanalytics_variables() {
  $variables = array();

  // Include variables set using the context module.
  if (\Drupal::moduleHandler()->moduleExists('context')) {
    if ($plugin = context_get_plugin('reaction', 'adobeanalytics_vars')) {
      $plugin->execute($variables);
    }
  }
// Include variables from the "custom variables" section of the settings form.
  // @FIXME
// Could not extract the default value because it is either indeterminate, or
// not scalar. You'll need to provide a default value in
// config/install/adobeanalytics.settings.yml and config/schema/adobeanalytics.schema.yml.
$settings_variables = \Drupal::config('adobeanalytics.settings')->get('adobeanalytics_variables');
  foreach ($settings_variables as $variable) {
    $variables[$variable['name']] = $variable['value'];
  }

  return array('variables' => $variables);
}


function adobeanalytics_page_attachments(array &$attachments) {
  $user = \Drupal::currentUser();
   $attachments['#attached']['library'][] = 'core/assets/vendor/adobeanalytics';

}

  // Check if we should track the currently active user's role.
/*  $track = 0;
  if (is_array($user->user__roles)) {
    foreach ($user->user__roles as $role) {
      $role = str_replace(' ', '_', $role);
      // @FIXME
// // @FIXME
// // The correct configuration object could not be determined. You'll need to
// // rewrite this call manually.
 $track += \Drupal::config('adobeanalytics.settings')->get("adobeanalytics_track_{$role}", FALSE);

    }
  }

  $tracking_type = \Drupal::config('adobeanalytics.settings')->get('adobeanalytics_role_tracking_type');
  $track = $tracking_type == 'inclusive' ? $track > 0 : $track <= 0;

  // Don't track page views in the admin sections, or for certain roles.
 if (path_is_admin(\Drupal\Core\Url::fromRoute("<current>")->toString()) || !$track) {
    return;
  }

  // Like drupal_add_js, add a query string to the end of the js file location.
  // @FIXME
// // @FIXME
// // This looks like another module's variable. You'll need to rewrite this call
// // to ensure that it uses the correct configuration object.
 $query_string = '?' . \Drupal::config('adobeanalytics.settings')->get('css_js_query_string', '0');

  $js_file_location = \Drupal\Component\Utility\SafeMarkup::checkPlain(\Drupal::config('adobeanalytics.settings')->get("adobeanalytics_js_file_location"));

  // Add any custom code snippets if specified and replace any tokens.
  $context = adobeanalytics_get_token_context();
  $codesnippet = adobeanalytics_token_replace(\Drupal::config('adobeanalytics.settings')->get('adobeanalytics_codesnippet'), $context, array(
    'clear' => TRUE,
    'sanitize' => TRUE,
  )) . "\n";

  // Format and combine variables in the "right" order
  // Right order is the code file (list likely to be maintained)
  // Then admin settings with codesnippet first and finally taxonomy->vars
  $extra_variables_formatted = $codesnippet;

  $header = "<!-- AdobeAnalytics code version: ";
  $header .= \Drupal\Component\Utility\SafeMarkup::checkPlain(\Drupal::config('adobeanalytics.settings')->get("adobeanalytics_version"));
  $header .= "\nCopyright 1996-" . date('Y') . " Adobe, Inc. -->\n";
  $header .= "<script type=\"text/javascript\" src=\"";
  $header .= $js_file_location . $query_string;
  $header .= "\"></script>\n";
  $header .= "<script type=\"text/javascript\"><!--\n";

  $footer = '/************* DO NOT ALTER ANYTHING BELOW THIS LINE ! **************//*'."\n";
  $footer .= 'var s_code=s.t();if(s_code)document.write(s_code)//--></script>'."\n";
  $footer .= '<script type="text/javascript"><!--'."\n";
  $footer .= "if(navigator.appVersion.indexOf('MSIE')>=0)document.write(unescape('%3C')+'\!-'+'-')"."\n";
  $footer .= '//--></script>' . "\n";
  $nojs = \Drupal::config('adobeanalytics.settings')->get("adobeanalytics_image_file_location");
  if (!empty($nojs)) {
    $footer .= '<noscript><img src="' . check_url($nojs . '/' . rand(0, 10000000)) . '" height="1" width="1" alt=""></noscript>' . "\n";
  }
  $footer .= '<!--/DO NOT REMOVE/-->' . "\n";
  $footer .= '<!-- End AdobeAnalytics code version: ';
  $footer .=  \Drupal\Component\Utility\SafeMarkup::checkPlain(\Drupal::config('adobeanalytics.settings')->get("adobeanalytics_version"));
  $footer .= ' -->'."\n";

  if ($adobeanalytics_hooked_vars = \Drupal::moduleHandler()->invokeAll('adobeanalytics_variables', [$page])) {
    if (isset($adobeanalytics_hooked_vars['header'])) {
      $header = $adobeanalytics_hooked_vars['header'];
    }
    if (isset($adobeanalytics_hooked_vars['variables'])) {
      $extra_variables_formatted .= adobeanalytics_format_variables($adobeanalytics_hooked_vars['variables']);
    }
    if (isset($adobeanalytics_hooked_vars['footer'])) {
      $footer = $adobeanalytics_hooked_vars['footer'];
    }
  }

  $attachments['#attached']['adobeanalytics'] =  array(
    'header'=> array(
      '#type' => 'markup',
      '#markup' => $header,
    ),
    'variables'=> array(
      '#type' => 'markup',
      '#markup' => $extra_variables_formatted,
    ),
    'footer'=> array(
      '#type' => 'markup',
      '#markup' => $footer,
    ),
  );
  return $attachments;
}
*/




  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('adobeanalytics.settings');


    $form['general'] = [
      // fieldset changed to details in drupal 8
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
      '#default_value' => \Drupal::config('adobeanalytics.settings')->get("adobeanalytics_image_file_location"),
    ];

    $form['general']['adobeanalytics_version'] = [
      '#type' => 'textfield',
      '#title' => t("AdobeAnalytics version (used by adobeanalytics for debugging)"),
      '#default_value' => \Drupal::config('adobeanalytics.settings')->get("adobeanalytics_version"),
    ];

    $form['general']['adobeanalytics_token_cache_lifetime'] = [
      '#type' => 'textfield',
      '#title' => t("Token cache lifetime"),
      '#default_value' => \Drupal::config('adobeanalytics.settings')->get("adobeanalytics_token_cache_lifetime"),
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
      '#default_value' => \Drupal::config('adobeanalytics.settings')->get("adobeanalytics_role_tracking_type",'inclusive'),
    ];
      // role table no longer exists in Drupal 8 , user__roles is used instead
    $result = db_select('user__roles', 'r')
      ->fields('r')
      //role_name is replaced with roles_target_id
      ->orderBy('roles_target_id', 'ASC')
      ->execute();
      
      //role table name replaced with user__roles
    foreach ($result as $role) {
      $user__role_varname = str_replace(' ', '_', $role->roles_target_id);
      $role_name = in_array($role->entity_id, array(DRUPAL_ANONYMOUS_RID, DRUPAL_AUTHENTICATED_RID)) ? t($role->roles_target_id) : $role->roles_target_id;
      $form['roles']["adobeanalytics_track_{$user__role_varname}"] = [
          '#type' => 'checkbox',
          '#title' => $role_name,
         '#default_value' => \Drupal::config('adobeanalytics.settings')->get("adobeanalytics_track_{$user__role_varname}",TRUE),
      ];
    }
    $form['variables'] = [
      '#type' => 'details',
      '#title' => t('Custom Variables'),
      '#open' => FALSE,
      '#description' => t('You can define tracking variables here.'),
      '#weight' => '-3',
      ];
    $this->adobeanalytics_variables_form($form);


    //dpm($existing_variables);
    $form['advanced'] = [
      '#type' => 'details',
      '#title' => t('Advanced'),
      '#description' => t('You can add custom AdobeAnalytics code here.'),
      '#open' => FALSE,
      '#weight' => '-2',
    ];

     $form['advanced']['adobeanalytics_codesnippet'] = [
         '#type' => 'textarea',
         '#title' => t('JavaScript Code'),
         '#default_value' => \Drupal::config('adobeanalytics.settings')->get('adobeanalytics_codesnippet'),
         '#rows' => 15,
         '#description' => (' Example : <br/> - if ([current-date:custom:N] >= 6) { s.prop5 = "weekend"; }<br/>
     - if ("[current-page:url:path]" == "node") {s.prop9 = "homepage";} else {s.prop9 = "[current-page:title]";}'), 
         // The description above is Hard Coded. Check if it can be replaced with using a variable to increase readability
         //'#theme'=> array('items' => $examples),
       ];

    $form['advanced']['tokens'] = [
      '#theme' => 'token_tree',
      '#token_types' => [
        'node',
        'menu',
        'term',
        'user',
      ],
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
  function adobeanalytics_variables_form(&$form) {
    $config = $this->config('adobeanalytics.settings');
    $extra_variables = $config->get('adobeanalytics_variables');
    $existing_variables = unserialize($extra_variables);
    $headers = array(t('Name'), t('Value'));
    $rows = array();
    foreach (\Drupal\Core\Render\Element::children($form) as $key) {
      $rows[] = array(\Drupal::service("renderer")->render($form[$key]['name']), \Drupal::service("renderer")->render($form[$key]['value']));
    }

    $form['variables']['adobeanalytics_variables'] = [
        '#type' => 'table',
        '#header' => $headers,
        '#rows' => $rows,
    ];
    $number = 0;
    foreach ($existing_variables as $key_name => $key_value) {
      $this->adobeanalytics_variable_form($form, $number, $key_name, $key_value);
      $number++;
    }
    $lastEmpty = FALSE;
    //print_r($form['variables']['hugs_variables']);
    foreach($form['variables']['adobeanalytics_variables'] as $key => $single_field) {
      if(isset($single_field[$key]['name']) && isset($single_field[$key]['value'])) {
        $lastEmpty = TRUE;
      }

    }
    if(!$lastEmpty) {
      $this->adobeanalytics_variable_form($form, sizeof($existing_variables) + 1, '', '');

    }
 /*   $form['submit'] = [
        '#type' => 'submit',
        '#value' => t('Add another variable'),
        '#submit' => array('adobeanalytics_add_another_variable_submit'),
        '#ajax' => [
            'callback' => 'adobeanalytics_add_another_variable_js',
            'wrapper' => 'adobeanalytics-variables-wrapper',
            'effect' => 'fade', ]
    ];
    $form['#submit']['#ajax'] = [
        'callback' => 'adobeanalytics_add_another_variable_js',
        'wrapper' => 'adobeanalytics-variables-wrapper',
        'effect' => 'fade', ];
    /* '#limit_validation_errors' => [],
*/

    $form['tokens'] = [
        '#theme' => 'token_tree',
        '#token_types' => array('node', 'menu', 'term', 'user'),
        '#global_types' => TRUE,
        '#click_insert' => TRUE,
        '#dialog' => TRUE,
    ];

  }

  public function adobeanalytics_variable_form(&$form, $index, $key_name, $key_value ) {

    $form['variables']['adobeanalytics_variables'][$index]['name'] = [
        '#type' => 'textfield',
        '#size' => 40,
        '#maxlength' => 40,
       '#title_display' => 'invisible',
        '#title' => t('Name'),
        '#default_value' => ($key_name != '' ? $key_name : ''),
    //    '#parents' => ['adobeanalytics_variables', $key_name, 'name'],
        '#attributes' => ['class' => ['field-variable-name']],
    //   '#element_validate' => ['adobeanalytics_validate_variable_name'],
      //'#theme' => 'adobeanalytics_variables',
    ];
    $form['variables']['adobeanalytics_variables'][$index]['value'] = [
        '#type' => 'textfield',
        '#size' => 40,
        '#maxlength' => 40,
       '#title_display' => 'invisible',
        '#title' => t('Value'),
        '#default_value' => ($key_value != '' ? $key_value : ''),
     //   '#parents' => ['adobeanalytics_variables', $key_value, 'value'],
        '#attributes' => ['class' => ['field-variable-value']],
      // '#theme' => 'adobeanalytics_variables',
    ];

    if (empty($key_name) && empty($key_value)) {
      $form['variables']['adobeanalytics_variables'][$index]['name']['#description'] = t('Example: prop1');
      $form['variables']['adobeanalytics_variables'][$index]['value']['#description'] = t('Example: [current-page:title]');
    }
    return $form;
    // dpm($debugging);
  }
/*
  function adobeanalytics_add_another_variable_submit($form, $key, &$form_state) {

    $form_state['_adobeanalytics_variables'][$key]['name'] = $form_state->getUserInput('adobeanalytics_name');
    $form_state['adobeanalytics_variables'][$key]['value']= $form_state->getUserInput('adobeanalytics_value');
    $form_state->setRebuild();
    \Drupal::$formBuilder()->buildForm('Drupal\adobeanalytics\Form\AdobeanalyticsAdminSettings',$form_state);
  }

  function adobeanalytics_add_another_variable_js($form, $form_state) {
    // @todo By hard-coding, "variables" here it forces a generic name for the containing form element. This is awkward for the node edit form.
    return $form['variables']['adobeanalytics_variables'];
  }
*/
  /**
 * Validation function used by the variables form.
 */


function adobeanalytics_variables_form_validate($form, &$form_state) {
  if ($form_state['triggering_element']['#value'] != t('Add another variable')) {
   adobeanalytics_variables_trim_empties($form_state['values']);
  }
}

/**
 * Submit function for the variables form.
 */


function adobeanalytics_variables_form_submit($form, &$form_state) {
  // clear our cached token generation, since it may have just changed anyway.
  cache_clear_all(ADOBEANALYTICS_TOKEN_CACHE, 'cache', TRUE);
}

/**
 * Given the values entered into the adobeanalytics variables form, remove any empty
 * variables (i.e. both "name" & "value" are blank).
 */

function adobeanalytics_variables_trim_empties(&$values, $parent = 'adobeanalytics_variables') {
  foreach ($values as $key => &$val) {
    if ($key === $parent) {
      // We found the adobeanalytics variables.
      foreach ($val as $k => $v) {
        if (empty($val[$k]['name']) && empty($val[$k]['value'])) {
          unset($val[$k]);
        }
      }
      // Reset the array indexes to prevent wierd behavior caused by a variable
      // being removed in the middle of the array.
      $val = array_values($val);
      break;
    }
    elseif (is_array($val)) {
      adobeanalytics_variables_trim_empties($val, $parent);
    }
  }
}

/**
 * AJAX callback function for adding variable fields to the settings form.
 */



/**
 * Submit handler to add more variables.
 */



/**
 * Validation function for variable names.
 */

function adobeanalytics_validate_variable_name($element, &$form_state, $form) {
  // Variable names must follow the rules defined by javascript syntax.
  if (!empty($element['#value']) && !preg_match("/^[A-Za-z_$]{1}\S*$/", $element['#value'])) {
    form_error($element, t('This is not a valid variable name. It must start with a letter, $ or _ and cannot contain spaces.'));
  }
}

/**
 * Form validation.
 */

function buildForm_validate($form, &$form_state) {
  // Remove any empty variables.
  foreach ($form_state['values']['adobeanalytics_variables'] as $key => $val) {
    if (empty($val['name']) && empty($val['value'])) {
      unset($form_state['values']['adobeanalytics_variables'][$key]);
    }
  }
  $form_state['values']['adobeanalytics_variables'] = array_values($form_state['values']['adobeanalytics_variables']);
}

function adobeanalytics_set_variable($name = NULL, $value = NULL) {
  $variables = &drupal_static(__FUNCTION__, array());

  if (empty($name)) {
    return $variables;
  }
  else {
    $variables[$name] = $value;
  }
}

function adobeanalytics_get_variables() {
  return adobeanalytics_set_variable();
}
function adobeanalytics_theme() {
  $theme['adobeanalytics_variables'] = [
      'render element' => 'form',
      'template' => 'adobeanalytics_variables',
    	];
	return $theme;	
}

function adobeanalytics_format_variables(array $variables = array()) {
  $extra_variables = adobeanalytics_get_variables();

  // Create context data to be used by token.
  $context = !empty($variables) ? adobeanalytics_get_token_context() : array();

  $variables_formatted = '';
  foreach ($variables as $key => $value) {
    if (is_array($value)) {
      // Use the last element.
      $value = end($value);
    }

    if (isset($extra_variables[$key])) {
      $value = $extra_variables[$key];
    }

    // Cannot use check_plain() here because $key may contain quotes (e.g. 's.contextData["tve_domain"]').
    $key = htmlspecialchars($key, ENT_NOQUOTES, 'UTF-8');
    $value = adobeanalytics_token_replace($value, $context, array(
      'clear' => TRUE,
      'sanitize' => FALSE,
    ));
    $value = \Drupal\Component\Serialization\Json::encode($value);
    $variables_formatted .= "{$key}={$value};\n";
  }
  return $variables_formatted;
}

function adobeanalytics_token_replace($text, $data = array(), array $options = array()) {
  $processed_strings =& drupal_static(__FUNCTION__, NULL);

  // Short-circuit the degenerate case, just like token_replace() does.
  $text_tokens = \Drupal::token()->scan($text);
  if (empty($text_tokens)) {
    return $text;
  }

  // Determine the cache key for this text string. That way we can cache reliably.
  $key = adobeanalytics_token_replace_make_key($text, $data);

  $cache_item = ADOBEANALYTICS_TOKEN_CACHE . ':' . \Drupal\Core\Url::fromRoute("<current>")->toString();

  // Lookup any already-cached token replacements.
  if (is_null($processed_strings)) {
    $cache = \Drupal::cache('cache')->get($cache_item);
    $processed_strings = $cache
      ? $cache->data
      : array();
  }


  // If the processed string we're looking for isn't already in the cache,
  // then, and only then, do we call the expensive token_replace() (and cache
  // the result).
  if (!isset($processed_strings[$key]) || is_null($processed_strings[$key])) {
    // Regenerate this particular replacement.
    $processed_strings[$key] = \Drupal::token()->replace($text, $data, $options);
    $lifetime = \Drupal::config('adobeanalytics.settings')->get('adobeanalytics_token_cache_lifetime');
    $expire_at = ($lifetime == 0) ? CACHE_TEMPORARY : (REQUEST_TIME + $lifetime);
    \Drupal::cache('cache')->set($cache_item, $processed_strings, $expire_at);
  }

  return $processed_strings[$key];
}

function adobeanalytics_token_replace_make_key($text, array $data) {

  // $text may be arbitrarily long, which can slow-down lookups. Hashing it
  // keeps uniqueness but guarantees a manageable size. Since this value won't
  // be used as the cache key itself we're not limited to 255 characters but
  // it will be nicer on array lookups in PHP.
  $keys[] = sha1($text);
  $keys[] = isset($data['node']->nid) ? $data['node']->nid . '-' . entity_modified_last('node', $data['node'])  : NULL;
  $keys[] = isset($data['menu']->menu_name) ? $data['menu']->menu_name . '-' . entity_modified_last('menu', $data['menu']) : NULL;
  $keys[] = isset($data['tag']->machinename) ? $data['tag']->machinename . '-' . entity_modified_last('tag', $data['tag']) : NULL;

  return implode('|', array_filter($keys));
}

function adobeanalytics_get_token_context() {
  $context = &drupal_static(__function__);

  if (is_null($context)) {
    $context['node'] = \Drupal::routeMatch()->getParameter('node');
    $context['term'] = \Drupal::routeMatch()->getParameter('taxonomy_term', 2);
    if (\Drupal::moduleHandler()->moduleExists('menu')) {
      $context['menu'] = menu_load('main-menu');
    }
  }

  return $context;
}

public function submitForm(array &$form, FormStateInterface $form_state) {
  $config = $this->config('adobeanalytics.settings');
  $extra_vars = array();
  foreach($form_state->getValue('adobeanalytics_variables') as $vars) {
    $extra_vars[$vars['name']] = $vars['value'];
  }

  if(!empty($extra_vars)) {
    $config->set('adobeanalytics_variables', '')->save(); // empty it first
    $config->set('adobeanalytics_variables', serialize($extra_vars))->save(); // save all the data
    // $config->get('hugs_variables', unserialize($extra_vars)); //get all the values
    //echo '<pre>';print_r($extra_vars); die;
  }

    \Drupal::configFactory()->getEditable('adobeanalytics.settings')->set('adobeanalytics_js_file_location', $form_state->getValue('adobeanalytics_js_file_location'))->save();
     \Drupal::configFactory()->getEditable('adobeanalytics.settings')->set('adobeanalytics_image_file_location', $form_state->getValue('adobeanalytics_image_file_location'))->save();
    \Drupal::configFactory()->getEditable('adobeanalytics.settings')->set('adobeanalytics_version', $form_state->getValue('adobeanalytics_version'))->save();
     \Drupal::configFactory()->getEditable('adobeanalytics.settings')->set('adobeanalytics_token_cache_lifetime', $form_state->getValue('adobeanalytics_token_cache_lifetime'))->save();
    \Drupal::configFactory()->getEditable('adobeanalytics.settings')->set('adobeanalytics_codesnippet', $form_state->getValue('adobeanalytics_codesnippet'))->save();
  \Drupal::configFactory()->getEditable('adobeanalytics.settings')->set('adobeanalytics_role_tracking_type', $form_state->getValue('adobeanalytics_role_tracking_type'))->save();
  \Drupal::configFactory()->getEditable('adobeanalytics.settings')->set('adobeanalytics_track_administrator',$form_state->getValue('adobeanalytics_track_administrator'))->save();
  \Drupal::configFactory()->getEditable('adobeanalytics.settings')->set('adobeanalytics_track_anonymous_user',$form_state->getValue('adobeanalytics_track_anonymous_user'))->save();
  \Drupal::configFactory()->getEditable('adobeanalytics.settings')->set('adobeanalytics_track_authenticated_user',$form_state->getValue('adobeanalytics_track_authenticated_user'))->save();


}

}
