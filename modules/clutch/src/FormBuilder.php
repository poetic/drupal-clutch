<?php

/**
 * @file
 * Contains \Drupal\clutch\FormBuilder.
 */

namespace Drupal\clutch;

use Drupal\Core\Entity\Entity\EntityFormMode;
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
use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Entity\Entity\EntityFormDisplay;

/**
 * Class FormBuilder.
 *
 * @package Drupal\clutch\Controller
 */
class FormBuilder extends ClutchBuilder{

  public function getHTMLTemplate($template) {
    $theme_array = $this->getCustomTheme();
    $theme_path = array_values($theme_array)[0];
    // $template name has the same name of directory that holds the template
    // pass null array to pass validation. we don't need to replace any variables. this only return 
    // the html string to we can parse and handle it
    return $this->twig_service->loadTemplate($theme_path.'/components/'.$template.'/'.$template.'.html.twig')->render(array());
  }

  public function collectFieldValues($entity, $field_definition) {
    return 1;
  }

  public function createBundle($bundle_info) {
    //TODO check if form already exists to reuse. always make new component type
    $form = $this->createForm($bundle_info);
    dpm($form);
    //$this->createFields($bundle_info);
  }

  public function createForm($bundle_info) {
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
  }

  public function createField($bundle, $field) {
    return 1;
  }

  public function getBundle(Crawler $crawler) {
       return 1;
  }

}
