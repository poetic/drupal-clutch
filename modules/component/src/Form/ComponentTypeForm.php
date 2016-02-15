<?php

/**
 * @file
 * Contains \Drupal\component\Form\ComponentTypeForm.
 */

namespace Drupal\component\Form;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ComponentTypeForm.
 *
 * @package Drupal\component\Form
 */
class ComponentTypeForm extends EntityForm {
  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $component_type = $this->entity;
    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $component_type->label(),
      '#description' => $this->t("Label for the Component type."),
      '#required' => TRUE,
    );

    $form['id'] = array(
      '#type' => 'machine_name',
      '#default_value' => $component_type->id(),
      '#machine_name' => array(
        'exists' => '\Drupal\component\Entity\ComponentType::load',
      ),
      '#disabled' => !$component_type->isNew(),
    );

    /* You will need additional form elements for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $component_type = $this->entity;
    $status = $component_type->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Component type.', [
          '%label' => $component_type->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Component type.', [
          '%label' => $component_type->label(),
        ]));
    }
    $form_state->setRedirectUrl($component_type->urlInfo('collection'));
  }

}
