<?php
/**
 * Implements hook_preprocess_hook().
 *
 * @{inheritdoc}
 */
function jutheme_preprocess_paragraph__buttons_home(&$variables) {
  /** @var \Drupal\Core\Entity\EntityInterface[] */
  $buttons = $variables['paragraph']->field_button_item->referencedEntities();
  $variables['prep']['buttons'] = _render_home_buttons($buttons);

  unset($variables['content']['field_button_item']);
}

/**
 * Render home buttons
 *
 * @param \Drupal\Core\Entity\EntityInterface[] $buttons
 * @return array
 */
function _render_home_buttons($buttons) {

  $lang_code = \Drupal::service('language_manager')->getCurrentLanguage()->getId();
  $button_arr = [];

  foreach ($buttons as $button) {

    $button = $button->getTranslation($lang_code);

    /** @var \Drupal\Core\Field\FieldItemListInterface */
    $title = $button->field_button_title;
    $icon = $button->field_button_icon;
    $cta = $button->field_button_link;

    $button_arr[] = [
      'title' => $title->view('full'),
      'icon' => $icon->view('full'),
      'cta' => $cta->view('full')
    ];
  }

  return $button_arr;
}
