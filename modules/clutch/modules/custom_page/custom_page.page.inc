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
//<<<<<<< Updated upstream
//    $entity = entity_load('component', $component['target_id']);
//    if($entity->bundle() == 'component_view_reference') {
//      $view_reference = $entity->get('field_component_view')->getValue();
//      foreach($view_reference as $view) {
//        $view_render = views_embed_view($view['target_id']);
//        $variables['contents'][] = drupal_render($view_render);
//      }
//    }elseif($entity->bundle() == 'component_contact_form') {
//      $form_reference = $entity->get('contact_form_reference')->getValue();
//      $form_id = array_pop($form_reference)['target_id'];
//      $message = \Drupal::entityManager()
//        ->getStorage('contact_message')
//        ->create(array(
//          'contact_form' => $form_id,
//        ));
//      $form = \Drupal::service('entity.form_builder')->getForm($message);
//      $variables['contents'][] = $form;
//    }else {
//      $markup = custom_page_render_entity($entity);
//      $variables['contents'][] = $markup;
//    }
//=======
    // drupal 8 render entity programmatically using EntityViewBuilder
    $view_builder = \Drupal::entityManager()->getViewBuilder('component');
    $output = $view_builder->view(entity_load('component', $component['target_id']));
    $variables['contents'][] = $output;
//>>>>>>> Stashed changes
  }
}