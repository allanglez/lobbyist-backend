<?php

namespace Drupal\biz_webform_rest\Plugin\rest\resource;

use Drupal\webform\Entity\WebformSubmission;
use Drupal\webform\WebformSubmissionForm;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ModifiedResourceResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Drupal\biz_business_rules\Controller\BusinessRulesFunctions;
/**
 * Creates a resource for retrieving webform submission data.
 *
 * @RestResource(
 *   id = "biz_webform_rest_submission",
 *   label = @Translation("Webform Submission"),
 *   uri_paths = {
 *     "canonical" = "/webform_rest/{webform_id}/submission/{sid}"
 *   }
 * )
 */
class WebformSubmissionResource extends ResourceBase {

  /**
   * Retrieve submission data.
   *
   * @param string $webform_id
   *   Webform ID.
   *
   * @param int $sid
   *   Submission ID.
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   *   HTTP response object containing webform submission.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws HttpException in case of error.
   */
  public function get($webform_id, $sid) {

    if (empty($webform_id) || empty($sid)) {
      $errors = [
        'error' => [
          'message' => 'Both webform ID and submission ID are required.'
        ]
      ];
      return new ModifiedResourceResponse($errors);
    }

    // Load the webform submission.
    $webform_submission = WebformSubmission::load($sid);

    // Check for a submission.
    if (!empty($webform_submission)) {
      $submission_webform_id = $webform_submission->get('webform_id')->getString();

      // Check webform_id.
      if ($submission_webform_id == $webform_id) {

        // Grab submission data.
        $data = $webform_submission->getData();

        $response = [
          'entity' => $webform_submission,
          'data' => $data
        ];

        // Return the submission.
        return new ModifiedResourceResponse($response);
      }
    }

    throw new NotFoundHttpException(t("Can't load webform submission."));

  }
   /**
   * Update submission data.
   *
   * @param string $webform_id
   *   Webform ID.
   *
   * @param int $sid
   *   Submission ID.
   *
   * @param array $webform_data
   *   Webform field data.
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   *   HTTP response object containing webform submission.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws HttpException in case of error.
   */
  public function patch($webform_id, $sid, array $webform_data) {

    if (empty($webform_id) || empty($sid)) {
      $errors = [
        'error' => [
          'message' => 'Both webform ID and submission ID are required.'
        ]
      ];
      return new ModifiedResourceResponse($errors);
    }

    if (empty($webform_data)) {
      $errors = [
        'error' => [
          'message' => 'No data has been submitted.'
        ]
      ];
      return new ModifiedResourceResponse($errors);
    }

    // Load the webform submission.
    $webform_submission = WebformSubmission::load($sid);

    // Check for a submission.
    if (!empty($webform_submission)) {
      $submission_webform_id = $webform_submission->get('webform_id')->getString();
      $today = date("Y-m-d"); 
      $dates = BusinessRulesFunctions::getDatesCalendarEnd();
      // Check webform_id.
      if ($submission_webform_id == $webform_id) {
        if($today >= $dates['from'] && $today <= $dates['to'] && 
        $submission_webform_id  == 'add_a_lobbying_activity'  && isset($webform_data['status']) ){
          $webform_data['status'] = 'active';
        } 
        foreach ($webform_data as $field => $value) {
          $webform_submission->setElementData($field, $value);
        }

        $errors = WebformSubmissionForm::validateWebformSubmission($webform_submission);

        // Check there are no validation errors.
        if (!empty($errors)) {
          $errors = ['error' => $errors];
          return new ModifiedResourceResponse($errors);
        }
        else {
          $business_rules = new BusinessRulesFunctions;
          $module = $business_rules->module;
          $key = $business_rules->key;
          $mailManager = \Drupal::service('plugin.manager.mail');
          $user_id = $webform_submission->getOwnerId();
          $owner =  user_load($user_id);
          $mail = $owner->getEmail();
          $langcode = $owner->getPreferredLangcode();
          
          if(!$business_rules->isFirstActivity($submission_webform_id, $user_id, TRUE) &&  $webform_data['status'] == 'active' && in_array($submission_webform_id,['add_a_lobbying_activity','add_a_lobbying_activity_consulta'] )){
              $params_first['subject'] = t("First activity approved");
              $params_first['body'] = \Drupal::config('biz_business_rules.settings')->get('commissioner_approve_first_act');
              $result_commisioner = $mailManager->mail($module, $key, $mail, $langcode, $params_first, NULL, TRUE);
          }
          // Return submission ID.
          $webform_submission = WebformSubmissionForm::submitWebformSubmission($webform_submission);

          switch($submission_webform_id){
            case 'add_a_lobbying_activity':
               if($today >= $dates['from'] && $today <= $dates['to'] && isset($webform_data['user_uid']) && !empty($webform_data['user_uid'])) {
                $params['subject'] = t("Activity Updated");
                $params['body'] = \Drupal::config('biz_business_rules.settings')->get('in_house_update_before_end_calendar');
                $result = $mailManager->mail($module, $key, $mail, $langcode, $params, NULL, TRUE);
              }
            break;
            case 'add_a_lobbying_activity_consulta': 
              if(!isset($webform_data['start_date'])){
                  $webform_submission_data = WebformSubmission::load($webform_submission->id());
                  $webform_data = $webform_submission_data->getData();
              }
              $today = date("Y-m-d"); // Today
              $new_start_dates = BusinessRulesFunctions::getDatesContract($webform_data['start_date']);
              $from = $new_start_dates['from'];
              $to = $new_start_dates['to'];            
              if($today >= $from && $today <= $to && isset($webform_data['user_uid']) && !empty($webform_data['user_uid'])&& $webform_data['user_uid'] == $user_id  ){
                \Drupal::logger("biz_webform_rest")->notice('Send email consultant certify:' . $mail);
                $params['subject'] = t("Activity Updated");
                $params['body'] = \Drupal::config('biz_business_rules.settings')->get('consultant_certify');
                $result = $mailManager->mail($module, $key, $mail, $langcode, $params, NULL, TRUE);
              }
            break;
          }
        }

        // Return submission ID.
        return new ModifiedResourceResponse(['sid' => $webform_submission->id()]);
      }
    }

    throw new NotFoundHttpException(t("Can't load webform submission."));
  }

}
