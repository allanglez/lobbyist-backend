<?php
namespace Drupal\biz_business_rules\Form;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
* Configure example settings for this site.
*/
class BusinessRulesSettingsForm extends ConfigFormBase {

  /** 
  * Config settings.
  *
  * @var string
  */
  const SETTINGS = 'biz_business_rules.settings';

  /** 
  * {@inheritdoc}
  */
  public function getFormId() {
    return 'biz_business_rules_admin_settings';
  }

  /** 
  * {@inheritdoc}
  */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  /** 
  * {@inheritdoc}
  */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(static::SETTINGS);
     $form['front_base_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t("Front: Base URL"),
      '#default_value' => $config->get('front_base_url'),
    ]; 
    
    $form['in_house'] = array(
      '#type' => 'fieldset',
      '#title' => t('In-house'), 
      '#collapsible' => TRUE, 
      '#collapsed' => FALSE,
    );

    $form['in_house']['in_house_add_new_lobbyist'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Mail to in-house lobbyist when added a new lobbyist'),
      '#default_value' => $config->get('in_house_add_new_lobbyist'),
    ];  

    $form['in_house']['in_house_add_new_activity'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Mail to in-house lobbyist when added a new activity'),
      '#default_value' => $config->get('in_house_add_new_activity'),
    ];  

    $form['in_house']['in_house_end_31_dec'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Mail to in-house lobbyist when the calendar year ends'),
      '#default_value' => $config->get('in_house_end_31_dec'),
    ];

    $form['in_house']['in_house_end_15_jan'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Mail to in-house lobbyist when is the end of the calendar year +15 days'),
      '#default_value' => $config->get('in_house_end_15_jan'),
    ];

    $form['in_house']['in_house_end_31_jan'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Mail to in-house lobbyist when is the end of the calendar year +31 days'),
      '#default_value' => $config->get('in_house_end_31_jan'),
    ];

/*
    $form['in_house']['in_house_update_activity_status'] = [
      '#type' => 'textarea',
      '#title' => $this->t("Mail to in-house lobbyist when updated activity's status"),
      '#default_value' => $config->get('in_house_update_activity_status'),
    ]; 


    $form['in_house']['in_house_update_organization_status'] = [
      '#type' => 'textarea',
      '#title' => $this->t("Mail to in-house updated organization's status"),
      '#default_value' => $config->get('in_house_update_organization_status'),
    ];
*/  
    $form['in_house']['in_house_update_before_end_calendar'] = [
      '#type' => 'textarea',
      '#title' => $this->t("Mail to in-house lobbyist when updated previous calendar year "),
      '#default_value' => $config->get('in_house_update_before_end_calendar'),
    ]; 
        
    $form['in_house']['in_house_first_activity'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Mail to in-house lobbyist when created their first activity'),
      '#default_value' => $config->get('in_house_first_activity'),
    ];
    
    $form['consultant'] = array(
      '#type' => 'fieldset',
      '#title' => t('Consultant'), 
      '#collapsible' => TRUE, 
      '#collapsed' => FALSE,
    );
    
    $form['consultant']['consultant_add_new_activity'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Mail to consultant lobbyist when added a new activity'),
      '#default_value' => $config->get('consultant_add_new_activity'),
    ];

/*
    $form['consultant']['consultant_update_activity_status'] = [
      '#type' => 'textarea',
      '#title' => $this->t("Mail to consultant lobbyist when updated activity's status "),
      '#default_value' => $config->get('consultant_update_activity_status'),
    ];
*/

    $form['consultant']['consultant_first_activity'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Mail to consultant lobbyist when created their first activity'),
      '#default_value' => $config->get('admin_consultant_first_activity'),
    ];
    
    $form['consultant']['consultant_certify'] = [
      '#type' => 'textarea',
      '#title' => $this->t("Mail to consultant when certify their activity "),
      '#default_value' => $config->get('consultant_certify'),
    ]; 

/*
    $form['consultant']['consultant_update_organization_status'] = [
      '#type' => 'textarea',
      '#title' => $this->t("Mail to consultant updated organization's status"),
      '#default_value' => $config->get('consultant_update_organization_status'),
    ];
*/

    $form['consultant']['consultant_prior_6_months'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Mail to consultant when the start date is prior to 6 Months from today'),
      '#default_value' => $config->get('consultant_prior_6_months'),
    ];

    $form['consultant']['consultant_prior_6_months_plus_15'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Mail to consultant when start date is prior 6 Months +15 days'),
      '#default_value' => $config->get('consultant_prior_6_months_plus_15'),
    ];

    $form['consultant']['consultant_prior_6_months_plus_30'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Mail to consultant when start date is prior 6 Months +30 days'),
      '#default_value' => $config->get('consultant_prior_6_months_plus_30'),
    ];

    $form['consultant']['consultant_end_contract'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Mail to consultant when the contract date ends'),
      '#default_value' => $config->get('consultant_end_contract'),
    ];

    $form['consultant']['consultant_end_contract_plus_15'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Mail to Consultant when end of contract date +15 days'),
      '#default_value' => $config->get('consultant_end_contract_plus_15'),
    ];

    $form['consultant']['consultant_end_contract_plus_30'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Mail to Consultant when end of contract date +30 days'),
      '#default_value' => $config->get('consultant_end_contract_plus_30'),
    ];
    
