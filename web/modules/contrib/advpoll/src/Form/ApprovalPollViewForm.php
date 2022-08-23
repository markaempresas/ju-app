<?php

namespace Drupal\advpoll\Form;

use Drupal\poll\Form\PollViewForm;
use Drupal\Core\Form\BaseFormIdInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\poll\PollInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ApprovalPollViewForm
 *
 * @package Drupal\advpoll\Form
 */
class ApprovalPollViewForm extends PollViewForm implements BaseFormIdInterface {

  /**
   * Index for the write in option.
   */
  protected const writeInIndex = -1;

  /**
   * {@inheritdoc}
   */
  public function getBaseFormId() {
    return 'approval_poll_view_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'approval_poll_view_form_' . $this->poll->id();
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Request $request = NULL, $view_mode = 'full') {
    // Check start date.
    $startTimestamp = $this->getStartTimestamp();
    if ($startTimestamp && $startTimestamp > time()) {
      // Start date is in the future.
      $date = \Drupal::service('date.formatter')->format($startTimestamp, 'long');
      $form['start_date'] = [
        '#type' => 'markup',
        '#markup' => $this->t('This poll will open on @date.', ['@date' => $date]),
      ];
      return $form;
    }

    // Get poll form with choice or results.
    $form = parent::buildForm($form, $form_state, $request, $view_mode);

    // Add poll-view-form class for AJAX selectors from the Poll module.
    $form['#attributes']['class'][] = 'poll-view-form';
    $form['#attributes']['class'][] = 'poll-view-form-' . $this->poll->id();

    // If we render a form (not results), upgrade it.
    if (isset($form['choice'])) {
      $pollType = '';
      if ($this->poll->hasField('field_poll_type')) {
        $pollType = $this->poll->field_poll_type->value;
      }

      // Change template.
      $form['#theme'] = 'poll_vote__advanced';

      // Upgrade for non-classic types.
      if ($pollType) {
        switch ($pollType) {
          case 'approval':
            // Set checkboxes.
            $form['choice']['#type'] = 'checkboxes';
            break;
        }
      }

      $isMultipleChoice = ($form['choice']['#type'] == 'checkboxes');

      // Check write-in option.
      $isWriteInPoll = FALSE;
      if ($this->poll->hasField('field_writein')) {
        $isWriteInPoll = $this->poll->field_writein->value;
      }
      if ($isWriteInPoll) {
        // Add special option and the text field.
        $form['choice']['#options'][self::writeInIndex] = $this->t('Other (Write-in)');
        $writeInWrapperId = 'write-in-fieldset-wrapper-' . $this->poll->id();

        $form['write_in'] = [
          '#type' => 'fieldset',
          '#prefix' => '<div id="' . $writeInWrapperId . '">',
          '#suffix' => '</div>',
        ];
        if ($isMultipleChoice) {
          $form['write_in']['#states']['visible']['input[name="choice[' . self::writeInIndex . ']"]']['checked'] = TRUE;
        }
        else {
          $form['write_in']['#states']['visible']['input[name="choice"]']['value'] = self::writeInIndex;
        }

        $maxChoices = $this->getMaxChoices();

        // Gather the number of write-in in the form already.
        $numWriteIn = $form_state->get('num_writein');
        // We have to ensure that there is at least one field.
        if ($numWriteIn === NULL) {
          $numWriteIn = 1;
          $form_state->set('num_writein', $numWriteIn);
        }
        for ($i = 0; $i < $numWriteIn; $i++) {
          $form['write_in']['write_in_' . $i] = [
            '#type' => 'textfield',
          ];
        }

        // Check multiple write-in availability.
        $allowMultipleWriteIn = FALSE;
        if ($isMultipleChoice && $this->poll->hasField('field_writein_multiple') && !$this->poll->get('field_writein_multiple')->isEmpty()) {
          $allowMultipleWriteIn = $this->poll->get('field_writein_multiple')->value;
        }

        // Allow to add another write-in choice.
        if ($allowMultipleWriteIn && (empty($maxChoices) || $numWriteIn < $maxChoices)) {
          $form['write_in']['actions'] = [
            '#type' => 'actions',
          ];
          $form['write_in']['actions']['add'] = [
            '#type' => 'submit',
            '#value' => $this->t('Add'),
            '#submit' => ['::addOneWriteIn'],
            '#ajax' => [
              'callback' => '::addWriteInCallback',
              'wrapper' => $writeInWrapperId,
            ],
          ];
        }
      }
      // Hide write-in choices.
      if (!empty($form['choice']['#options'])) {
        $choiceKeys = array_keys($form['choice']['#options']);

        /** @var \Drupal\poll\PollChoiceInterface[] $choicesWriteIn */
        $choicesWriteIn = \Drupal::entityTypeManager()->getStorage('poll_choice')->loadByProperties([
          'id' => $choiceKeys,
          'field_writein' => TRUE,
        ]);
        if ($choicesWriteIn) {
          foreach ($choicesWriteIn as $choice) {
            unset($form['choice']['#options'][$choice->id()]);
          }
        }
      }
    }

    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function validateVote(array &$form, FormStateInterface $form_state) {
    parent::validateVote($form, $form_state);

    // Check multiple.
    $maxChoices = $this->getMaxChoices();
    if ($maxChoices) {
      // Check choices.
      $choices = array_filter($form_state->getValue('choice'));

      // Check multiple write-in.
      $writeInOptions = [];
      if (isset($choices[self::writeInIndex])) {
        $writeInOptions = $this->getWriteInOptions($form_state);
        // We don't need write-in checkbox because we use textfields.
        unset($choices[self::writeInIndex]);
      }

      if (count($choices) + count($writeInOptions) > $maxChoices) {
        $form_state->setErrorByName('choice', $this->t('Select up to @quantity @votes.', [
          '@quantity' => $maxChoices,
          '@votes' => $this->formatPlural($maxChoices, 'vote', 'votes'),
        ]));
      }
    }
  }

  /**
   * {@inheritDoc}
   */
  public function showResults(PollInterface $poll, FormStateInterface $form_state) {
    $showResults = parent::showResults($poll, $form_state);

    if (!$showResults) {
      // Check duration.
      $duration = $this->poll->getRuntime();
      if ($duration) {
        $startTimestamp = $this->getStartTimestamp();
        if (empty($startTimestamp)) {
          $startTimestamp = $this->poll->getCreated();
        }
        if ($startTimestamp + $duration < time()) {
          // End date is in the past.
          $showResults = TRUE;
        }
      }
    }

    return $showResults;
  }

  /**
   * Cancel vote submit function.
   *
   * @param array $form
   *   The previous form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function cancel(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\poll\PollVoteStorageInterface $vote_storage */
    $vote_storage = \Drupal::service('poll_vote.storage');
    $vote_storage->cancelVote($this->poll, $this->currentUser());
    \Drupal::logger('poll')->notice('%user\'s vote in Poll #%poll deleted.', array(
      '%user' => $this->currentUser()->id(),
      '%poll' => $this->poll->id(),
    ));
    \Drupal::messenger()->addMessage($this->t('Your vote was cancelled.'));

    // In case of an ajax submission, trigger a form rebuild so that we can
    // return an updated form through the ajax callback.
    if ($this->getRequest()->query->get('ajax_form')) {
      $form_state->setRebuild(TRUE);
    }
  }

