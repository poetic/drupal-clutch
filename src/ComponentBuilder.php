<?php

/**
 * @file
 * Contains \Drupal\clutch\ClutchBuilder.
 */

namespace Drupal\clutch;

use Drupal\component\Entity\Component;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;
use Drupal\file\Entity\File;
use Drupal\file\Plugin\Field\FieldType\FileItem;
use Drupal\Core\StreamWrapper\StreamWrapperInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\CssSelector\CssSelector;
use Wa72\HtmlPageDom\HtmlPageCrawler;
use Drupal\clutch\clutchBuilder;

/**
 * Class ClutchBuilder.
 *
 * @package Drupal\clutch\Controller
 */

class ComponentBuilder extends ClutchBuilder{

  public function getHTMLTemplate($template){
    $theme_array = $this->getCustomTheme();
      $theme_path = array_values($theme_array)[0];
      $twig_service = \Drupal::service('twig');
      // $template name has the same name of directory that holds the template
      // pass null array to pass validation. we don't need to replace any variables. this only return 
      // the html string to we can parse and handle it
      return $twig_service->loadTemplate($theme_path.'/components/'.$template.'/'.$template.'.html.twig')->render(array());

  }

  public function getFieldInfo($component, $field_definition) {
    $bundle = $component->bundle();
    $field_name = $field_definition->getName();
    $field_language = $field_definition->language()->getId();
    $field_value = $component->get($field_name)->getValue();
    $field_type = $field_definition->getType();
    if($field_type == 'image') {
      $file = File::load($field_value[0]['target_id']);
      $url = file_create_url($file->get('uri')->value);
      $field_value[0]['url'] = $url;
    }

    $field_attribute = 'component/' . $component->id() . '/' . $field_name . '/' . $field_language . '/full';
    return [str_replace($bundle.'_', '', $field_name) => array(
      'content' => $field_value[0],
      'quickedit' => $field_attribute,
    )];
  }

  public function createBundle($bundle_info) {
    if(entity_load('component_type', $bundle_info['id'])) {
      \Drupal::logger('clutch:workflow')->notice('Bundle exists. Need to update bundle.');
      drupal_set_message('Cannot create bundle. Bundle exists. Need to update bundle.');
    }else {
      $bundle_label = ucwords(str_replace('_', ' ', $bundle_info['id']));
      $bundle = entity_create('component_type', array(
        'id' => $bundle_info['id'],
        'label' => $bundle_label,
        'revision' => FALSE,
      ));
      $bundle->save();
      \Drupal::logger('clutch:workflow')->notice('Create bundle @bundle',
        array(
          '@bundle' => $bundle_label,
        ));
      $this->updateAssociatedComponents($bundle_info['id']);
      $this->createFields($bundle_info);
      $this->createComponentContent($bundle_info);
    }
  }