    $form['admin'] = array(
      '#type' => 'fieldset',
      '#title' => t('Administrator'), 
      '#collapsible' => TRUE, 
      '#collapsed' => FALSE,
    );
    
    $form['admin']['admin_end_consultant_activity'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Mail to admin when consultant non-compliance the sixth month'),
      '#default_value' => $config->get('admin_end_consultant_activity'),
    ];
    
    $form['admin']['admin_contract_end_date'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Mail to admin when the contract ends in the consultant activity'),
      '#default_value' => $config->get('admin_contract_end_date'),
    ];
    
    $form['admin']['admin_end_31_jan'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Mail to admin when the calendar year ends +31 days'),
      '#default_value' => $config->get('admin_end_31_jan'),
    ];
    
    $form['admin']['admin_in_house_first_activity'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Mail to admin for first in-house activity'),
      '#default_value' => $config->get('admin_in_house_first_activity'),
    ];
    
    $form['admin']['admin_consultant_first_activity'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Mail to admin for first consultant activity'),
      '#default_value' => $config->get('admin_consultant_first_activity'),
    ];
    
    $form['admin']['lobbyist_new_comment'] = [
      '#type' => 'textarea',
      '#title' => $this->t("Mail to admin when a comment is made in an activity by the lobbyist"),
      '#default_value' => $config->get('lobbyist_new_comment'),
    ]; 
    
    $form['lobbyist'] = array(
      '#type' => 'fieldset',
      '#title' => t('For both lobbyist type'), 
      '#collapsible' => TRUE, 
      '#collapsed' => FALSE,
    );

    $form['lobbyist']['admin_new_comment'] = [
      '#type' => 'textarea',
      '#title' => $this->t("Mail to lobbyist when a comment is made in an activity by the admin"),
      '#default_value' => $config->get('admin_new_comment'),
    ]; 
    
    $form['lobbyist']['commissioner_approve_first_act'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Mail to lobbyist when commissioner approve their first activity'),
      '#default_value' => $config->get('commissioner_approve_first_act'),
    ];
    
    $form['lobbyist']['wave_for_not_validate'] = [
      '#type' => 'checkbox',
      '#title' => $this->t("Not end the activities"),
      '#default_value' => $config->get('wave_for_not_validate'),
    ]; 
    
    
    return parent::buildForm($form, $form_state);
  }

  /** 
  * {@inheritdoc}
  */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Retrieve the configuration.
    $this->configFactory->getEditable(static::SETTINGS)
      // Set the submitted configuration setting. 
      ->set('front_base_url', $form_state->getValue('front_base_url'))
      ->set('in_house_add_new_lobbyist', $form_state->getValue('in_house_add_new_lobbyist'))
      ->set('in_house_add_new_activity', $form_state->getValue('in_house_add_new_activity'))
      ->set('in_house_end_31_dec', $form_state->getValue('in_house_end_31_dec'))
      ->set('in_house_end_15_jan', $form_state->getValue('in_house_end_15_jan'))
      ->set('in_house_end_31_jan', $form_state->getValue('in_house_end_31_jan'))
      ->set('in_house_update_activity_status', $form_state->getValue('in_house_update_activity_status'))
      ->set('in_house_update_organization_status', $form_state->getValue('in_house_update_organization_status'))
      ->set('in_house_update_before_end_calendar', $form_state->getValue('in_house_update_before_end_calendar'))
      ->set('in_house_first_activity', $form_state->getValue('in_house_first_activity'))
      
      ->set('consultant_prior_6_months', $form_state->getValue('consultant_prior_6_months'))
      ->set('consultant_prior_6_months_plus_15', $form_state->getValue('consultant_prior_6_months_plus_15'))
      ->set('consultant_prior_6_months_plus_30', $form_state->getValue('consultant_prior_6_months_plus_30'))
      ->set('consultant_end_contract', $form_state->getValue('consultant_end_contract'))
      ->set('consultant_end_contract_plus_15', $form_state->getValue('consultant_end_contract_plus_15'))
      ->set('consultant_end_contract_plus_30', $form_state->getValue('consultant_end_contract_plus_30'))
      ->set('consultant_add_new_activity', $form_state->getValue('consultant_add_new_activity'))
      ->set('consultant_first_activity', $form_state->getValue('consultant_first_activity'))

      ->set('consultant_certify', $form_state->getValue('consultant_certify'))
      ->set('consultant_update_activity_status', $form_state->getValue('consultant_update_activity_status'))
      ->set('consultant_update_organization_status', $form_state->getValue('consultant_update_organization_status'))
      
      ->set('commissioner_approve_first_act', $form_state->getValue('commissioner_approve_first_act'))
      ->set('lobbyist_new_comment', $form_state->getValue('lobbyist_new_comment'))
      ->set('wave_for_not_validate', $form_state->getValue('wave_for_not_validate'))
      
      ->set('admin_end_consultant_activity', $form_state->getValue('admin_end_consultant_activity'))
      ->set('admin_end_31_jan', $form_state->getValue('admin_end_31_jan'))
      ->set('admin_contract_end_date', $form_state->getValue('admin_contract_end_date'))
      ->set('admin_in_house_first_activity', $form_state->getValue('admin_in_house_first_activity'))
      ->set('admin_consultant_first_activity', $form_state->getValue('admin_consultant_first_activity'))
      ->set('admin_new_comment', $form_state->getValue('admin_new_comment'))
      ->save();
    parent::submitForm($form, $form_state);
  }
}