<?php

namespace Drupal\biz_webforms\Entity;

use Drupal\webform\Entity\WebformSubmission;

class BizWebformSubmission extends WebformSubmission{
  
  public static function preCreate(EntityStorageInterface $storage, array &$values) {
     $access_rules_manager = \Drupal::service('webform.access_rules_manager');
    $values += [
      'status' => \Drupal::config('webform.settings')->get('settings.default_status'),
      'uid' => \Drupal::currentUser()->id(),
      'settings' => static::getDefaultSettings(),
      'access' => $access_rules_manager->getDefaultAccessRules(),
    ];

    // Convert boolean status to STATUS constant.
    if ($values['status'] === TRUE) {
      $values['status'] = WebformInterface::STATUS_OPEN;
    }
    elseif ($values['status'] === FALSE) {
      $values['status'] = WebformInterface::STATUS_CLOSED;
    }
    elseif ($values['status'] === NULL) {
      $values['status'] = WebformInterface::STATUS_SCHEDULED;
    }
    
    
    //parent::preCreate($storage, $values);
    if (empty($values['webform_id']) && empty($values['webform'])) {
      if (empty($values['webform_id'])) {
        throw new \Exception('Webform id (webform_id) is required to create a webform submission.');
      }
      elseif (empty($values['webform'])) {
        throw new \Exception('Webform (webform) is required to create a webform submission.');
      }
    }

    // Get temporary webform entity and store it in the static
    // WebformSubmission::$webform property.
    // This could be reworked to use \Drupal\Core\TempStore\PrivateTempStoreFactory
    // but it might be overkill since we are just using this to validate
    // that a webform's elements can be rendered.
    // @see \Drupal\webform\WebformEntityElementsValidator::validateRendering()
    // @see \Drupal\webform_ui\Form\WebformUiElementTestForm::buildForm()
    if (isset($values['webform']) && ($values['webform'] instanceof WebformInterface)) {
      $webform = $values['webform'];
      static::$webform = $values['webform'];
      $values['webform_id'] = $values['webform']->id();
    }
    else {
      /** @var \Drupal\webform\WebformInterface $webform */
      $webform = Webform::load($values['webform_id']);
      static::$webform = NULL;
    }

    // Get request's source entity parameter.
    /** @var \Drupal\webform\WebformRequestInterface $request_handler */
    $request_handler = \Drupal::service('webform.request');
    \Drupal::logger('Submission request_handler')->notice(json_encode($request_handler));
      
    $source_entity = $request_handler->getCurrentSourceEntity('webform');
    \Drupal::logger('Submission source_entity')->notice(json_encode($source_entity));
    $values += [
      'entity_type' => ($source_entity) ? $source_entity->getEntityTypeId() : NULL,
      'entity_id' => ($source_entity) ? $source_entity->id() : NULL,
    ];

    // Decode all data in an array.
    if (empty($values['data'])) {
      $values['data'] = [];
    }
    elseif (is_string($values['data'])) {
      $values['data'] = Yaml::decode($values['data']);
    }

    // Get default date from source entity 'webform' field.
    if ($values['entity_type'] && $values['entity_id']) {
      $source_entity = \Drupal::entityTypeManager()
        ->getStorage($values['entity_type'])
        ->load($values['entity_id']);

      /** @var \Drupal\webform\WebformEntityReferenceManagerInterface $entity_reference_manager */
      $entity_reference_manager = \Drupal::service('webform.entity_reference_manager');
      if ($webform_field_name = $entity_reference_manager->getFieldName($source_entity)) {
        if ($source_entity->$webform_field_name->target_id == $webform->id() && $source_entity->$webform_field_name->default_data) {
          $values['data'] += Yaml::decode($source_entity->$webform_field_name->default_data);
        }
      }
    }

    // Set default values.
    $current_request = \Drupal::requestStack()->getCurrentRequest();
    $content = $current_request->getContent();
    $content = json_decode($content);
    $values += [
      'in_draft' => FALSE,
      'uid' => $content->uid,//\Drupal::currentUser()->id(),
      'langcode' => \Drupal::languageManager()->getCurrentLanguage()->getId(),
      'token' => Crypt::randomBytesBase64(),
      'uri' => preg_replace('#^' . base_path() . '#', '/', $current_request->getRequestUri()),
      'remote_addr' => ($webform && $webform->hasRemoteAddr()) ? $current_request->getClientIp() : '',
    ];

    $webform->invokeHandlers(__FUNCTION__, $values);
    $webform->invokeElements(__FUNCTION__, $values);
  }
}