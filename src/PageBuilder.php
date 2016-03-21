<?php

/**
 * @file
 * Contains \Drupal\clutch\PageBuilder.
 */

namespace Drupal\clutch;

use Symfony\Component\Yaml\Yaml;

/**
 * Class PageBuilder.
 *
 * @package Drupal\clutch\Controller
 */

class PageBuilder extends ClutchBuilder{

  /**
   *  {@inheritdoc}
   */

  public function createPages(){
    $page_infos = Yaml::parse(file_get_contents('themes/webflow/pages.yml'));
    foreach($page_infos as $key=>$page_info){
      $page_components = $page_info['components'];
      $array_nid = array();
      foreach($page_components as $page_component){
        $components = \Drupal::entityQuery('component')->condition('type',$page_component)->execute();
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
}