  /**
   * Save a user's vote submit function.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function save(array $form, FormStateInterface $form_state) {
    $choices = $form_state->getValue('choice');

    // Make array from single vote to use the same code.
    if (!is_array($choices)) {
      $choices = [$choices => $choices];
    }

    $storagePollChoice = \Drupal::entityTypeManager()->getStorage('poll_choice');

    foreach($choices as $index => $choice) {
      if ($choice) {
        if ($index == self::writeInIndex) {
          // Add write-in options if exists.
          $writeInOptions = $this->getWriteInOptions($form_state);
          $pollOptions = $this->poll->getOptions();
          foreach ($writeInOptions as $writeInOption) {
            // Check duplicate.
            $chId = array_search($writeInOption, $pollOptions);
            if (empty($chId)) {
              // Create a new write-in option.
              $pollChoice = $storagePollChoice->create([
                'choice' => $writeInOption,
              ]);
              $pollChoice->set('field_writein', TRUE);
              $pollChoice->save();

              $this->poll->get('choice')->appendItem($pollChoice);
              $this->poll->save();

              $chId = $pollChoice->id();
            }
            $this->saveVote($chId);
          }
        }
        else {
          // Add other options.
          $chId = $index;
          $this->saveVote($chId);
        }
      }
    }

    \Drupal::messenger()->addMessage($this->t('Your vote has been recorded.'));

    if ($this->currentUser()->isAnonymous()) {
      // The vote is recorded so the user gets the result view instead of the
      // voting form when viewing the poll. Saving a value in $_SESSION has the
      // convenient side effect of preventing the user from hitting the page
      // cache. When anonymous voting is allowed, the page cache should only
      // contain the voting form, not the results.
      $_SESSION['poll_vote'][$form_state->getValue('poll')->id()] = $form_state->getValue('choice');
    }

    // In case of an ajax submission, trigger a form rebuild so that we can
    // return an updated form through the ajax callback.
    if ($this->getRequest()->query->get('ajax_form')) {
      $form_state->setRebuild(TRUE);
    }

    // No explicit redirect, so that we stay on the current page, which might
    // be the poll form or another page that is displaying this poll, for
    // example as a block.
  }

  /**
   * Callback for ajax-enabled buttons.
   *
   * Selects and returns the fieldset with the write-in in it.
   */
  public function addWriteInCallback(array &$form, FormStateInterface $form_state) {
    return $form['write_in'];
  }

