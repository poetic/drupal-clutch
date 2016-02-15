<?php

/**
 * @file
 * Contains \Drupal\custom_page\Form\CustomPageForm.
 */

namespace Drupal\custom_page\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Custom page edit forms.
 *
 * @ingroup custom_page
 */
class CustomPageForm extends ContentEntityForm {
  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\custom_page\Entity\CustomPage */
    $form = parent::buildForm($form, $form_state);
    $entity = $this->entity;
    $form['metatags'] = array(
      '#type' => 'details',
      '#title' => 'Meta tags',
    );
    $form['metatags']['meta_title'] = $form['meta_title'];
    $form['metatags']['meta_description'] = $form['meta_description'];
    unset($form['meta_title']);
    unset($form['meta_description']);
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
        drupal_set_message($this->t('Created the %label Custom page.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Custom page.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.custom_page.canonical', ['custom_page' => $entity->id()]);
  }

}
