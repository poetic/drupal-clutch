<?php

/**
 * @file
 * Contains \Drupal\clutch\PageBuilder.
 */

namespace Drupal\clutch;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\DomCrawler\Crawler;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * Class PageBuilder.
 *
 * @package Drupal\clutch\Controller
 */

class PageBuilder {

  /**
   * Create pages
   *
   * @param $theme
   *   theme name
   *
   * @return
   *   TODO
   */
  public function createPages($theme){
    $page_infos = Yaml::parse(file_get_contents('themes/'.$theme.'/components.yml'));
    foreach($page_infos as $page_title => $page_info){
      $this->createPage($page_title, $page_info);
    }
  }

  /**
   * Create page
   *
   * @param $page_title, $components
   *   page title
   *   associated components
   *
   * @return
   *   page entity
   */
  public function createPage($page_title, $components) {
    $associated_component_ids = array();
    foreach($components['components'] as $component) {
      array_push($associated_component_ids, $this->createAssociatedComponent($component));
    }
    $page_name = ucwords(str_replace('-', ' ', $page_title));
    $page = entity_create('custom_page', array(
      'id' => $page_title,
      'name' => $page_name,
      'associated_components' => $associated_component_ids,
    ));
    $page->save();
  }

  /**
   * Create associated components for page
   *
   * @param $component
   *   component string with component type and region
   *
   * @return
   *   array of target_id, target_revision_id of component to associate to page
   */
  public function createAssociatedComponent($component) {
    $component_info_array = explode('|', $component);
    $component_type = $component_info_array[0];
    $component_position = $component_info_array[1];
    $component_id = \Drupal::entityQuery('component')->condition('type',$component_type)->execute();
    $paragraph = Paragraph::create([
      'type' => 'associated_component',
      'component' => [
        'target_id' => key($component_id),
      ],
      'region' => [
        'value' => $component_position,
        'format' => 'string',
      ],
    ]);

    $paragraph->save();
    return [
      'target_id' => $paragraph->id(),
      'target_revision_id' => $paragraph->getRevisionId(),
    ];
  }
}
