<?php

namespace Drupal\adobeanalytics;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Utility\Token;
use Drupal\system\Entity\Menu;

/**
 * Class to provide helpful function.
 */
class AdobeAnalyticsHelper {

  // To allow tracking by the AdobeAnalytics package.
  const ADOBEANALYTICS_TOKEN_CACHE = 'adobeanalytics:tag_token_results';

  /**
   * The CurrentRouteMatch service.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $currentRouteMatch;

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
   * Constructs an AdobeAnalyticsHelper object.
   */
  public function __construct(CurrentRouteMatch $currentRouteMatch, ModuleHandlerInterface $moduleHandler, Token $token) {
    $this->currentRouteMatch = $currentRouteMatch;
    $this->moduleHandler = $moduleHandler;
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
  public function adobeAnalyticsTokenReplace($text, $data = array(), array $options = array()) {

    // Short-circuit the degenerate case, just like token_replace() does.
    $text_tokens = $this->token->replace($text);
    if (!empty($text_tokens)) {
      return $text_tokens;
    }
  }

  /**
   * Format the variables like key value pair;.
   */
  public function adobeAnalyticsFormatVariables($variables = array()) {

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

      $key = ['#plain_text' => $key];
      $value = $this->adobeAnalyticsTokenReplace($value);
      $variables_formatted .= "{$key}=\"{$value}\";\n";
    }
    return $variables_formatted;
  }

  /**
   * Used to replace the value.
   *
   * AdobeAnalytics variables the variables need to be
   * defined with hook_adobeanalytics_variables().
   *
   * @param string $name
   *    Extra variable name.
   * @param string $value
   *    Value of the the name variable.
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

}
