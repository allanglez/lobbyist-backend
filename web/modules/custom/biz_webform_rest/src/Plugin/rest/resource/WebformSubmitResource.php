<?php

namespace Drupal\biz_webform_rest\Plugin\rest\resource;

use Drupal\webform\Entity\Webform;
use Drupal\webform\WebformSubmissionForm;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ModifiedResourceResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Drupal\biz_business_rules\Controller\BusinessRulesFunctions;

/**
 * Creates a resource for submitting a webform.
 *
 * @RestResource(
 *   id = "biz_webform_rest_submit",
 *   label = @Translation("Webform Submit"),
 *   uri_paths = {
 *     "canonical" = "/webform_rest/submit",
 *     "https://www.drupal.org/link-relations/create" = "/webform_rest/submit"
 *   }
 * )
 */
class WebformSubmitResource extends ResourceBase {
  /**
   * Responds to entity POST requests and saves the new entity.
   *
   * @param array $webform_data
   *   Webform field data and webform ID.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The HTTP response object.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws HttpException in case of error.
   */
  public function post(array $webform_data) {
    $webform_id = $webform_data['webform_id'];
    // Basic check for webform ID.
    if (empty($webform_data['webform_id'])) {
      throw new BadRequestHttpException("Missing requred webform_id value.");
    }

    // Convert to webform values format.
    $values = [
      'webform_id' => $webform_id,
      'entity_type' => NULL,
      'entity_id' => NULL,
      'in_draft' => FALSE,
      'uri' => '/webform/' . $webform_id . '/api',
    ];

    $values['data'] = $webform_data;

    // Don't submit webform ID.
    unset($values['data']['webform_id']);

    // Check for a valid webform.
    $webform = Webform::load($values['webform_id']);
    if (!$webform) {
      throw new BadRequestHttpException('Invalid webform_id value.');
    }

    // Check webform is open.
    $is_open = WebformSubmissionForm::isOpen($webform);

    if ($is_open === TRUE) {
      // Validate submission.
      $errors = WebformSubmissionForm::validateFormValues($values);

      // Check there are no validation errors.
      if (!empty($errors)) {
        return new ModifiedResourceResponse([
          'message' => 'Submitted Data contains validation errors.',
          'error'   => $errors,
        ], 400);
      }
      else {
        // Return submission ID.
        $new_user = isset($values["data"])&& isset( $values["data"]["uid"]) ? $values["data"]["uid"] : 0;
        $webform_submission = WebformSubmissionForm::submitFormValues($values);
        $webform_user = $webform_submission->getOwnerId();
        $business_rules = new BusinessRulesFunctions;
        $module = $business_rules->module;
        $key = $business_rules->key;
        $owner = user_load($new_user);
        $langcode = $owner->getPreferredLangcode();
        $first_activity = FALSE;
        if(empty($webform_user) && !empty($new_user)){
          $webform_submission->setOwnerId($new_user);
          $webform_submission->save();
          $mailManager = \Drupal::service('plugin.manager.mail');
          $webform_activity_ids= ['add_a_lobbying_activity_consulta', 'add_a_lobbying_activity'];
          if(in_array($webform_id,$webform_activity_ids) && BusinessRulesFunctions::isFirstActivity($webform_id, $new_user)){
             \Drupal::logger("biz_business_rules")->notice("This is the first activity for this user");
            //Send an email to notify the admin
            switch($webform_id){
              case 'add_a_lobbying_activity':
                $body_admin = \Drupal::config('biz_business_rules.settings')->get('admin_in_house_first_activity');
                $body_lobbyist = \Drupal::config('biz_business_rules.settings')->get('in_house_first_activity');
              break;
              case 'add_a_lobbying_activity_consulta':
                $body_admin = \Drupal::config('biz_business_rules.settings')->get('admin_consultant_first_activity');
                $body_lobbyist = \Drupal::config('biz_business_rules.settings')->get('consultant_first_activity');
              break;
            }
            $all_users = BusinessRulesFunctions::get_all_mail_from_role('role_administrator');
            $params['subject'] = t('New activity added');
            $params['body'] = $body_admin;
            foreach($all_users as $user){
              $result = $mailManager->mail($module, $key, $user['mail'], $user['langcode'], $params, NULL, TRUE);
            }
            $first_activity = TRUE;
            $params['body'] = $body_lobbyist;
            $result = $mailManager->mail($module, $key, $owner->getEmail(), $langcode, $params, NULL, TRUE);
          }

          if(in_array($webform_id,$webform_activity_ids) && BusinessRulesFunctions::isFirstActivity($webform_id, $new_user, TRUE)){
            \Drupal::logger("biz_business_rules")->notice("Updated activity's status, the user already have an activity with 'Active' status");
            //change status to "Active"
            $submission_data = $webform_submission->getData();
            $submission_data['status'] = 'active';
            $webform_submission->setData($submission_data);
            $webform_submission->save();
          }
          
          
          if(!$first_activity || $webform_id == 'add_new_in_house_lobbyist'){
            //Send email to the owner ""you have submitted your activity""
            if(in_array($webform_id,$webform_activity_ids)){
              $emails[] = $owner->getEmail();
            }
            $roles = $owner->getRoles();
            $organization_name = "";
            $person = "";
            if(in_array('in_house_lobbyist', $roles)){
              $organization_name = $owner->get('field_legal_organization')->getValue()['0']['value'];
              $person = $owner->get('field_first_name')->value .' '. $owner->get('field_last_name')->value;
            }elseif(in_array('consultant_lobbyist', $roles)){
              $organization_name = $owner->get('field_first_name_consultant_')->value  .' '. $owner->get('field_last_name_consultant_')->value;
            }
            
            //Apply business rules
            switch($webform_id){
              //Add new in-house activity
              case 'add_a_lobbying_activity':
                $body = \Drupal::config('biz_business_rules.settings')->get('in_house_add_new_activity');
                $params['subject'] = t('New activity added');
                $params['body'] = $body;
              break;
              //Add new consultant activity
              case 'add_a_lobbying_activity_consulta':
                $body = \Drupal::config('biz_business_rules.settings')->get('consultant_add_new_activity');
                $params['subject'] = t('New activity added');
                $params['body'] = $body;
              break;
              //Add new in-house lobbyist 
              case 'add_new_in_house_lobbyist':
                $body = \Drupal::config('biz_business_rules.settings')->get('in_house_add_new_lobbyist');
                $body = str_replace('{{organization}}', $organization_name, $body);
                $body = str_replace('{{person}}', $person, $body);
                $params['subject'] = t('New lobbyist added');
                $params['body'] = $body;
                if(isset($values["data"]["e_mail_address"]) && !empty($values["data"]["e_mail_address"])){
                  $emails[] = $values["data"]["e_mail_address"];
                }
              break;
            }
            
            $emails = implode(',', $emails);
            if(!empty($emails)){
              \Drupal::logger("WebformSubmitResource")->notice("Send mails: " . json_encode($emails));
              $result = $mailManager->mail($module, $key, $emails, $langcode, $params, NULL, TRUE);
            }
          }
        }
        return new ModifiedResourceResponse(['sid' => $webform_submission->id()]);
      }
    }
    else {
      throw new AccessDeniedHttpException('This webform is closed, or too many submissions have been made.');
    }
  }
    
}
