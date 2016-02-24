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

/**
 * Class ClutchBuilder.
 *
 * @package Drupal\clutch\Controller
 */
abstract class ClutchBuilder {
  /**
   * Load template using twig engine.
   * @param string $template
   *
   * @return string
   *   Return html string from template
   */
  abstract public function getHTMLTemplate($template);

  /**
   * Find and replace static value with dynamic value from created content
   *
   * @param $template, $component
   *   html string template from component
   *   component enityt
   *
   * @return
   *   render html for entity
   */
  public function findAndReplace($template, $component) {
    // TODO: find and replace info.
    $html = $this->getHTMLTemplate($template);
    $crawler = new HtmlPageCrawler($html);
    $html = $this->addQuickeditAttributeForBundle($crawler, $component);
    $html = $this->findAndReplaceValueForFields($crawler, $component);
    return $html;
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
    $bundle_layer = $crawler->filter('[data-bundle="'. $bundle .'"]');
    $bundle_layer->setAttribute('data-quickedit-entity-id', $quickedit)->addClass('contextual-region');

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
   * Add quickedit attribute for fields
   *
   * @param $crawler
   *   crawler instance of class Crawler - Symfony
   *
   * @return
   *   crawler instance with update html
   */
  public function findAndReplaceValueForFields($crawler, $component) {
    $fields = $this->prepareFields($component);
    foreach($fields as $field_name => $field) {
      $field_type = $crawler->filter('[data-field="'.$field_name.'"]')->getAttribute('data-type');
      if($field_type == 'link') {
        $crawler->filter('[data-field="'.$field_name.'"]')->addClass('quickedit-field')->setAttribute('data-quickedit-field-id', $field['quickedit'])->setAttribute('href', $field['content']['uri'])->text($field['content']['title'])->removeAttr('data-type')->removeAttr('data-form-type')->removeAttr('data-format-type')->removeAttr('data-field');
      }elseif($field_type == 'image') {
        $crawler->filter('[data-field="'.$field_name.'"]')->addClass('quickedit-field')->setAttribute('data-quickedit-field-id', $field['quickedit'])->setAttribute('src', $field['content']['url'])->removeAttr('data-type')->removeAttr('data-form-type')->removeAttr('data-format-type')->removeAttr('data-field');
      }else {
        // make sure delete/add other attributes
        $crawler->filter('[data-field="'.$field_name.'"]')->addClass('quickedit-field')->setAttribute('data-quickedit-field-id', $field['quickedit'])->setInnerHtml($field['content']['value'])->removeAttr('data-type')->removeAttr('data-form-type')->removeAttr('data-format-type')->removeAttr('data-field');
      }
    }
    return $crawler;
  }

  public function prepareFields($component) {
    $fields = array();
    $fields_definition = $component->getFieldDefinitions();
    foreach($fields_definition as $field_definition) {
     if(!empty($field_definition->getTargetBundle())) {
       if($field_definition->getType() == 'entity_reference_revisions') {
        // TODO: handle paragraph fields

       }else {
         $non_paragraph_field = $this->getFieldInfo($component, $field_definition);
         $key = key($non_paragraph_field);
         $fields[$key] = $non_paragraph_field[$key];
       }
     }
    }
    return $fields;
  }

  abstract public function getFieldInfo($component, $field_definition);

  /**
   * Clean up page after deleting component. 
   * Page still references non existing component therefore breaks rendering function
   *
   * @param $component_id
   *   id of component
   *
   * @return
   *   TODO
   */
  public function removeComponentOnPage($component_id) {
    $page_ids = \Drupal::entityQuery('custom_page')
        ->condition('associated_components.entity.id', $component_id)->execute();
    $pages = entity_load_multiple('custom_page', $page_ids);
    foreach($pages as $page) {
      $components = $page->get('associated_components')->getValue();
      $component_target_ids_array = array_column($components, 'target_id');
      if( in_array($component_id, $component_target_ids_array) ) {
       unset($component_target_ids_array[array_search($component_id, $component_target_ids_array)]);
      }
      $page->set('associated_components', $component_target_ids_array);
      $page->save();
    }
  }

  /**
   * Create entities from template
   *
   * @param $bundles
   *   array of bundles
   *
   * @return
   *   TODO
   */
  public function createEntitiesFromTemplate($bundles) {
    foreach($bundles as $bundle) {
      $this->createEntityFromTemplate(str_replace('_', '-', $bundle));
    }
  }

  /**
   * Create entity from template
   *
   * @param $template
   *   html string template from theme
   *
   * @return
   *   return entity object
   */
  public function createEntityFromTemplate($template) {
    $bundle_info = $this->prepareEntityInfoFromTemplate($template);
    $this->createBundle($bundle_info);
  }

  /**
   * Create bundle
   *
   * @param $bundle
   *   array of bundle info
   *
   * @return
   *   return bundle object
   */
  abstract public function createBundle($bundle_info);


  public function createFields($bundle) {
    foreach($bundle['fields'] as $field) {
      $this->createField($bundle['id'], $field);
    }
  }

  /**
   * create field and associate to bundle
   *
   * @param $bundle, $field
   *   bundle machine name
   *   array of field info
   *
   * @return
   *   TODO
   */
  abstract public function createField($bundle, $field);

  /**
   * Prepare entity to create bundle and content
   *
   * @param $template
   *   html string template from theme
   *
   * @return
   *   An array of entity info.
   */
  public function prepareEntityInfoFromTemplate($template) {
    $html = $this->getHTMLTemplate($template);
    $crawler = new HtmlPageCrawler($html);
    $entity_info = array();
    $bundle = $this->getBundle($crawler);
    $entity_info['id'] = $bundle;
    $fields = $this->getFields($crawler, $bundle);
    $entity_info['fields'] = $fields;
    return $entity_info;
  }

  /**
   * Look up bundle information from template
   *
   * @param $crawler
   *   crawler instance of class Crawler - Symfony
   *
   * @return
   *   An array of bundle info.
   */
  abstract public function getBundle(Crawler $crawler);

  /**
   * Look up fields information from template
   *
   * @param $crawler, $bundle
   *   crawler instance of class Crawler - Symfony
   *   bundle value
   *
   * @return
   *   An array of fields info.
   */
  public function getFields(Crawler $crawler, $bundle) {
    $fields = $crawler->filterXPath('//*[@data-field]')->each(function (Crawler $node, $i) use ($bundle) {
      $field_type = $node->extract(array('data-type'))[0];
      $field_name = $bundle . '_' . $node->extract(array('data-field'))[0];
      $field_form_display = $node->extract(array('data-form-type'))[0];
      $field_formatter = $node->extract(array('data-format-type'))[0];

      switch($field_type) {
        case 'link':
          $default_value['uri'] = $node->extract(array('href'))[0];
          $default_value['title'] = $node->extract(array('_text'))[0];
          break;
        case 'image':
          $default_value = $node->extract(array('src'))[0];
          break;
        default:
          $default_value = $node->getInnerHtml();
          break;
      }
      return array(
        'field_name' => $field_name,
        'field_type' => $field_type,
        'field_form_display' => $field_form_display,
        'field_formatter' => $field_formatter,
        'value' => $default_value,
      );
    });
    return $fields;
  }

  /**
   * Find bundles that need to be updated
   *
   * @param $bundles
   *   array of bundles
   *
   * @return
   *   An array bundles that need to be updated
   */
  public function getNeedUpdateComponents($bundles) {
    $need_to_update_bundles = array();
    foreach($bundles as $bundle => $label) {
      if($this->verifyIfBundleNeedToUpdate($bundle)) {
        $need_to_update_bundles[$bundle] = $label;
      }
    }
    return $need_to_update_bundles;
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

  public function getFieldInfoFromExistingBundle($field) {
    return array(
      'field_name' => $field->get('field_name'),
      'field_type' => $field->get('field_type'),
    );
  }
  /**
   * Get front end theme directory
   * @return 
   *  an array of theme namd and theme path
   */
  public function getCustomTheme() {
    $themes = system_list('theme');
    foreach($themes as $theme) {
      if($theme->origin !== 'core') {
        return [$theme->getName() => $theme->getPath()];
      } 
    }
  }
}