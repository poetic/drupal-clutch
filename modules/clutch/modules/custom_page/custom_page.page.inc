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
use Drupal\component\Entity\Component;
use Drupal\paragraphs\Entity\Paragraph;

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
  $associated_components = $custom_page->get('associated_components')->getValue();
  foreach($associated_components as $associated_component) {
  	$component = get_associated_component_entity($associated_component);
    // drupal 8 render entity programmatically using EntityViewBuilder
    $view_builder = \Drupal::entityManager()->getViewBuilder('component');
    $output = $view_builder->view(entity_load('component', $component['component']));
    $variables['contents'][$component['region']][] = $output;
  }
}

function get_associated_component_entity($associated_component) {
	$associated_component_id = $associated_component['target_id'];
	$associated_component_entity = Paragraph::load($associated_component_id);
	$component_id = $associated_component_entity->get('component')->getValue()[0]['target_id'];
	$component_region = $associated_component_entity->get('region')->value;
	return array(
		'component' => $component_id,
		'region' => $component_region,
	);
}