<?php

/**
 * @file
 * Contains Drupal\clutch\Form\ClutchAPIForm.
 */

namespace Drupal\clutch\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\clutch\ComponentBuilder;
use Drupal\clutch\FormBuilder;

/**
 * Class clutchForm.
 *
 * @package Drupal\clutch\Form
 */
class ClutchAPIForm extends FormBase {

  private $component_builder;
  
  public function __construct() {
    $this->component_builder = new ComponentBuilder();
  }

   /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'clutch_api_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $create = $delete = $update = FALSE;
    
    $listing_bundles = '';
    $existing_bundles = $this->component_builder->getExistingBundles();
    $theme_array = $this->component_builder->getCustomTheme();
    if(!isset($theme_array)) {
      return drupal_set_message('You haven\'t set a theme yet! Please set a default theme and try again! Remember to use the custom theme from the generator.');
    }
    $theme_path = array_values($theme_array)[0];
    $components_dir = $theme_path . '/components/';
    if(!is_dir($components_dir)) {
      return drupal_set_message('Your theme is missing the components directory. Please update and try again!');
    }
    $components_dir = scandir($theme_path . '/components/');
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

    // $to_update_bundles = $this->component_builder->getNeedUpdateComponents($match_bundles);
    // if(count($to_update_bundles) > 0){
    //   $to_update_bundles['select_all'] = 'Select All';
    // }

    // foreach($to_update_bundles as $bundle => $label) {
    //   $to_update_bundles[$bundle] = $label . ' (This bundle has changes! Need to delete and create a new one)';
    // }

    foreach($existing_bundles as $bundle) {
      $listing_bundles .= '<li>'.$bundle.'</li>';
    }

    // retrieve bundles

    if ($to_create_bundles){
      $form['new_bundles_wrapper'] = array(
        '#type' => 'details',
        '#prefix' => '<div class="action new-bundles">',
        '#suffix' => '</div>',
        '#title' => 'New Bundles in template',
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
        '#title' => 'Deleted Bundles in template',
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
    // if ($to_update_bundles){
    //   $form['update_bundles_wrapper'] = array(
    //     '#type' => 'details',
    //     '#prefix' => '<div class="action update-bundles">',
    //     '#suffix' => '</div>',
    //     '#title' => 'Update Bundles in template',
    //     '#open' => TRUE,
    //   );

    //   $form['update_bundles_wrapper']['update-bundles'] = array(
    //     '#type' => 'checkboxes',
    //     // '#title' => t('Update Bundles in template'),
    //     '#options' => $to_update_bundles,
    //   );

    //   $form['update_bundles_wrapper']['update'] = array(
    //     '#type' => 'submit',
    //     '#value' => t('Update'),
    //     '#submit' => [[$this, 'updateComponents']],
    //     '#attributes' => array(
    //       'class' => array('button--primary'),
    //     ),
    //   );
    //   $update = TRUE;
    // }

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
    $this->component_builder->createEntitiesFromTemplate($bundles, 'component');
    drupal_set_message('Create Entity');
  }

  public function deleteComponents(array &$form, FormStateInterface $form_state) {
    $submission_values = $form_state->getValues();
    $bundles = array_filter(array_values($submission_values['delete-bundles']));
    if(in_array('select_all', $bundles)){
      array_pop($bundles);
    }
    $this->component_builder->deleteEntities($bundles);
    drupal_set_message('Delete Entity');
  }

  // public function updateComponents(array &$form, FormStateInterface $form_state) {
  //   $submission_values = $form_state->getValues();
  //   $bundles = array_filter(array_values($submission_values['update-bundles']));
  //   if(in_array('select_all', $bundles)){
  //     array_pop($bundles);
  //   }
  //   $this->component_builder->updateEntities($bundles);
  //   drupal_set_message('Update Entity');
  // }
}