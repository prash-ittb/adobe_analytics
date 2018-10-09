<?php

namespace Drupal\adobe_analytics\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ReportSuiteForm.
 *
 * @package Drupal\adobe_analytics\Form
 */
class ReportSuiteForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'adobe_analytics.report_suite',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'report_suite_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('adobe_analytics.report_suite');

    $form['report_suites_mode'] = [
      '#required' => '1',
      '#key_type_toggled' => '0',
      '#description' => t('Select whether the s_code file is in Production (prod) or Development (dev) mode.'),
      '#default_value' => $config->get('report_suites_mode'),
      '#weight' => '0',
      '#type' => 'radios',
      '#options' => [
        'dev' => t('Development'),
        'prod' => t('Production'),
      ],
      '#title' => t('Adobe Analytics Configuration Mode:'),
    ];

    $form['report_suites'] = [
      '#weight' => '1',
      '#description' => t('Enter the Adobe Analytics Report Suites to use for each mode:'),
      '#type' => 'fieldset',
      '#title' => t('Report Suites'),
      '#collapsible' => '1',
      '#collapsed' => '0',
    ];
    $form['report_suites']['development_report_suites'] = [
      '#required' => '0',
      '#description' => t('A comma delimited list of all the development report suites'),
      '#weight' => '0',
      '#type' => 'textfield',
      '#title' => t('Development:'),
      '#default_value' => $config->get('development_report_suites'),
    ];
    $form['report_suites']['production_report_suites'] = [
      '#required' => '0',
      '#description' => t('A comma delimited list of all the production report suites.'),
      '#weight' => '1',
      '#type' => 'textfield',
      '#title' => t('Production'),
      '#default_value' => $config->get('production_report_suites'),
    ];
    $form['domains'] = [
      '#weight' => '2',
      '#description' => t('Enter the domains used by each mode:'),
      '#type' => 'fieldset',
      '#title' => t('Domains'),
      '#collapsible' => '1',
      '#collapsed' => '0',
    ];
    $form['domains']['development_domains'] = [
      '#required' => '0',
      '#description' => t('A comma delimited list of all the development domains this s_code is used for.'),
      '#weight' => '0',
      '#type' => 'textfield',
      '#title' => t('Development:'),
      '#default_value' => $config->get('development_domains'),
    ];
    $form['domains']['production_domains'] = [
      '#required' => '0',
      '#description' => t('A comma delimited list of all the production domains this s_code is used for.'),
      '#weight' => '1',
      '#type' => 'textfield',
      '#title' => t('Production:'),
      '#default_value' => $config->get('production_domains'),
    ];
    $form['site_section'] = [
      '#weight' => '3',
      '#description' => t('Enter the Site Section values:'),
      '#type' => 'fieldset',
      '#title' => t('Site Section'),
      '#collapsible' => '1',
      '#collapsed' => '0',
    ];
    $form['site_section']['site_section_prefix'] = [
      '#required' => '0',
      '#description' => t("A prefix for all the returned values. All the returned values for s.channel and s.prop1 – 4 will have this prefix in the string. Example: if 'prefix' is set to 'LIPITOR' s.channel value would be 'LIPITOR>folder1'."),
      '#weight' => '0',
      '#type' => 'textfield',
      '#title' => t('Prefix:'),
      '#default_value' => $config->get('site_section_prefix'),
    ];
    $form['site_section']['site_section_delimiter'] = [
      '#required' => '0',
      '#description' => t('The delimiter to use to delimit the values returned.'),
      '#weight' => '1',
      '#type' => 'textfield',
      '#title' => t('Delimiter:'),
      '#default_value' => $config->get('site_section_delimiter'),
    ];
    $form['page_name'] = [
      '#weight' => '4',
      '#description' => t('Enter the Page Name values:'),
      '#title' => t('Page Name'),
      '#type' => 'fieldset',
      '#collapsible' => '1',
      '#collapsed' => '0',
    ];
    $form['page_name']['page_name_base'] = [
      '#required' => '0',
      '#description' => t("['path'|'title'] If this configuration setting is set to 'title', the returned value of s.pageName will be based on the DOM document.title object. If it is set to 'path', the returned value of s.pageName will be based on the page URL excluding the domain-name (location.pathname)."),
      '#weight' => '0',
      '#type' => 'textfield',
      '#title' => t('Base:'),
      '#default_value' => $config->get('page_name_base'),
    ];
    $form['page_name']['page_name_prefix'] = [
      '#required' => '0',
      '#description' => t('A prefix to prepend to all pageNames.'),
      '#weight' => '1',
      '#type' => 'textfield',
      '#title' => t('Prefix:'),
      '#default_value' => $config->get('page_name_prefix'),
    ];
    $form['page_name']['page_name_homepage'] = [
      '#required' => '0',
      '#description' => t('The pageName value to use for the home page of the site.'),
      '#weight' => '2',
      '#type' => 'textfield',
      '#title' => t('Homepage:'),
      '#default_value' => $config->get('page_name_homepage'),
    ];
    $form['page_name']['page_name_delimiter'] = [
      '#required' => '0',
      '#description' => t('The delimiter to use in the pageName string.'),
      '#weight' => '3',
      '#type' => 'textfield',
      '#title' => t('Delimiter:'),
      '#default_value' => $config->get('page_name_delimiter'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('adobe_analytics.report_suite')
      ->set('report_suites_mode', $form_state->getValue('report_suites_mode'))
      ->set('development_report_suites', $form_state->getValue('development_report_suites'))
      ->set('production_report_suites', $form_state->getValue('production_report_suites'))
      ->set('development_domains', $form_state->getValue('development_domains'))
      ->set('production_domains', $form_state->getValue('production_domains'))
      ->set('site_section_prefix', $form_state->getValue('site_section_prefix'))
      ->set('site_section_delimiter', $form_state->getValue('site_section_delimiter'))
      ->set('page_name_base', $form_state->getValue('page_name_base'))
      ->set('page_name_prefix', $form_state->getValue('page_name_prefix'))
      ->set('page_name_delimiter', $form_state->getValue('page_name_delimiter'))
      ->set('page_name_homepage', $form_state->getValue('page_name_homepage'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
