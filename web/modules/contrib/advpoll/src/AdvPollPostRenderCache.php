<?php

namespace Drupal\advpoll;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Security\TrustedCallbackInterface;

/**
 * Basically a copy of the original PollPostRenderCache class with factory-style
 * logic for the renderViewForm to select which poll type to display.
 */
class AdvPollPostRenderCache implements TrustedCallbackInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new PollPostRenderCache object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity manager service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * @inheritDoc
   */
  public static function trustedCallbacks() {
    return ['renderViewForm'];
  }

  /**
   * Callback for #post_render_cache; replaces placeholder with poll view form.
   *
   * @param int $id
   *   The poll ID.
   * @param string $view_mode
   *   The view mode the poll should be rendered with.
   * @param string $langcode
   *   The langcode in which the poll should be rendered.
   *
   * @return array
   *   A renderable array containing the poll form.
   */
  public function renderViewForm($id, $view_mode, $langcode = NULL) {
    /** @var \Drupal\poll\PollInterface $poll */
    $poll = $this->entityTypeManager->getStorage('poll')->load($id);

    if ($poll) {
      if ($langcode && $poll->hasTranslation($langcode)) {
        $poll = $poll->getTranslation($langcode);
      }

      $form_object = \Drupal::service('class_resolver')->getInstanceFromDefinition('Drupal\advpoll\Form\ApprovalPollViewForm');
      $form_object->setPoll($poll);
      return \Drupal::formBuilder()->getForm($form_object, \Drupal::request(), $view_mode);
    }
    else {
      return ['#markup' => ''];
    }
  }

}
