<?php

/**
 * @file
 * Contains custom_page.page.inc..
 *
 * Page callback for Custom page entities.
 */

use Drupal\Core\Render\Element;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\CssSelector\CssSelector;
use Drupal\clutch\ComponentBuilder;
/**
 * Prepares variables for Custom page templates.
 *
 * Default template: custom_page.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_custom_page(array &$variables) {
  $custom_page = $variables['elements']['#custom_page'];
  $components = $custom_page->get('associated_components')->getValue();
  foreach($components as $component) {
    $entity = entity_load('component', $component['target_id']);
    if($entity->bundle() == 'component_view_reference') {
      $view_reference = $entity->get('field_component_view')->getValue();
      $view_id = array_pop($view_reference)['target_id'];
      $view_render = views_embed_view($view_id);
      $variables['contents'][] = drupal_render($view_render);
    }else {
      $markup = custom_page_render_entity($entity);
      $variables['contents'][] = $markup;
    }
  }
}

/**
 * Return markup for component on custom page
 */
function custom_page_render_entity($component) {
  $component_type = $component->bundle();
  $template_name = str_replace('_', '-', $component_type);
  $clutch_builder = new ComponentBuilder();
  $html = $clutch_builder->findAndReplace($template_name, $component);
  return $html;
}