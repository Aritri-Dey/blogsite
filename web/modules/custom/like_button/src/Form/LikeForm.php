<?php
/**
 * @file
 * Contains \Drupal\like_button\Form\CartForm.
 */
namespace Drupal\like_button\Form;

use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\NodeInterface;

class LikeForm extends FormBase {

  /**
   * Vairbale to store object of RouteMatchInterface class.
   */
  protected $route;

  /**
   * Constructor to initialise RouteMatchInterface object variable.
   */
  public function __construct(RouteMatchInterface $route) {
    $this->route = $route;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('current_route_match'));
  }
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cart_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['submit'] = [
      // '#markup' => '<div class="like"></div>',
      '#type' => 'submit',
      '#value' => $this->t('Like'),
      '#suffix' => '<div id="msg">',
      // '#submit' => ['::showCount'],
      '#ajax' => [
        'callback' => '::showCount',
      ],
    ];
    return $form;
  }

  /**
   * Function to show message when add_to_cart button is clicked.
   * 
   *  @param $form
   *    Stores the form
   *  @param FormStateInterface $form_state
   *    Stores form_state values.
   */
  public function showCount($form, FormStateInterface $form_state) {
    // $ajax_res = new AjaxResponse();
    // $ajax_res->addCommand(new HtmlCommand('#msg', 'Product has been added to cart'));
    // return $ajax_res;
    dump('1');
    $node = $this->route->getParameter('node');
    if ($node instanceof NodeInterface) {
      $nid = $node->id();
    }
    dd($nid);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }
  
}
