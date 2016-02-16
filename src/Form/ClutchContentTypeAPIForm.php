<?php

/**
 * @file
 * Contains Drupal\clutch\Form\ClutchAPIForm.
 */

namespace Drupal\clutch\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\clutch\ClutchContentTypeBuilder;

/**
 * Class clutchForm.
 *
 * @package Drupal\clutch\Form
 */
class ClutchContentTypeAPIForm extends FormBase {

   /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'clutch_content_type_api_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $listing_bundles = '';
    $existing_bundles = $this->getExistingBundles();
    $clutch_builder = new ClutchContentTypeBuilder();
    $theme_array = $clutch_builder->getFrontTheme();
    $theme_path = array_values($theme_array)[0];
    $components_dir = scandir($theme_path . '/nodes/');
    $bundles_from_theme_directory = array();
    foreach ($components_dir as $dir) {
      if (strpos($dir, '.') !== 0) {
        $bundles_from_theme_directory[str_replace('-', '_', $dir)] = ucwords(str_replace('-', ' ', $dir));
      }
    }

    // retrieve bundles need to delete
    $to_delete_bundles = array_diff_key($existing_bundles, $bundles_from_theme_directory);
    if(count($to_delete_bundles) > 0){
      $to_delete_bundles['select_all'] = 'Select All';
    }

    // retrieve bundles need to create
    $to_create_bundles = array_diff_key($bundles_from_theme_directory, $existing_bundles);
    if(count($to_create_bundles) > 0){
      $to_create_bundles['select_all'] = 'Select All';
    }

    $match_bundles = array_intersect_key($existing_bundles, $bundles_from_theme_directory);

    $to_update_bundles = $clutch_builder->getNeedUpdateComponents($match_bundles);
    if(count($to_update_bundles) > 0){
      $to_update_bundles['select_all'] = 'Select All';
    }

    foreach($to_update_bundles as $bundle => $label) {
      $to_update_bundles[$bundle] = $label . ' (This bundle has changes! Need to delete and create a new one)';
    }

    foreach($existing_bundles as $bundle) {
      $listing_bundles .= '<li>'.$bundle.'</li>';
    }

    // retrieve bundles

    if ($to_create_bundles){
      $form['new_bundles_wrapper'] = array(
        '#type' => 'details',
        '#prefix' => '<div class="action new-bundles">',
        '#suffix' => '</div>',
        '#title' => 'New Nodes in template',
        '#open' => TRUE,
      );
      $form['new_bundles_wrapper']['new-bundles'] = array(
        '#type' => 'checkboxes',
        '#options' => $to_create_bundles,
      );
      $form['new_bundles_wrapper']['create'] = array(
        '#type' => 'submit',
        '#value' => t('Create'),
        '#submit' => [[$this, 'createComponents']],
        '#attributes' => array(
          'class' => array('button--primary'),
        ),
      );
      $create = TRUE;
    }

    if ($to_delete_bundles){
      $form['delete_bundles_wrapper'] = array(
        '#type' => 'details',
        '#prefix' => '<div class="action delete-bundles">',
        '#suffix' => '</div>',
        '#title' => 'Deleted Nodes in template',
        '#open' => TRUE,
      );

      $form['delete_bundles_wrapper']['delete-bundles'] = array(
        '#type' => 'checkboxes',
        '#options' => $to_delete_bundles,
      );

      $form['delete_bundles_wrapper']['delete'] = array(
        '#type' => 'submit',
        '#value' => t('Delete'),
        '#submit' => [[$this, 'deleteComponents']],
        '#attributes' => array(
          'class' => array('button--primary'),
        ),
      );
      $delete = TRUE;
    }
    if ($to_update_bundles){
      $form['update_bundles_wrapper'] = array(
        '#type' => 'details',
        '#prefix' => '<div class="action update-bundles">',
        '#suffix' => '</div>',
        '#title' => 'Update Nodes in template',
        '#open' => TRUE,
      );

      $form['update_bundles_wrapper']['update-bundles'] = array(
        '#type' => 'checkboxes',
        // '#title' => t('Update Bundles in template'),
        '#options' => $to_update_bundles,
      );

      $form['update_bundles_wrapper']['update'] = array(
        '#type' => 'submit',
        '#value' => t('Update'),
        '#submit' => [[$this, 'updateComponents']],
        '#attributes' => array(
          'class' => array('button--primary'),
        ),
      );
      $update = TRUE;
    }

    if (!$create && !$update && !$delete) {
      $form['upToDate'] = array(
        '#markup' => '<h1>All Bundles are Up To Date!</h1>'
      );
    }

    if(!empty($listing_bundles)) {
      $form['listing'] = array(
        '#type' => 'details',
        '#title' => 'Existing Bundles',
        '#markup' => '<ul>' . $listing_bundles . '</ul>',
      );
    }

    $form['#attached']['library'][] = 'clutch/clutch';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   *  Create component
   */
  public function createComponents(array &$form, FormStateInterface $form_state) {
    $submission_values = $form_state->getValues();
    $bundles = array_filter(array_values($submission_values['new-bundles']));
    if(in_array('select_all', $bundles)){
      array_pop($bundles);
    }
    $clutch_builder = new ClutchContentTypeBuilder();
    $clutch_builder->createEntitiesFromTemplate($bundles);
    // dpm('Create Entity');
  }

  public function deleteComponents(array &$form, FormStateInterface $form_state) {
    $submission_values = $form_state->getValues();
    $bundles = array_filter(array_values($submission_values['delete-bundles']));
    if(in_array('select_all', $bundles)){
      array_pop($bundles);
    }
    $clutch_builder = new ClutchContentTypeBuilder();
    $clutch_builder->deleteEntities($bundles);
    // dpm('Delete Entity');
  }

  public function updateComponents(array &$form, FormStateInterface $form_state) {
    $submission_values = $form_state->getValues();
    $bundles = array_filter(array_values($submission_values['update-bundles']));
    $clutch_builder = new ClutchContentTypeBuilder();
    if(in_array('select_all', $bundles)){
      array_pop($bundles);
    }
    $clutch_builder->updateEntities($bundles);
    // dpm('Update Entity');
  }

  public function getExistingBundles() {
    $bundles = \Drupal::entityQuery('component_type')->execute();
    foreach($bundles as $bundle => $label) {
      $bundles[$bundle] = ucwords(str_replace('_', ' ', $label));
    }
    return $bundles;
  }
}