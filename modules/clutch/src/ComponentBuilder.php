<?php

/**
 * @file
 * Contains \Drupal\clutch\ComponentBuilder.
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
use Drupal\clutch\ClutchBuilder;
use Drupal\clutch\MenuBuilder;

/**
 * Class ComponentBuilder.
 *
 * @package Drupal\clutch\Controller
 */

class ComponentBuilder extends ClutchBuilder{

  /**
   *  {@inheritdoc}
   */
  public function getHTMLTemplate($template){
    $theme_array = $this->getCustomTheme();
    $theme_path = array_values($theme_array)[0];
    // $template name has the same name of directory that holds the template
    // pass null array to pass validation. we don't need to replace any variables. this only return 
    // the html string to we can parse and handle it
    return $this->twig_service->loadTemplate($theme_path.'/components/'.$template.'/'.$template.'.html.twig')->render(array());
  }

  /**
   *  {@inheritdoc}
   */
  public function collectFieldValues($component, $field_definition) {
    $bundle = $component->bundle();
    $field_name = $field_definition->getName();
    $field_language = $field_definition->language()->getId();
    $field_value = $component->get($field_name)->getValue();
    $field_type = $field_definition->getType();
    if($field_type == 'image' && !empty($field_value)) {
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

  /**
   * Create bundle
   *
   * @param $bundle_info
   *   array of bundle_info from template
   *
   * @return
   *   TODO
   */
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
      $this->createDefaultContentForEntity($bundle_info, 'component');
    }
  }

