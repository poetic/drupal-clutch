<?php

/**
 * @file
 * Contains component.page.inc..
 *
 * Page callback for Component entities.
 */

use Drupal\Core\Render\Element;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\clutch\ComponentBuilder;

/**
 * Prepares variables for Component templates.
 *
 * Default template: component.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_component(array &$variables) {
  // Fetch Component Entity Object.
  $component = $variables['elements']['#component'];

  $build_contextual_links['#contextual_links']['component'] = array(
    'route_parameters' =>array('component' => $component->id()),
    'metadata' => array('changed' => $component->getChangedTime()),
  );

  $markup = component_render_standard_component($component);
  $variables['content']['#markup'] = $markup;
}

/**
 * Return markup for standard component (not view/form reference)
 */
function component_render_standard_component($component) {
  $component_type = $component->bundle();
  $template_name = str_replace('_', '-', $component_type);
  $clutch_builder = new ComponentBuilder();
  $html = $clutch_builder->findAndReplace($template_name, $component);
  return $html;
}

/**
* Prepares variables for a custom entity type creation list templates.
*
* Default template: component-content-add-list.html.twig.
*
* @param array $variables
*   An associative array containing:
*   - content: An array of component-types.
*
* @see block_content_add_page()
*/
function template_preprocess_component_content_add_list(&$variables) {
  $variables['types'] = array();
  $query = \Drupal::request()->query->all();
  foreach ($variables['content'] as $type) {
    $variables['types'][$type->id()] = array(
      'link' => Link::fromTextAndUrl($type->label(), new Url('entity.component.add_form', array(
        'component_type' => $type->id()
      ), array('query' => $query))),
      'description' => array(
      '#markup' => $type->label(),
      ),
      'title' => $type->label(),
      'localized_options' => array(
      'query' => $query,
      ),
    );
  }
}
