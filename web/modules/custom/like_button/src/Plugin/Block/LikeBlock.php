<?php
/**
 * @file
 * Contains \Drupal\like_button\Plugin\Block\LikeBlock.
 */

namespace Drupal\like_button\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormInterface;

/**
 * Provides a 'Like' block.
 *
 * @Block(
 *   id = "like_block",
 *   admin_label = @Translation("Like block"),
 *   category = @Translation("Custom Like Block")
 * )
 */
class LikeBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Fetching custom form to be shown in the block.
    $form = \Drupal::formBuilder()->getForm('Drupal\like_button\Form\LikeForm');
    
    return $form;
   }
}
