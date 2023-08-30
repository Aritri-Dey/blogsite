<?php 

/**
 * @file
 * Contains Drupal\task_module\Form\TaskForm
 */
namespace Drupal\task_module\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Database\Database;
use Drupal\Core\Form\FormBase;  
use Drupal\Core\Form\FormStateInterface; 

class TaskForm extends FormBase { 

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
      return 'task_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm($form, FormStateInterface $form_state) {

    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name:'),
      '#default_value' => $form_state->getValue('name'),
    ];

    $form['email'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Email:'),
      '#suffix' => '<div id="email-err"></div>',
      '#default_value' => $form_state->getValue('email'),
    ];

    $form['date_of_joining'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Date of joining:'),
        '#default_value' => $form_state->getValue('date_of_joining'),
      ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      // '#ajax' => [
      //   'callback' => '::validateAjax',
      // ],
    ];
    return $form;
  }
  
  /**
   * Function to validate form fields.
   */
  public function validateAjax($form, FormStateInterface $form_state) {
    $ajax_res = new AjaxResponse();
    $email = $form_state->getValue('email');
    $ajax_res->addCommand(new HtmlCommand('#email-err', ''));

    if (!preg_match('/^(?!\.)[a-zA-Z0-9_\+\-\.]+@[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,}$/', $email)) {
      $ajax_res->addCommand(new HtmlCommand('#email-err', 'Enter valid email id.'));
    }

    else {
      $this->submitForm($form, $form_state);
    }
    return $ajax_res;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(&$form, FormStateInterface $form_state) {
    $conn = Database::getConnection();
    $conn->insert('task_form')->fields([
        'name' => $form_state->getValue('name'),
        'email' => $form_state->getValue('email'),
        'date_of_joining' => $form_state->getValue('date_of_joining'),
        ])->execute();
  }
}
