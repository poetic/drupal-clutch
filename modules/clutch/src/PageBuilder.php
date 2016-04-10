<?php

/**
 * @file
 * Contains \Drupal\clutch\PageBuilder.
 */

namespace Drupal\clutch;

use Drupal\clutch\ClutchBuilder;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\DomCrawler\Crawler;


/**
 * Class PageBuilder.
 *
 * @package Drupal\clutch\Controller
 */

class PageBuilder extends ClutchBuilder{

  /**
   *  {@inheritdoc}
   */

  public function createPages($theme){
    $page_infos = Yaml::parse(file_get_contents('themes/'.$theme.'/components.yml'));
    foreach($page_infos as $key=>$page_info){
      $page_components = $page_info['components'];
      $array_nid = array();
      foreach($page_components as $page_component){
        $components = \Drupal::entityQuery('component')->condition('type',$page_component)->execute();
        $components = str_replace('-', '_', $components);
        $id = key($components);
        $id = array_values($components)[0];
        $id = array('target_id'=>$id);
        array_push($array_nid, $id);
      }
      $page_name = ucwords(str_replace('-', ' ', $key));
      $page = entity_create('custom_page', array(
        'id' => [$page_name],
        'name'=>[$page_name],
        'associated_components'=>$array_nid,
      ));
      $page->save();
    }
  }
  public function collectFieldValues($entity, $field_definition) {

  }

  public function createBundle($bundle_info) {

  }

  public function createField($bundle, $field) {

  }
   public function getHTMLTemplate($template) {

  }


  public function getBundle(Crawler $crawler) {

  }
}
