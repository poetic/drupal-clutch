<?php

/**
 * @file
 * Contains \Drupal\clutch\FormBuilder.
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
use Drupal\clutch\clutchBuilder;
use Drupal\contact\Entity\ContactForm;
use Drupal\clutch\ExampleForm;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Class FormBuilder.
 *
 * @package Drupal\clutch\Controller
 */
class FormBuilder extends ClutchBuilder{

  /**
   * Load template using twig engine.
   * @param string $template, $view_mode
   *
   * @return string
   *   Return html string from template
   */
  
  public function getHTMLTemplate($template){
    $theme_array = $this->getCustomTheme();
    $theme_path = array_values($theme_array)[0];
    // $template name has the same name of directory that holds the template
    // pass null array to pass validation. we don't need to replace any variables. this only return 
    // the html string to we can parse and handle it
    return $this->twig_service->loadTemplate($theme_path.'/components/'.$template.'/'.$template.'.html.twig')->render(array());
  }

  public function createEntitiesFromTemplate($bundles) {
    parent::createEntitiesFromTemplate($bundles);
  }

  public function collectFieldValues($component, $field_definition) {
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

    $field_attribute = 'node/' . $component->id() . '/' . $field_name . '/' . $field_language . '/full';
    return [str_replace($bundle.'_', '', $field_name) => array(
      'content' => $field_value[0],
      'quickedit' => $field_attribute,
    )];
  }

  public function createBundle($bundle_info) {
    if(entity_load('contact_form', $bundle_info['id'])) { //what is node_type?
      // TODO Handle update bundle
      dpm('exists');
      \Drupal::logger('clutch:workflow')->notice('Bundle exists. Need to update bundle.');
      // dpm('Cannot create bundle. Bundle exists. Need to update bundle.');
    } else {
      $bundle_label = ucwords(str_replace('_', ' ', $bundle_info['id']));
      $form_type = ContactForm::create(array(
          'id' => $bundle_info['id'],
          'label' => $bundle_label,
          'type' => "contact_form",
      ));
      $form_type->save();
      \Drupal::logger('clutch:workflow')->notice('Create bundle @bundle',
        array(
          '@bundle' => $bundle_label,
        ));
      $this->createFields($bundle_info);
    }
  }

  public function createField($bundle, $field) {
    // since we are going to treat each field unique to each bundle, we need to
    // create field storage(field base)
//    $new_field = BaseFieldDefinition::create('string');
//    dpm($new_field);
    $field_storage = FieldStorageConfig::create([
      'field_name' => $field['field_name'],
      'entity_type' => 'contact_message',
      'type' => 'string',
      'cardinality' => 1,
      'custom_storage' => FALSE,
    ]);
//    dpm($field_storage);
    $field_storage->save();

    // create field instance for bundle
    $field_instance = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => $bundle,
      'label' => str_replace('_', ' ', $field['field_name']),
    ]);

    $field_instance->save();

    // // Assign widget settings for the 'default' form mode.
  //     entity_get_form_display('node', $bundle, 'default')
  //       ->setComponent($field['field_name'], array(
  //         'type' => $field['field_form_display'],
  //       ))
  //       ->save();
  //
  //    // // Assign display settings for 'default' view mode.
  //     entity_get_display('node', $bundle, 'default')
  //       ->setComponent($field['field_name'], array(
  //         'label' => 'hidden',
  //         'type' => $field['field_formatter'],
  //       ))
  //       ->save();
  //      \Drupal::logger('clutch:workflow')->notice('Create field @field for bundle @bundle',
  //       array(
  //         '@field' => str_replace('_', ' ', $field['field_name']),
  //         '@bundle' => $bundle,
  //       ));
  }
    public function prepareEntityInfoFromTemplate($template) {
    $html = $this->getHTMLTemplate($template);
    $crawler = new HtmlPageCrawler($html);
    $entity_info = array();
    $bundle = $this->getBundle($crawler);
    $entity_info['id'] = $bundle;
    $fields = $this->getFieldsInfoFromTemplate($crawler, $bundle);
    $entity_info['fields'] = $fields;
    return $entity_info;
  }

  public function getBundle(Crawler $crawler) {
    $bundle = $crawler->filter('*')->getAttribute('data-component');
    return $bundle;
  }
}