  /**
   * Submit handler for the "add-one-more" button.
   *
   * Increments the max counter and causes a rebuild.
   */
  public function addOneWriteIn(array &$form, FormStateInterface $form_state) {
    $numWriteIn = $form_state->get('num_writein');
    $form_state->set('num_writein', $numWriteIn + 1);
    $form_state->setRebuild();
  }

  /**
   * Get max choices for the poll or 0.
   *
   * @return int
   */
  protected function getMaxChoices() {
    $maxChoices = 0;
    if ($this->poll->hasField('field_number_of_votes') && !$this->poll->get('field_number_of_votes')->isEmpty()) {
      $maxChoices = $this->poll->get('field_number_of_votes')->value;
    }

    return $maxChoices;
  }

  /**
   * Get list of non-empty write-in options.
   *
   * @param  \Drupal\Core\Form\FormStateInterface  $form_state
   *
   * @return array
   */
  protected function getWriteInOptions(FormStateInterface $form_state) {
    $numWriteIn = $form_state->get('num_writein');
    $writeInOptions = [];
    for ($i = 0; $i < $numWriteIn; $i++) {
      $value = trim($form_state->getValue('write_in_' . $i));
      if ($value) {
        $writeInOptions[] = $value;
      }
    }

    return $writeInOptions;
  }

  /**
   * Get start timestamp.
   *
   * @return int
   */
  protected function getStartTimestamp() {
    $startTimestamp = 0;
    if ($this->poll->hasField('field_start_date') && !$this->poll->get('field_start_date')->isEmpty()) {
      /** @var \Drupal\Core\Datetime\DrupalDateTime $startDateTime */
      $startDateTime = $this->poll->get('field_start_date')->date;
      $startTimestamp = $startDateTime->getTimestamp();
    }

    return $startTimestamp;
  }

  /**
   * Save vote.
   *
   * @param int $chId
   *   Choice ID.
   */
  protected function saveVote($chId) {
    $options = array();
    $options['chid'] = $chId;
    $options['uid'] = $this->currentUser()->id();
    $options['pid'] = $this->poll->id();
    $options['hostname'] = \Drupal::request()->getClientIp();
    $options['timestamp'] = time();

    /** @var \Drupal\poll\PollVoteStorage $voteStorage */
    $voteStorage = \Drupal::service('poll_vote.storage');
    $voteStorage->saveVote($options);
  }
}
