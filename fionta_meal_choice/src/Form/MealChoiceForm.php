<?php

namespace Drupal\fionta_meal_choice\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;

/**
 * Process HTML file content.
 *
 * @package Drupal\fionta_meal_choice.
 */
class MealChoiceForm extends FormBase  {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'fionta_meal_choice';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['user_name'] = array(
      '#type' => 'textfield',
      '#title' => t('Enter Your Name:'),
      '#required' => TRUE,
    );
    $form['user_mail'] = array(
      '#type' => 'email',
      '#title' => t('Enter Your Email Address:'),
      '#required' => TRUE,
    );
    $form['meal_type'] = array (
      '#type' => 'select',
      '#title' => ('Select Type of Meal:'),
      '#options' => array(
        'Meat' => t('Meat'),
		'Fish' => t('Fish'),
        'Vegetarian' => t('Vegetarian'),
      ),
      '#required' => TRUE,
    );
    $form['dietary_restrictions'] = array(
      '#type' => 'textfield',
      '#title' => t('Enter Dietary Restrictions:'),
    );
    $form['special_instructions'] = array(
      '#type' => 'textarea',
      '#title' => t('Enter Special instructions:'),
    );
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
      '#button_type' => 'primary',
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $connection = Database::getConnection();
    $connection->insert('fionta_meal_choice')->fields(
      array(
        'user_name' => $form_state->getValue('user_name'),
        'user_mail' => $form_state->getValue('user_mail'),
        'meal_type' => $form_state->getValue('meal_type'),
        'dietary_restrictions' => $form_state->getValue('dietary_restrictions'),
        'special_instructions' => $form_state->getValue('special_instructions'),
      )
    )->execute();

    if(!empty($form_state->getValue('special_instructions'))) {
      $mailManager = \Drupal::service('plugin.manager.mail');
      $module = 'fionta_meal_choice';
      $key = 'meal_choice';
      $to = \Drupal::config('system.site')->get('mail');
      $params['message'] = "An order has been placed with special instructions. Here are the details: " . $form_state->getValue('special_instructions');
      $langcode = \Drupal::currentUser()->getPreferredLangcode();
      $send = true;

      $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
      if ($result['result'] !== true) {
        \Drupal::messenger()->addMessage(t('There was a problem sending your message and it was not sent.'), 'error');
      }
      else {
        \Drupal::messenger()->addMessage(t('Your message has been sent.'));
      }
	}
	\Drupal::messenger()->addMessage(t("Request has been submitted successfully"));
  }

}
