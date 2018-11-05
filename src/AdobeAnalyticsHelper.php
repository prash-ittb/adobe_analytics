<?php

namespace Drupal\adobe_analytics;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Routing\AdminContext;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Utility\Token;
use Drupal\system\Entity\Menu;

/**
 * Class to provide helpful function.
 */
class AdobeAnalyticsHelper {

  // To allow tracking by the AdobeAnalytics package.
  const ADOBEANALYTICS_TOKEN_CACHE = 'adobe_analytics:tag_token_results';

  /**
   * The route admin context to determine whether a route is an admin one.
   *
   * @var \Drupal\Core\Routing\AdminContext
   */
  protected $adminContext;

  /**
   * The CurrentRouteMatch service.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $currentRouteMatch;

  /**
   * Current user object.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The ModuleHandler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The Token replacement object.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $token;

  /**
   * Array of variables.
   *
   * @var array
   */
  protected $variables;

  /**
   * Context array.
   *
   * @var array
   */
  protected $context;

  /**
   * CDN and general config settings.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $adobeConfig;

  /**
   * Datalayer config settings.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $datalayerConfig;

  /**
   * Constructs an AdobeAnalyticsHelper object.
   *
   * @param \Drupal\Core\Config\ConfigFactory $config_factory
   *   The config factory service.
   * @param \Drupal\Core\Routing\CurrentRouteMatch $currentRouteMatch
   *   The route matching service.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user account.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler service.
   * @param \Drupal\Core\Routing\AdminContext $admin_context
   *   The admin context service.
   * @param \Drupal\Core\Utility\Token $token
   *   The token service.
   */
  public function __construct(ConfigFactory $config_factory, CurrentRouteMatch $currentRouteMatch, AccountProxyInterface $current_user, ModuleHandlerInterface $moduleHandler, AdminContext $admin_context, Token $token) {
    $this->adobeConfig = $config_factory->get('adobe_analytics.settings');
    $this->datalayerConfig = $config_factory->get('adobe_analytics.data_layer');
    $this->currentRouteMatch = $currentRouteMatch;
    $this->currentUser = $current_user;
    $this->moduleHandler = $moduleHandler;
    $this->adminContext = $admin_context;
    $this->token = $token;
  }

  /**
   * Get the context.
   */
  public function adobeAnalyticsGetTokenContext() {
    if (is_null($this->context)) {
      $this->context['node'] = $this->currentRouteMatch->getParameter('node');
      $this->context['term'] = ($this->currentRouteMatch->getParameter('taxonomy_term')) ? $this->currentRouteMatch->getParameter('taxonomy_term') : 2;
      $this->context['menu'] = Menu::load('main-menu');
    }

    return $this->context;
  }

  /**
   * Replace tokens.
   */
  public function adobeAnalyticsTokenReplace($text, $data = [], array $options = []) {

    // Short-circuit the degenerate case, just like token_replace() does.
    $text_tokens = $this->token->replace($text);
    if (!empty($text_tokens)) {
      return $text_tokens;
    }
  }

  /**
   * Format the variables like key value pair;.
   */
  public function adobeAnalyticsFormatVariables($variables = []) {

    $extra_variables = $this->getVariables();

    // Create context data to be used by token.
    $variables_formatted = '';
    foreach ($variables as $key => $value) {
      if (is_array($value)) {
        // Use the last element.
        $value = end($value);
      }

      if (isset($extra_variables[$key])) {
        $value = $extra_variables[$key];
      }

      $key = htmlspecialchars($key, ENT_NOQUOTES, 'UTF-8');
      $value = $this->adobeAnalyticsTokenReplace($value);
      $variables_formatted .= "{$key}=\"{$value}\";\n";
    }
    return $variables_formatted;
  }

  /**
   * Used to replace the value.
   *
   * AdobeAnalytics variables the variables need to be
   * defined with hook_adobe_analytics_variables().
   *
   * @param string $name
   *   Extra variable name.
   * @param string $value
   *   Value of the the name variable.
   */
  public function setVariable($name = NULL, $value = NULL) {
    if (!empty($name)) {
      $this->variables[$name] = $value;
    }
  }

  /**
   * Return variables.
   *
   * @return array
   *   The array of variables.
   */
  public function getVariables() {
    return $this->variables;
  }

