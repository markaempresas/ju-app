<?php
/**
 * Base functions on Theme
 */
use Drupal\Core\Url;

function jutheme_preprocess(&$variables, $hook) {
  $theme = \Drupal::theme()->getActiveTheme();
  $base_url = Url::fromRoute('<front>', [], ['absolute' => TRUE])->toString();
  $variables['base_path'] = $base_url.'/'. $theme->getPath();
  $variables['logged_in'] = (\Drupal::currentUser()->isAnonymous()) ? false : true;
}
