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

  public function createMenu($crawler) {
    $menu_name = $crawler->filterXPath('//*[@data-menu]')->getAttribute('data-menu');
    //check and see if the menu name exists
    if(!\Drupal::entityQuery('menu')->condition('id', $menu_name)->execute()){
      $menu = Menu::create(array(
        'id'=> $menu_name,
        'label' => ucwords(str_replace('-', ' ', $menu_name)),
      ));
      $menu->save();

      // check if menu has sub menu
      if($crawler->filter('.w-dropdown')->count()) {
        $this->handleDropdownMenu($crawler, $menu_name);
      }
      // proceed with single menu item
      $menu_links = $crawler->filter('.w-nav-link')->each(function (Crawler $node, $i) use ($menu_name) {
        $link = $node->extract(array('href'))[0];
        $link = str_replace('.html', '', $link);
        if(!strpos($uri, '//')) {
          $link = '/' . $link;
        }
        $title = $node->extract(array('_text'))[0];
        $this->createMenuLink($title, 'internal:/' . $link, $menu_name, TRUE, NULL);
      });
    }
  }

  public function handleDropdownMenu($crawler, $menu_name) {
    $menu_links = $crawler->filter('.w-dropdown')->each(function (Crawler $node, $i) use ($menu_name) {
      $title = $node->filter('.w-dropdown-toggle.nav-link div')->text();
      $parent_menu = $this->createMenuLink($title, NULL, $menu_name, TRUE, NULL);
      $this->handleChildrenLinks($node, $parent_menu, $menu_name);
    });
  }

  public function handleChildrenLinks($crawler, $parent_menu, $menu_name) {
    $parent_menu_uuid = $parent_menu->uuid();
    $parent = "menu_link_content:$parent_menu_uuid";
    $menu_links = $crawler->filter('.sub-link')->each(function (Crawler $node, $i) use ($menu_name, $parent) {
      $link = $node->extract(array('href'))[0];
      $link = str_replace('.html', '', $link);
      if(!strpos($uri, '//')) {
        $link = '/' . $link;
      }
      $title = $node->extract(array('_text'))[0];
      $this->createMenuLink($title, 'internal:/' . $link, $menu_name, FALSE, $parent);
    });
  }

  public function createMenuLink($title, $link, $menu_name, $expanded, $parent) {
    $menu_link = MenuLinkContent::create([
        'title' => $title,
        'link' => ['uri' => $link],
        'menu_name' => $menu_name,
        'expanded' => $expanded,
        'parent' => $parent,
    ]);
    $menu_link->save(); 
    return $menu_link;
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