  /**
   * Lazy builder callback to render markup.
   *
   * @return array
   *   Build array.
   */
  public function renderMarkup() {
    $build = [];
    $build['#theme'] = 'analytics_code';

    if ($this->adobeConfig->get('installation_mode') == 'cdn') {
      $build = $this->renderCdnMarkup($build);
    }

    if ($this->datalayerConfig->get('data_layer_enabled') == 1) {
      $build = $this->renderDatalayerMarkup($build);
    }

    if ($this->adobeConfig->get('installation_mode') == 'general') {
      if ($this->skipTracking()) {
        return [];
      }

      // Extract module settings.
      $js_file_location = $this->adobeConfig->get('js_file_location');
      $codesnippet = $this->adobeConfig->get('codesnippet');
      $version = $this->adobeConfig->get("version");
      $nojs = !empty($this->adobeConfig->get("image_file_location")) ? $this->adobeConfig->get("image_file_location") : NULL;

      // Extract entity overrides.
      list ($include_main_codesnippet, $include_custom_variables, $entity_snippet) = $this->extractEntityOverrides();

      // Format and combine variables in the "right" order right order is the
      // code file (list likely to be maintained) then admin settings with
      // codesnippet first and finally taxonomy->vars.
      $formatted_vars = '';

      // Load variables implemented by modules.
      $adobe_analytics_hooked_vars = $this->moduleHandler->invokeAll('adobe_analytics_variables', []);

      // Append header variables.
      if ($include_custom_variables && !empty($adobe_analytics_hooked_vars['header'])) {
        $formatted_vars = $this->adobeAnalyticsFormatVariables($adobe_analytics_hooked_vars['header']);
      }

      // Append main JavaScript snippet.
      if ($include_main_codesnippet && !empty($codesnippet)) {
        $formatted_vars .= $this->formatJsSnippet($codesnippet);
      }

      // Append main variables.
      if ($include_custom_variables && !empty($adobe_analytics_hooked_vars['variables'])) {
        $formatted_vars .= $this->adobeAnalyticsFormatVariables($adobe_analytics_hooked_vars['variables']);
      }

      // Append footer variables.
      if ($include_custom_variables && !empty($adobe_analytics_hooked_vars['footer'])) {
        $formatted_vars .= $this->adobeAnalyticsFormatVariables($adobe_analytics_hooked_vars['footer']);
      }

      // Append entity's custom snippet.
      if (!empty($entity_snippet)) {
        $formatted_vars .= $this->formatJsSnippet($entity_snippet);
      }

      $build['#general_status'] = TRUE;
      $build['#js_file_location'] = $js_file_location;
      $build['#version'] = $version;
      $build['#image_location'] = $nojs;
      $build['#formatted_vars'] = $formatted_vars;

    }

    return $build;
  }

  /**
   * Function to render cdn markup.
   *
   * @param array $build
   *   Build array containing theme markup.
   *
   * @return array
   *   Build array.
   */
  protected function renderCdnMarkup(array $build) {
    if ($this->skipTracking()) {
      return [];
    }

    $environment = \Drupal::config('adobe_analytics')->get('mode');

    if ($environment == 'prod') {
      $environment = 'production';
    }
    else {
      $environment = 'development';
    }

    $cdnType = $this->adobeConfig->get('cdn_install_type');

    // For Amazon S3.
    if ($cdnType == 'amazon') {
      $build['#amazon_status'] = TRUE;
      $build['#s_code_config_path'] = $this->adobeConfig->get($environment . '_s_code_config');
      $build['#s_code_path'] = $this->adobeConfig->get($environment . '_s_code');
      $build['#footer_js_code'] = $this->adobeConfig->get($environment . '_footer_js_code');
      if (!empty($this->adobeConfig->get($environment . '_cdn_custom_tracking_js_before'))) {
        $build['#custom_tracking_js_before'] = $this->adobeConfig->get($environment . '_cdn_custom_tracking_js_before');
      }
      if (!empty($this->adobeConfig->get($environment . '_cdn_custom_tracking_js_after'))) {
        $build['#custom_tracking_js_after'] = $this->adobeConfig->get($environment . '_cdn_custom_tracking_js_after');
      }
    }
    else {
      // For Tag Manager Tool.
      $build['#tag_status'] = TRUE;
      if (!empty($this->adobeConfig->get($environment . '_tag_manager_footer_js'))) {
        $build['#tag_manager_footer_js'] = $this->adobeConfig->get($environment . '_tag_manager_footer_js');
      }
    }

    return $build;
  }

