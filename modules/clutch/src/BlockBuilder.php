<?php

/**
 * @file
 * Contains \Drupal\clutch\BlockBuilder.
 */

namespace Drupal\clutch;

use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;
use Drupal\file\Entity\File;
use Symfony\Component\DomCrawler\Crawler;
use Drupal\clutch\ClutchBuilder;
use Drupal\block_content\Entity\BlockContent;
use Drupal\block_content\Entity\BlockContentType;

/**
 * Class BlockBuilder.
 *
 * @package Drupal\clutch\Controller
 */

class BlockBuilder extends ClutchBuilder{

  /**
   *  {@inheritdoc}
   */
  public function getHTMLTemplate($template){
    $theme_array = $this->getCustomTheme();
    $theme_path = array_values($theme_array)[0];
    // $template name has the same name of directory that holds the template
    // pass null array to pass validation. we don't need to replace any variables. this only return 
    // the html string to we can parse and handle it
    return $this->twig_service->loadTemplate($theme_path.'/blocks/'.$template.'/'.$template.'.html.twig')->render(array());
  }

  /**
   *  {@inheritdoc}
   */
  public function collectFieldValues($block, $field_definition) {
    $bundle = $block->bundle();
    $field_name = $field_definition->getName();
    $field_language = $field_definition->language()->getId();
    $field_value = $block->get($field_name)->getValue();
    $field_type = $field_definition->getType();
    if(($field_type == 'image' && !empty($field_value)) || ($field_type == 'file' && !empty($field_value))) {
      $file = File::load($field_value[0]['target_id']);
      $url = file_create_url($file->get('uri')->value);
      $field_value[0]['url'] = $url;
    }

    $field_attribute = 'block_content/' . $block->id() . '/' . $field_name . '/' . $field_language . '/full';
    return [str_replace($bundle.'_', '', $field_name) => array(
      'content' => !empty($field_value) ? $field_value[0] : NULL,
      'quickedit' => $field_attribute,
      'type' => $field_type,
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
    if(BlockContentType::load($bundle_info['id'])) {
      \Drupal::logger('clutch:workflow')->notice('Bundle exists. Need to update bundle.');
    }else {
      $bundle_label = ucwords(str_replace('_', ' ', $bundle_info['id']));
      $bundle = BlockContentType::create(array(
        'id' => $bundle_info['id'],
        'label' => $bundle_label,
        'revision' => TRUE,
      ));
      $bundle->save();
      \Drupal::logger('clutch:workflow')->notice('Create block type @bundle',
        array(
          '@bundle' => $bundle_label,
        ));
      $this->createFields($bundle_info);
      $this->createDefaultContentForEntity($bundle_info, 'block_content');
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
    switch($field['field_type']) {

     case 'entity_reference_revisions':
      $field_storage = FieldStorageConfig::create([
        'field_name' => $field['field_name'],
        'entity_type' => 'block_content',
        'type' => $field['field_type'],
        'cardinality' => -1,
        'custom_storage' => FALSE,
        'settings' => array(
          'target_type' => 'paragraph'
         ),
      ]);
      break;

     case 'entity_reference':
      $field_storage = FieldStorageConfig::create([
        'field_name' => $field['field_name'],
        'entity_type' => 'block_content',
        'type' => $field['field_type'],
        'cardinality' => -1,
        'custom_storage' => FALSE,
        'settings' => array(
          'target_type' => 'contact_form'
         ),
      ]);
      break;

      default:
      $field_storage = FieldStorageConfig::create([
        'field_name' => $field['field_name'],
        'entity_type' => 'block_content',
        'type' => $field['field_type'],
        'cardinality' => 1,
        'custom_storage' => FALSE,
      ]);
      break;
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

    if($field['field_type'] == 'file') {
      $paragraph_bundle = str_replace($bundle . '_', '', $field['field_name']);
      $handler_settings = $field_instance->setSetting('file_extensions', 'pdf doc docx txt svg');
      $field_instance->save();
    }

    // Assign widget settings for the 'default' form mode.
    entity_get_form_display('block_content', $bundle, 'default')
      ->setComponent($field['field_name'], array(
        'type' => $field['field_form_display'],
      ))
      ->save();

    // Assign display settings for 'default' view mode.
    entity_get_display('block_content', $bundle, 'default')
      ->setComponent($field['field_name'], array(
        'label' => 'hidden',
        'type' => $field['field_formatter'],
      ))
      ->save();
     \Drupal::logger('clutch:workflow')->notice('Create field @field for block @bundle',
      array(
        '@field' => str_replace('_', ' ', $field['field_name']),
        '@bundle' => $bundle,
      ));
  }

  /**
   *  {@inheritdoc}
   */
  public function getBundle(Crawler $crawler) {
    $bundle = $crawler->getAttribute('data-block');
    return $bundle;
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
  public function addQuickeditAttributeForBundle($crawler, $block) {
    $bundle = $block->bundle();
    $quickedit = 'block_content/'. $block->id();
    $bundle_layer = $crawler->filter('[data-block="'. $bundle .'"]');
    $bundle_layer->setAttribute(QE_ENTITY_ID, $quickedit)->addClass('contextual-region');
    dpm($bundle_layer);
    $build_contextual_links['#contextual_links']['block_content'] = array(
      'route_parameters' =>array('block_content' => $block->id()),
      'metadata' => array('changed' => $block->getChangedTime()),
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
  public function findAndReplace($template, $block, $view_mode = NULL) {
    $html = parent::findAndReplace($template, $block);
    if(in_array('administrator', \Drupal::currentUser()->getRoles())) {
      $html = $this->addQuickeditAttributeForBundle($html, $block);
    }
    return $html;
  }
}