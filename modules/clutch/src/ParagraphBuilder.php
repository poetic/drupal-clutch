<?php

/**
 * @file
 * Contains \Drupal\clutch\ParagraphBuilder.
 */

namespace Drupal\clutch;

use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;
use Drupal\file\Entity\File;
use Drupal\file\Plugin\Field\FieldType\FileItem;
use Drupal\Core\StreamWrapper\StreamWrapperInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\CssSelector\CssSelector;
use Wa72\HtmlPageDom\HtmlPageCrawler;
use Drupal\clutch\ClutchBuilder;

/**
 * Class ParagraphBuilder.
 *
 * @package Drupal\clutch\Controller
 */
class ParagraphBuilder extends ClutchBuilder{
  
  /**
   *  {@inheritdoc}
   */
  public function getHTMLTemplate($template) {

  }
  
  /**
   *  {@inheritdoc}
   */
  public function collectFieldValues($paragraph, $field_definition) {
    $bundle = $paragraph->bundle();
    $field_name = $field_definition->getName();
    $field_language = $field_definition->language()->getId();
    $field_value = $paragraph->get($field_name)->getValue();
    $field_type = $field_definition->getType();
    if(($field_type == 'image' && !empty($field_value)) || ($field_type == 'file' && !empty($field_value))) {
      $file = File::load($field_value[0]['target_id']);
      $url = file_create_url($file->get('uri')->value);
      $field_value[0]['url'] = $url;
    }

    $field_attribute = 'paragraph/' . $paragraph->id() . '/' . $field_name . '/' . $field_language . '/full';

    return [str_replace($bundle.'_', '', $field_name) => array(
      'content' => $field_value[0],
      'quickedit' => $field_attribute,
      'type' => $field_type,
    )];
  }
  
  /**
   *  {@inheritdoc}
   */
  public function getBundle(Crawler $crawler) {

  }
  
  /**
   *  {@inheritdoc}
   */
  public function createBundle($bundle_info) {
    if(entity_load('paragraphs_type', $bundle_info['id'])) {
      // TODO Handle update bundle
      \Drupal::logger('clutch:workflow')->notice('Bundle exists. Need to update bundle.');
    }else {
      $bundle_label = ucwords(str_replace('_', ' ', $bundle_info['id']));
      $paragraph_type = entity_create('paragraphs_type', array(
        'id' => $bundle_info['id'],
        'label' => $bundle_label
      ));
      $paragraph_type->save();
      \Drupal::logger('clutch:workflow')->notice('Create bundle @bundle',
        array(
          '@bundle' => $bundle_label,
        ));
      $this->createFields($bundle_info);
    }
    $array_of_referenced_paragraph = array();
    foreach($bundle_info['fields'] as $content) {
      $bundle = array(
        'id' => $bundle_info['id'],
        'fields' => $content
      );
      $paragraph = $this->createDefaultContentForEntity($bundle, 'paragraph');
      $temp['target_id'] = $paragraph->id();
      $temp['target_revision_id'] = $paragraph->getRevisionId();
      $array_of_referenced_paragraph[] = $temp;
    }
    return $array_of_referenced_paragraph;
  }
  
  /**
   *  {@inheritdoc}
   */
  public function createFields($bundle) {
    foreach($bundle['fields'][0] as $field) {
      $this->createField($bundle['id'], $field);
    }
  }

  /**
   *  {@inheritdoc}
   */
  public function createField($bundle, $field) {
    // since we are going to treat each field unique to each bundle, we need to
    // create field storage(field base)
    $field_storage = FieldStorageConfig::create([
      'field_name' => $field['field_name'],
      'entity_type' => 'paragraph',
      'type' => $field['field_type'],
      'cardinality' => 1,
      'custom_storage' => FALSE,
    ]);

    $field_storage->save();

    // create field instance for bundle
    $field_instance = FieldConfig::loadByName('paragraph', $bundle ,$field['field_name']);
    if(empty($field_instance)) {
      $field_instance = FieldConfig::create([
        'field_storage' => $field_storage,
        'bundle' => $bundle,
        'label' => str_replace('_', ' ', $field['field_name']),
      ]);

      $field_instance->save();
    }

    if($field['field_type'] == 'file') {
      $paragraph_bundle = str_replace($bundle . '_', '', $field['field_name']);
      $handler_settings = $field_instance->setSetting('file_extensions', 'pdf doc docx txt svg');
      $field_instance->save();
    }

    // Assign widget settings for the 'default' form mode.
     entity_get_form_display('paragraph', $bundle, 'default')
      ->setComponent($field['field_name'], array(
        'type' => $field['field_form_display'],
      ))->save();

    // Assign display settings for 'default' view mode.
    entity_get_display('paragraph', $bundle, 'default')
      ->setComponent($field['field_name'], array(
       'label' => 'hidden',
       'type' => $field['field_formatter'],
      ))->save();
    
    \Drupal::logger('clutch:workflow')->notice('Create field @field for bundle @bundle',
     array(
       '@field' => str_replace('_', ' ', $field['field_name']),
       '@bundle' => $bundle,
     ));
  }

  public function getFieldsInfoFromTemplate(Crawler $crawler, $bundle) {
    $collections = $crawler->filter('.collection')->each(function (Crawler $collection, $i) use ($bundle) {
      $fields = $collection->filterXPath('//*[@data-paragraph-field]')->each(function (Crawler $node, $i) use ($bundle) {
        $field_type = $node->extract(array('data-type'))[0];
        $field_name = $bundle . '_' . $node->extract(array('data-paragraph-field'))[0];
        $field_form_display = $node->extract(array('data-form-type'))[0];
        $field_formatter = $node->extract(array('data-format-type'))[0];
        switch($field_type) {
          case 'link':
            $uri = $node->extract(array('href'))[0];
            if(!strpos($uri, '//')) {
              $uri = 'internal:/' . $uri;
            }
            $default_value['uri'] = str_replace('.html', '', $uri);
            $default_value['title'] = $node->extract(array('_text'))[0];
            break;
          
          case 'image':
            $default_value = $node->extract(array('src'))[0];
            break;

          case 'file':
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
    });
    return $collections;
  }


  /**
   * Prepare entity to create bundle and content
   *
   * @param $template
   *   html string template from theme
   *
   * @return
   *   An array of entity info.
   */
  public function prepareEntityInfoFromTemplate($template = NULL) {
    $crawler = new HtmlPageCrawler($html);
    $entity_info = array();
    $bundle = $this->getBundle($crawler);
    $entity_info['id'] = $bundle;
    $fields = $this->getFieldsInfoFromTemplate($crawler, $bundle);
    $entity_info['fields'] = $fields;
    return $entity_info;
  }
}