  /**
   * Function to render datalayer markup.
   *
   * @param array $build
   *   Build array containing theme and cdn markup, if cdn is enabled.
   *
   * @return array
   *   Build array.
   */
  protected function renderDatalayerMarkup(array $build) {
    if ($this->skipTracking()) {
      return [];
    }

    $environment = \Drupal::config('adobe_analytics')->get('mode');

    if ($environment == 'prod') {
      $environment = 'production';
    }
    else {
      $environment = 'development';
    }

    // Get value of data layer variable.
    $data_root_field = !empty($this->datalayerConfig->get('data_layer_root_field')) ? $this->datalayerConfig->get('data_layer_root_field') : 'pfAnalyticsData';
    $data_layer_json = $this->datalayerConfig->get('data_layer_json_object');

    // $node = \Drupal::request()->attributes->get('node');
    if (empty($data_layer_json) || !\Drupal::moduleHandler()
        ->moduleExists('token')
    ) {
      return [];
    }
    $build['#data_layer_status'] = TRUE;
    $build['#data_layer_json'] = 'window.' . $data_root_field . ' = ' . $this->formatJsSnippet($data_layer_json);
    $build['#custom_js'] = \Drupal::config('adobe_analytics.data_layer_custom_javascript')
      ->get($environment . '_data_layer_custom_javascript');

    return $build;
  }

  /**
   * Determines whether or not to skip adding analytics code.
   *
   * @return bool
   *   Skips the tracking if true.
   */
  public function skipTracking() {
    // Check if we should track the currently active user's role.
    $config = $this->adobeConfig;
    $track_user = TRUE;
    $get_roles = [];
    $tracking_type = $config->get('role_tracking_type');
    $stored_roles = $config->get('track_roles');
    if ($stored_roles) {
      $get_roles = [];
      foreach ($stored_roles as $key => $value) {
        if ($value) {
          // Get all the selected roles.
          $get_roles[$key] = $key;
        }
      }
    }

    // Compare the roles with current user.
    if (is_array($this->currentUser->getRoles())) {
      foreach ($this->currentUser->getRoles() as $role) {
        if (array_key_exists($role, $get_roles)) {
          if ($tracking_type == 'inclusive') {
            $track_user = TRUE;
          }
          if ($tracking_type == 'exclusive') {
            $track_user = FALSE;
          }
          break;
        }
      }
    }

    // Don't track page views in the admin sections, or for certain roles.
    $is_admin = $this->adminContext->isAdminRoute();
    if ($is_admin || $track_user == FALSE) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Extracts entity overrides when the entity has an Adobe Analytics field.
   *
   * @return array
   *   An array containing:
   *     * A flag for whether to include the global custom JavaScript snippet.
   *     * A flag for whether to include the global custom variables.
   *     * A string with a custom JavaScript snippet, or an empty string.
   */
  protected function extractEntityOverrides() {
    // Check if we are viewing an entity containing field overrides.
    $entity_field_manager = \Drupal::service('entity_field.manager');
    $route_match = \Drupal::routeMatch();
    $entity = NULL;
    $field_name = NULL;
    foreach ($entity_field_manager->getFieldMapByFieldType('adobe_analytics') as $entity_type => $field_config) {
      if ($entity = $route_match->getParameter($entity_type)) {
        $field_name = key($field_config);
        break;
      }
    }

    $include_main_codesnippet = TRUE;
    $include_custom_variables = TRUE;
    $entity_snippet = '';
    if (!empty($entity) && !$entity->{$field_name}->isEmpty()) {
      $entity_values = $entity->{$field_name}->first()->getValue();
      $include_main_codesnippet = $entity_values['include_main_codesnippet'];
      $include_custom_variables = $entity_values['include_custom_variables'];
      $entity_snippet = $entity_values['codesnippet'];
    }

    return [
      $include_main_codesnippet,
      $include_custom_variables,
      $entity_snippet,
    ];
  }

  /**
   * Processes tokens and formats a JavaScript snippet.
   *
   * @param string $raw_snippet
   *   The raw snippet.
   *
   * @return string
   *   The processed snippet.
   */
  protected function formatJsSnippet($raw_snippet) {
    // Add any custom code snippets if specified and replace any tokens.
    $context = $this->adobeAnalyticsGetTokenContext();
    $snippet = $this->adobeAnalyticsTokenReplace(
        $raw_snippet, $context, [
          'clear' => TRUE,
          'sanitize' => TRUE,
        ]
      ) . "\n";
    return $snippet;
  }

}
