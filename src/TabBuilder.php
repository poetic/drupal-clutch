<?php

/**
 * @file
 * Contains \Drupal\clutch\TabBuilder.
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
use Drupal\clutch\ParagraphBuilder;

/**
 * Class TabBuilder.
 *
 * @package Drupal\clutch\Controller
 */

class TabBuilder extends ParagraphBuilder{
  public function getFieldsInfoFromTemplate(Crawler $crawler, $bundle) {
    $collections = Parent::getFieldsInfoFromTemplate($crawler, $bundle);
    foreach($collections as $index => $collection) {
      $collections[$index] = $this->addFieldTabTitle($index, $collection, $bundle);
    }
    return $collections;
  }

  public function addFieldTabTitle($index, $collection, $bundle) {
    $temp['field_name'] = $bundle . '_tab_title';
    $temp['field_type'] = 'string';
    $temp['field_form_display'] = 'string_textfield';
    $temp['field_formatter'] = 'string';
    $temp['value'] = "Tab $index";
    $collection[] = $temp;
    return $collection;
  }
}