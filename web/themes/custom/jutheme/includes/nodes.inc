<?php
/*/**
 * General theme functions
 * Node bundle
 */

function jutheme_preprocess_node(&$variables) {
  $node = $variables['node'];
  $options = [
    'absolute' => true,
  ];
  $variables['full_url'] = $node->toUrl()->setAbsolute()->toString();
}