  /**
   * Create field and associated to bundle
   *
   * @param $bundle, $field
   *   bundle machine name
   *   array of field info from template
   *
   * @return
   *   TODO
   */
  public function createField($bundle, $field) {
    // since we are going to treat each field unique to each bundle, we need to
    // create field storage(field base)
    if($field['field_type'] == 'entity_reference_revisions') {
      $field_storage = FieldStorageConfig::create([
        'field_name' => $field['field_name'],
        'entity_type' => 'component',
        'type' => $field['field_type'],
        'cardinality' => -1,
        'custom_storage' => FALSE,
        'settings' => array(
          'target_type' => 'paragraph'
         ),
      ]);
    }else {
      $field_storage = FieldStorageConfig::create([
        'field_name' => $field['field_name'],
        'entity_type' => 'component',
        'type' => $field['field_type'],
        'cardinality' => 1,
        'custom_storage' => FALSE,
      ]);
    }

    $field_storage->save();

    // create field instance for bundle
    $field_instance = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => $bundle,
      'label' => str_replace('_', ' ', $field['field_name']),
    ]);

    $field_instance->save();


    if($field['field_type'] == 'entity_reference_revisions') {
      $paragraph_bundle = str_replace($bundle . '_', '', $field['field_name']);
      $handler_settings = $field_instance->getSetting('handler_settings');      
      $handler_settings['target_bundles'][$paragraph_bundle] = $paragraph_bundle;
      $handler_settings['target_bundles_drag_drop'][$paragraph_bundle]['enabled'] = TRUE;
      $field_instance->setSetting('handler_settings', $handler_settings);
      $field_instance->save();
    }

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

    $bundle = $crawler->getAttribute('data-component');
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

  /**
   * Add quickedit attribute for bundle
   *
   * @param $crawler
   *   crawler instance of class Crawler - Symfony
   *
   * @return
   *   crawler instance with update html
   */
  public function addQuickeditAttributeForBundle($crawler, $component) {
    $bundle = $component->bundle();
    $quickedit = 'component/'. $component->id();
    $bundle_layer = $crawler->filter('[data-component="'. $bundle .'"]');
    $bundle_layer->setAttribute(QE_ENTITY_ID, $quickedit)->addClass('contextual-region');

    $build_contextual_links['#contextual_links']['component'] = array(
      'route_parameters' =>array('component' => $component->id()),
      'metadata' => array('changed' => $component->getChangedTime()),
    );
    $contextual_links['contextual_links'] = array(
      '#type' => 'contextual_links_placeholder',
      '#id' => _contextual_links_to_id($build_contextual_links['#contextual_links']),
    );
    $render_contextual_links = render($contextual_links)->__toString();
    $bundle_layer->prepend($render_contextual_links);
    return $crawler;
  }

  /**
   * {@inheritdoc}
   */ 
  public function findAndReplace($template, $component, $view_mode = NULL) {
    $html = parent::findAndReplace($template, $component);
    $html = $this->addQuickeditAttributeForBundle($html, $component);
    return $html;
  }

  /**
   * Clean up page after deleting component. 
   * Page still references non existing component therefore breaks rendering function
   *
   * @param $entity_id
   *   id of component
   *
   * @return
   *   TODO
   */
  public function removeComponentOnPage($entity_id) {
    $page_ids = \Drupal::entityQuery('custom_page')
        ->condition('associated_components.entity.id', $entity_id)->execute();
    $pages = entity_load_multiple('custom_page', $page_ids);
    foreach($pages as $page) {
      $entitys = $page->get('associated_components')->getValue();
      $entity_target_ids_array = array_column($entitys, 'target_id');
      if( in_array($entity_id, $entity_target_ids_array) ) {
       unset($entity_target_ids_array[array_search($entity_id, $entity_target_ids_array)]);
      }
      $page->set('associated_components', $entity_target_ids_array);
      $page->save();
    }
  }

  /**
   * verify bundle that need to be updated
   *
   * @param $bundle
   *   bundle machine name
   *
   * @return
   *   TRUE or FALSE
   */
  public function verifyIfBundleNeedToUpdate($bundle) {
    $template = str_replace('_', '-', $bundle);
    $existing_bundle_fields_definition = \Drupal::entityManager()->getFieldDefinitions('component', $bundle);
    $existing_bundle_info = array();
    $existing_bundle_info['id'] = $bundle;
    foreach($existing_bundle_fields_definition as $field_definition) {
      if(!empty($field_definition->getTargetBundle())) {
        $existing_bundle_info['fields'][] = $this->getFieldInfoFromExistingBundle($field_definition);
      }
    }
    $bundle_info_from_template = $this->prepareEntityInfoFromTemplate($template);
    return $this->compareInfo($existing_bundle_info, $bundle_info_from_template);
  }

  /**
   * Compare information between existing bundle and bundle from template
   *
   * @param $existing_bundle, $bundle_from_template
   *   array of info from existing bundle
   *   array of info from template
   *
   * @return
   *   TRUE or FALSE
   */
  public function compareInfo($existing_bundle, $bundle_from_template) {
    $count_fields_from_existing_bundle = count($existing_bundle['fields']);
    $count_fields_from_bundle_from_template = count($bundle_from_template['fields']);
    sort($existing_bundle['fields']);
    sort($bundle_from_template['fields']);

    // check if match number of fields
    if($count_fields_from_existing_bundle != $count_fields_from_bundle_from_template) {
      return TRUE;
    } else {
      // check if match field type
      for($i = 0; $i < $count_fields_from_existing_bundle; $i++) {
        if($existing_bundle['fields'][$i]['field_name'] != $bundle_from_template['fields'][$i]['field_name']) {
          return TRUE;
        }elseif($existing_bundle['fields'][$i]['field_type'] != $bundle_from_template['fields'][$i]['field_type']) {
          return TRUE;
        }
      }
      return FALSE;
    }
  }

  /**
   * Get field name and type from field definition
   *
   * @param $field
   *   array of info from field definition
   *
   * @return
   *   an array that contain field name and field type
   */
  public function getFieldInfoFromExistingBundle($field) {
    return array(
      'field_name' => $field->get('field_name'),
      'field_type' => $field->get('field_type'),
    );
  }
}