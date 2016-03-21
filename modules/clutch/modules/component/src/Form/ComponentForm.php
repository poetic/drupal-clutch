<?php

/**
 * @file
 * Contains \Drupal\component\Form\ComponentForm.
 */

namespace Drupal\component\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Component edit forms.
 *
 * @ingroup component
 */
class ComponentForm extends ContentEntityForm {
  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\component\Entity\Component */
    $form = parent::buildForm($form, $form_state);
    $entity = $this->entity;

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;
    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Component.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Component.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.component.canonical', ['component' => $entity->id()]);
  }

}
