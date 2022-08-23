<?php
/*/**
 * General theme functions
 * Node and Taxonomy bundle
 */

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Url;
use Drupal\Component\Render\FormattableMarkup;
use Drupal\Component\Utility\Xss;

function jutheme_theme_suggestions_taxonomy_term_alter(&$suggestions, $vars, $hook) {
  $term = $vars['elements']['#taxonomy_term'];
  $parent = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadParents($term->id());
  $suggestions[] = 'taxonomy_term__' . $term->bundle() . '__' . $vars['elements']['#view_mode'];
  $isChildren = $parent ? 'subterm' : 'parent';
  $suggestions[] = 'taxonomy_term__' . $term->bundle()
                    . '__'
                    . $vars['elements']['#view_mode']
                    .'_'
                    . $isChildren;
}
