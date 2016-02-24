<?php

/**
 * @file
 * Contains Drupal\clutch\Form\ClutchAPINodeForm.
 */

namespace Drupal\clutch\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\clutch\NodeBuilder;

/**
 * Class clutchForm.
 *
 * @package Drupal\clutch\Form
 */
class ClutchAPINodeForm extends FormBase {

   /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'clutch_api_node_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $listing_bundles = '';
    $existing_bundles = $this->getExistingBundles();
    $clutch_node_builder = new NodeBuilder();
    $theme_array = $clutch_node_builder->getFrontTheme();
    $theme_path = array_values($theme_array)[0];
    $components_dir = scandir($theme_path . '/nodes/');
    $bundles_from_theme_directory = array();
    foreach ($components_dir as $dir) {
      if (strpos($dir, '.') !== 0) {
        $bundles_from_theme_directory[str_replace('-', '_', $dir)] = ucwords(str_replace('-', ' ', $dir));
      }
    }

    // retrieve bundles need to create
    $to_create_bundles = array_diff_key($bundles_from_theme_directory, $existing_bundles);
    if(count($to_create_bundles) > 0){
      $to_create_bundles['select_all'] = 'Select All';
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


    if (!$create) {
      $form['upToDate'] = array(
        '#markup' => '<h1>All Nodes are Up To Date!</h1>'
      );
    }

    if(!empty($listing_bundles)) {
      $form['listing'] = array(
        '#type' => 'details',
        '#title' => 'Existing Nodes',
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
    $clutch_node_builder = new NodeBuilder();
    $clutch_node_builder->createEntitiesFromTemplate($bundles);
    // dpm('Create Entity');
  }

  public function getExistingBundles() {
    $bundles = \Drupal::entityQuery('node_type')->execute();
    foreach($bundles as $bundle => $label) {
      $bundles[$bundle] = ucwords(str_replace('_', ' ', $label));
    }
    return $bundles;
  }
}
