<?php
/**
 * Implements hook_preprocess_hook().
 *
 * @{inheritdoc}
 */
function jutheme_preprocess_paragraph(&$variables){
  $node = \Drupal::request()->attributes->get('node');
  if ($node):
    $variables['node_title'] = $node->getTitle();
  endif;
}
