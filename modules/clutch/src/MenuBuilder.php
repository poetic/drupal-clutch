<?php

/**
 * @file
 * Contains \Drupal\clutch\MenuBuilder.
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
 * Class MenuBuilder.
 *
 * @package Drupal\clutch\Controller
 */

class MenuBuilder extends ClutchBuilder{

  /**
   *  {@inheritdoc}
   */
  public function getHTMLTemplate($template){
    $theme_array = $this->getCustomTheme();
    $theme_path = array_values($theme_array)[0];
    return $this->twig_service->loadTemplate($theme_path.'/menu/' . $template . '.html.twig')->render(array());
  }

  public function createMenu() {
    $html = $this->getHTMLTemplate('main-menu');
    $crawler = new HtmlPageCrawler($html);

    $menu_name = $crawler->getAttribute('data-menu');
    
    $menu = Menu::create(array(
      'id'=> $menu_name,
      'label' => ucwords(str_replace('-', ' ', $menu_name)),
    ));
    $menu->save();

    // check if menu has sub menu
    // dpm($crawler->filter('.dropdown')->count());

    $menu_links = $crawler->filter('.w-nav-link')->each(function (Crawler $node, $i) use ($menu_name) {
      $link = strtolower($node->extract(array('_text'))[0]);
      $link = str_replace(' ', '-', $link);
      $menu_link = MenuLinkContent::create([
          'title' => $node->extract(array('_text'))[0],
          'link' => ['uri' => 'internal:/' . $link],
          'menu_name' => $menu_name,
          'expanded' => TRUE,
      ]);
      $menu_link->save();
    });
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