<?php

/**
 * @file
 * Contains \Drupal\clutch\PageBuilder.
 */

namespace Drupal\clutch;

use Symfony\Component\Yaml\Yaml;
use Drupal\page_manager\Entity\Page;
use Drupal\page_manager\Entity\PageVariant;
use Drupal\block_content\Entity\BlockContent;

/**
 * Class PageBuilder.
 *
 * @package Drupal\clutch\Controller
 */

class PageBuilder {

  /**
   * Build Pages and associate Blocks with Pages
   *
   * @param $theme
   *   theme name
   *
   * @return
   *   TODO
   */
  public function build($theme){
    $page_infos = Yaml::parse(file_get_contents('themes/'.$theme.'/blocks.yml'));
    $page_variants = $this->createPages($page_infos);
    $block_uuids = $this->getBlocks();

    foreach($page_infos as $page => $blocks) {
      if(!empty($blocks)) {
        $this->addBlockstoPage($blocks, $page_variants[$page], $block_uuids);
      }
    }
    drupal_flush_all_caches();
  }

  /**
   * Create pages
   *
   * @param $page_infos
   *   array of pages and blocks associated with pages
   *
   * @return $page_variants
   *   array of page variants
   */
  public function createPages($page_infos) {
    $pages = array_keys($page_infos);
    $page_variants = array();
    foreach($pages as $page) {
      $page_variants[$page] = $this->createPage($page);
    }
    return $page_variants;
  }

  /**
   * Get array of block uuids
   *
   * @return $block_uuids
   *   array of block uuids. key is block bundle. value is block uuid
   */
  public function getBlocks() {
    $block_ids = \Drupal::entityQuery('block_content')->execute();
    $blocks = BlockContent::loadMultiple($block_ids);
    $block_uuids = array();
    foreach($blocks as $block) {
      $block_uuids[$block->bundle()] = $block->uuid();
    }
    return $block_uuids;
  }

  /**
   * Create page
   *
   * @param $page_title
   *   page title
   *
   * @return
   *   page variant entity
   */
  public function createPage($page_title) {
    $page = Page::create(array(
      'id' => $page_title,
      'label' => ucwords(str_replace('_', ' ', $page_title)),
      'path' => '/' . str_replace('_', '-', $page_title),
    ));
    $page->save();

    $variant = PageVariant::create(array(
      'id' => $page_title,
      'label' => ucwords(str_replace('_', ' ', $page_title)),
      'variant' => 'block_display',
      'page' => $page_title,
    ));
    $variant->save();
    return $variant;
  }
  
  /**
   * Add Block Instances to Page Variant using Page manager mechanism 
   *
   * @param $block_infos, $page_variant, $block_uuids
   *   array of blocks associate to page
   *   variant entity of page
   *   array of block uuids to generate plugin id
   *
   */
  public function addBlockstoPage($block_infos, $page_variant, $block_uuids) {
    $blockManager = \Drupal::service('plugin.manager.block');
    foreach($block_infos as $block) {
      $block_uuid = $block_uuids[$block];
      $block_plugin_id = "block_content:$block_uuid";
      
      $block_instance = $blockManager->createInstance($block_plugin_id);
      $block_instance_configuration = $block_instance->getConfiguration();
      // use 'top' as region for now
      $block_instance_configuration['region'] = 'top';
      $variant_plugin = $page_variant->getVariantPlugin();
      $variant_plugin->addBlock($block_instance_configuration);
    }
    $page_variant->save();
  }
}