  public function createField($bundle, $field) {
    // since we are going to treat each field unique to each bundle, we need to
    // create field storage(field base)
    $field_storage = FieldStorageConfig::create([
      'field_name' => $field['field_name'],
      'entity_type' => 'component',
      'type' => $field['field_type'],
      // 'cardinality' => $field_info['cardinality'],
      'cardinality' => 1,
      'custom_storage' => FALSE,
    ]);

    $field_storage->save();

    // create field instance for bundle
    $field_instance = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => $bundle,
      'label' => str_replace('_', ' ', $field['field_name']),
    ]);

    $field_instance->save();

    // Assign widget settings for the 'default' form mode.
    entity_get_form_display('component', $bundle, 'default')
      ->setComponent($field['field_name'], array(
        'type' => $field['field_form_display'],
      ))
      ->save();

    // Assign display settings for 'default' view mode.
    entity_get_display('component', $bundle, 'default')
      ->setComponent($field['field_name'], array(
        'label' => 'hidden',
        'type' => $field['field_formatter'],
      ))
      ->save();
     \Drupal::logger('clutch:workflow')->notice('Create field @field for bundle @bundle',
      array(
        '@field' => str_replace('_', ' ', $field['field_name']),
        '@bundle' => $bundle,
      ));
  }

  /**
   * {@inheritdoc}
   */
  public function createEntitiesFromTemplate($bundles) {
    parent::createEntitiesFromTemplate($bundles);
    entity_get_form_display('custom_page', 'custom_page', 'default')
      ->setComponent('associated_components', array(
        'type' => 'entity_reference_autocomplete',
      ))
      ->save();
  }

  /**
   *  {@inheritdoc}
   */
  public function getBundle(Crawler $crawler) {
    $bundle = $crawler->filter('*')->getAttribute('data-component');
    return $bundle;
  }


  /**
   * Get existing component types
   * @return
   *  an array of existing component types
   */
  public function getExistingBundles() {
    $bundles = \Drupal::entityQuery('component_type')->condition('id', ['component_view_reference'], 'NOT IN')->execute();
    foreach($bundles as $bundle => $label) {
      $bundles[$bundle] = ucwords(str_replace('_', ' ', $label));
    }
    return $bundles;
  }

  /**
   * Create default content for component
   *
   * @return
   *  a component object
   */
  public function createComponentContent($content) {
    $component = Component::create([
      'type' => $content['id'],
      'name' => ucwords(str_replace('_', ' ', $content['id'])),
    ]);
    $component->save();
    foreach($content['fields'] as $field) {
      if($field['field_type'] == 'image') {
        $settings['file_directory'] = 'components/[date:custom:Y]-[date:custom:m]';
        $image = File::create();
        $image->setFileUri($field['value']);
        $image->setOwnerId(\Drupal::currentUser()->id());
        $image->setMimeType('image/' . pathinfo($field['value'], PATHINFO_EXTENSION));
        $image->setFileName(drupal_basename($field['value']));
        $destination_dir = 'public://components';
        file_prepare_directory($destination_dir, FILE_CREATE_DIRECTORY);
        $destination = $destination_dir . '/' . basename($field['value']);
        $file = file_move($image, $destination, FILE_CREATE_DIRECTORY);

        $values = array(
          'target_id' => $file->id(),
        );

        $component->set($field['field_name'], $values);
      }else {
        $component->set($field['field_name'], $field['value']);
      }
    }
    $component->save();
    \Drupal::logger('clutch:workflow')->notice('Create content for bundle @bundle',
      array(
        '@bundle' => $content['id'],
      ));
  }

  /**
   * Associate field associated_components with new bundle
   *
   * @param $bundle
   *   bundle name
   *
   * @return
   *   TODO
   */
  public function updateAssociatedComponents($bundle) {
    $field_associated_components = FieldConfig::loadByName('custom_page', 'custom_page', 'associated_components');
    $handler_settings = $field_associated_components->getSetting('handler_settings');
    $handler_settings['target_bundles'][$bundle] = $bundle;
    $field_associated_components->setSetting('handler_settings', $handler_settings);
    $field_associated_components->save();
    \Drupal::logger('clutch:workflow')->notice('Add new target bundle @bundle for associated components field on Custom Page.',
      array(
        '@bundle' => $bundle,
      ));
  }

  /**
   * Update entities and bundles
   * Since we treate those as singlton, we just need to delete and create a new one
   *
   * @param $bundles
   *   array of bundles
   *
   * @return
   *   TODO
   */
  public function updateEntities($bundles) {
    $this->deleteEntities($bundles);
    $this->createEntitiesFromTemplate($bundles);
  }

  /**
   * Delete entities and bundles
   *
   * @param $bundles
   *   array of bundles
   *
   * @return
   *   TODO
   */
  public function deleteEntities($bundles) {
    foreach($bundles as $bundle) {
      $bundle_value = str_replace('-', '_', $bundle);
      $entity = \Drupal::entityQuery('component')
        ->condition('type', $bundle_value);
      $entity_array = $entity->execute();
      $entity_id = key($entity_array);
      if($entity_id) {
        $this->removeComponentOnPage($entity_id);
        entity_load('component', $entity_id)->delete();
      }
      entity_load('component_type', $bundle_value)->delete();
      entity_get_form_display('custom_page', 'custom_page', 'default')
        ->setComponent('associated_components', array(
          'type' => 'entity_reference_autocomplete',
        ))
        ->save();
    }
  }
}