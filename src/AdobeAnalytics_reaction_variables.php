<?php
namespace Drupal\adobeanalytics;

class AdobeAnalytics_reaction_variables extends context_reaction {
  /**
   * Implements context_reaction::options_form()
   *
   * Allow users to specify varables that should be set for the given context.
   */
  function options_form($context) {
    $settings = $this->fetch_from_context($context);

    // Get the existing variables from context.
    $existing_variables = isset($settings['adobeanalytics_variables']) ? $settings['adobeanalytics_variables'] : array();

    // Build the variables form.
    _adobeanalytics_variables_form($form, $existing_variables);

    // Modify the #parents property to make sure values are stored in the
    // location that contexts expects them.
    $parents = array('reactions', 'plugins', $this->plugin);

    foreach (\Drupal\Core\Render\Element::children($form['adobeanalytics_variables']) as $key) {
      if (is_numeric($key)) {
        $form['adobeanalytics_variables'][$key]['name']['#parents'] = array_merge($parents, $form['adobeanalytics_variables'][$key]['name']['#parents']);
        $form['adobeanalytics_variables'][$key]['value']['#parents'] = array_merge($parents, $form['adobeanalytics_variables'][$key]['value']['#parents']);
      }
    }

    // Modify form to call the contexts-specific versions of certain functions.
    $form['add_another_variable']['#submit'] = array('adobeanalytics_context_add_another_variable_submit');
    $form['add_another_variable']['#ajax']['callback'] = 'adobeanalytics_context_add_another_variable_js';

    return $form;
  }

  /**
   * Implements context_reaction::execute()
   *
   * Updates the $variables array based on the current context. Note that tokens
   * will be replaced later in _adobeanalytics_format_variables().
   *
   * @param array $variables
   *   The variables array, passed by reference. Context defined variables will
   *   be added as new keys in this array.
   */
  function execute(&$variables) {
    foreach ($this->get_contexts() as $context) {
      foreach ($context->reactions['adobeanalytics_vars']['adobeanalytics_variables'] as $key => $value) {
        $variables[$value['name']] = $value['value'];
      }
    }
  }
}
