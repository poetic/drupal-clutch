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
    // drupal 8 render entity programmatically using EntityViewBuilder
    $view_builder = \Drupal::entityManager()->getViewBuilder('component');
    $output = $view_builder->view(entity_load('component', $component['target_id']));
    $variables['contents'][] = $output;
  }
}