<?php

/**
 * @file
 * Contains \Drupal\clutch\ViewBuilder.
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
use Drupal\system\Entity\Menu;
use Drupal\menu_link_content\Entity\MenuLinkContent;

/**
 * Class ViewBuilder.
 *
 * @package Drupal\clutch\Controller
 */

class ViewBuilder extends ClutchBuilder{

  /**
   *  {@inheritdoc}
   */
  public function getHTMLTemplate($template){
    $theme_array = $this->getCustomTheme();
    $theme_path = array_values($theme_array)[0];
    return $this->twig_service->loadTemplate($theme_path.'/views/' . $template . '.html.twig')->render(array());
  }

  public function getWrapperForView($template) {
    $crawler = new HtmlPageCrawler($this->getHTMLTemplate($template));
    return $crawler;
  }

  public function collectFieldValues($entity, $field_definition) {

  }

  public function createBundle($bundle_info) {

  }

  public function createField($bundle, $field) {

  }

  public function getBundle(Crawler $crawler) {

  }
}