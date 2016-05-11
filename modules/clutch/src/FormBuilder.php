<?php

/**
 * @file
 * Contains \Drupal\clutch\FormBuilder.
 */

namespace Drupal\clutch;

use Drupal\Core\Entity\Entity\EntityFormMode;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;
use Symfony\Component\DomCrawler\Crawler;
use Drupal\clutch\clutchBuilder;
use Drupal\contact\Entity\ContactForm;

/**
 * Class FormBuilder.
 *
 * @package Drupal\clutch\Controller
 */
class FormBuilder extends ClutchBuilder{

  public function getHTMLTemplate($template) {
    $theme_array = $this->getCustomTheme();
    $theme_path = array_values($theme_array)[0];
    return $this->twig_service->loadTemplate($theme_path.'/components/'.$template.'/'.$template.'.html.twig')->render(array());
  }

  public function collectFieldValues($entity, $field_definition) {
  }

  public function createBundle($bundle_info) {
    //TODO check if form already exists to reuse. always make new component type
    $this->createForm($bundle_info);
  }

  public function createForm(&$bundle_info) {
    if(!\Drupal::entityQuery('contact_form')->condition('id', $bundle_info['id'])->execute()){
      $form_type = ContactForm::create(array(
        'id' => $bundle_info['id'],
        'label' => ucwords(str_replace('_', ' ', $bundle_info['id'])),
        'type' => "contact_form",
      ))->save();
      \Drupal::logger('clutch:workflow')->notice('Create bundle @bundle',
      array(
        '@bundle' => $bundle_info,
        'form' => $form_type
      ));
      $this->createFields($bundle_info);
      $this->removeDefaultFormFields($bundle_info);
    }
  }

  public function createField($id, $field) {
    $field_storage = FieldStorageConfig::create([
      'field_name' => $field['field_name'],
      'entity_type' => 'contact_message',
      'type' => $field['field_type'],
      'cardinality' => 1,
      'custom_storage' => FALSE,
    ]);
    $field_storage->save();
    $field_instance = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => $id,
      'label' => $field['field_name'],
    ]);
    $field_instance->save();
  }

  public function getBundle(Crawler $crawler) {
    $bundle = $crawler->getAttribute('data-component');
    return $bundle;
  }

  public function createFields($bundle) {
    foreach($bundle['fields'] as $field) {
      $this->createField($bundle['id'], $field);
      $this->displayFormField($bundle, $field);
    }
  }

  public function removeDefaultFormFields($bundle_info) {
    entity_get_form_display('contact_message', $bundle_info['id'], 'default')
          ->removeComponent('name')
          ->save();

    entity_get_form_display('contact_message', $bundle_info['id'], 'default')
          ->removeComponent('email')
          ->save();

    entity_get_form_display('contact_message', $bundle_info['id'], 'default')
          ->removeComponent('subject')
          ->save();

    entity_get_form_display('contact_message', $bundle_info['id'], 'default')
          ->removeComponent('message')
          ->save();

    entity_get_form_display('contact_message', $bundle_info['id'], 'default')
          ->removeComponent('copy')
          ->save();

    entity_get_form_display('contact_message', $bundle_info['id'], 'default')
          ->removeComponent('mail')
          ->save();
  }

  public function displayFormField($bundle,$field) {
     entity_get_form_display('contact_message', $bundle['id'], 'default')
          ->setComponent($field['field_name'], array(
              'type' => $field['field_form_display'],
          ))
          ->save();
   }
}
