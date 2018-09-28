<?php

namespace Drupal\adobe_analytics\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class DataLayerForm.
 */
class DataLayerForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'adobe_analytics.data_layer',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'data_layer_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('adobe_analytics.data_layer');
    $form['#attached']['library'][] = 'adobe_analytics/codesnippet';
    $form['data_layer_enabled'] = [
      '#required' => '1',
      '#key_type_toggled' => '0',
      '#default_value' => $config->get('data_layer_enabled'),
      '#weight' => '0',
      '#type' => 'radios',
      '#options' => [
        '1' => t('Yes'),
        '0' => t('No'),
      ],
      '#title' => t('Enable Data Layer'),
    ];

    $form['data_layer_root_field'] = [
      '#required' => '0',
      '#description' => t('Enter your development Adobe analytics tracking S code configuration path (s_code_config.js).'),
      '#weight' => '0',
      '#type' => 'textfield',
      '#title' => t('Data Layer Root Field'),
      '#default_value' => $config->get('data_layer_root_field'),
    ];

    $form['data_layer_json_object'] = [
      '#title' => t('Adobe Analytics Global Data Layer Object in JSON format'),
      '#type' => 'textarea',
      '#description' => t('Dynamic value generated from Key and Value inserted in the below fields. This code will be as a Data Layer on all the pages.'),
      '#rows' => 20,
      '#default_value' => $config->get('data_layer_json_object'),
      '#attributes' => [
        'class' => ['use-snippet'],
      ],
    ];

    $form['data_layer_level_option'] = [
      '#title' => t('Data Layer Object Option'),
      '#type' => 'select',
      "#options" => [
        "select_layer" => t("Select option"),
        "remove_existing" => t("Remove Existing"),
      ],
      '#description' => t('Select option to remove existing element.'),
      '#default_value' => 'select_layer',
    ];

    $data_layer_options = $this->transformJsonToArray();

    $form['data_layer_keys'] = [
      '#title' => t('Data Layer Object Select Keys'),
      '#type' => 'select',
      '#options' => $data_layer_options,
      '#empty_option' => t('Select Key'),
      '#description' => $this->t('Choose the Key to remove from Data Layer Object.'),
      '#default_value' => 'select_layer',
    ];
    $form['data_layer_key'] = [
      '#title' => t('Name'),
      '#type' => 'textfield',
      '#description' => t('Insert your Data Layer Key here. This code will used to generate JSON object.'),
    ];
    $form['data_layer_value'] = [
      '#title' => t('Value'),
      '#type' => 'textfield',
      '#description' => t('Insert your Data Layer Value here. Use tokens to get dynamic values.'),
    ];

    $form['tokens'] = [
      '#theme' => 'token_tree_link',
      '#token_types' => ['node', 'user', 'custom_group'],
      '#click_insert' => TRUE,
    ];

    $form['roles'] = [
      '#type' => 'details',
      '#title' => $this->t('User role tracking'),
      '#open' => TRUE,
      '#description' => $this->t('Define which user roles should, or should not be tracked by AdobeAnalytics.'),
      '#weight' => '5',
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
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('adobe_analytics.data_layer');
    $data_layer_array = [];
    $key = $form_state->getValue('data_layer_key');
    $data_layer_keys = $form_state->getValue('data_layer_keys');
    $value = $form_state->getValue('data_layer_value');
    $check_option = $form_state->getValue('data_layer_level_option');
    $data_layer_org = $config->get('data_layer_json_object') ? $config->get('data_layer_json_object') : '[]';
    if (!empty($data_layer_org)) {
      $data_layer_array = json_decode($data_layer_org, TRUE);
    }
    switch ($check_option) {
      case 'remove_existing':
        $keys = explode(".", $data_layer_keys);
        $data_layer_array = $this->buildJson($data_layer_array, $keys, $value, 1);
        break;

      default:
        if (!empty($key)) {
          $keys = explode('.', $key);
          $data_layer_array = $this->buildJson($data_layer_array, $keys, $value, 0);
        }
    }

    $config
      ->set('data_layer_enabled', $form_state->getValue('data_layer_enabled'))
      ->set('data_layer_root_field', $form_state->getValue('data_layer_root_field'))
      ->set('data_layer_json_object', json_encode($data_layer_array, JSON_PRETTY_PRINT))
      ->set('role_tracking_type', $form_state->getValue('role_tracking_type'))
      ->set('track_roles', $form_state->getValue('track_roles'))
      ->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * Build the JSON object based on the key and value from the form.
   *
   * @param array $data_layer_array
   *   The data layer array.
   * @param mixed $keys
   *   The key to be inserted/removed into/from the $data_layer array.
   * @param mixed $value
   *   The value to be inserted/removed into/from into the $data_layer array.
   * @param int $remove
   *   Flag to specify whether to remove the key or insert the item.
   *
   * @return array
   *   Returns the processed $data_layer array.
   */
  private function buildJson(array &$data_layer_array, $keys, $value, $remove = 1) {
    $branch = &$data_layer_array;

    // Remove the last element of array.
    $array_remove_last = array_pop($keys);

    // Loop through the array.
    while (count($keys)) {
      // Remove the first element and return its value.
      $key = array_shift($keys);

      // Check if $key is present in $branch.
      if (!array_key_exists($key, $branch)) {
        // Create a array with $key as key value.
        $branch[$key] = [];
      }

      $branch = &$branch[$key];
    }

    if ($remove == 1) {
      unset($branch[$array_remove_last]);
    }
    else {
      $branch[$array_remove_last] = $value;
    }
    return array_filter($data_layer_array);
  }

  /**
   * Transform JSON object from Data Layer Object Option to array.
   */
  private function transformJsonToArray($path_separator = '.') {
    $result = [];
    $data_layer_org = $this->config('adobe_analytics.data_layer')
      ->get('data_layer_json_object');
    $array = json_decode($data_layer_org, TRUE);
    while ($array) {
      $value = reset($array);
      $key = key($array);
      unset($array[$key]);

      if (is_array($value)) {
        $build = [$key => ''];
        foreach ($value as $sub_key => $sub_val) {
          $build[$key . $path_separator . $sub_key] = $sub_val;
        }
        // Add the sub-array values to the original array,
        // so that they can handled recursively.
        $array = $build + $array;
        continue;
      }
      $result[$key] = $value;
    }

    return array_combine(array_keys($result), array_keys($result));
  }

}
