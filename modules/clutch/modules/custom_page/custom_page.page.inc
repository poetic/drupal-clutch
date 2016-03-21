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
use Drupal\clutch\ViewBuilder;
use \Wa72\HtmlPageDom\HtmlPageCrawler;
use Drupal\Core\Render\Markup;


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
      $view_references = $entity->get('field_component_view')->getValue();
      $component_title = str_replace(' ', '-', strtolower($entity->getName()));
      $component_builder = new ComponentBuilder();
      $component_view_wrapper = $component_builder->getWrapperForComponentView($component_title);
      $views_markup = '';
      foreach($view_references as $view) {
        $view_markup = custom_page_render_view($view);
        $views_markup .=$view_markup->__toString();
      }
      $component_view_wrapper->children()->append($views_markup);
      $variables['contents'][] = $component_view_wrapper;
    }elseif($entity->bundle() == 'component_form_reference') {
      $form_reference = $entity->get('field_form_reference')->getValue();
      $form_id = array_pop($form_reference)['target_id'];
      $message = \Drupal::entityManager()
        ->getStorage('contact_message')
        ->create(array(
          'contact_form' => $form_id,
        ));
      $form = \Drupal::service('entity.form_builder')->getForm($message);
      $variables['contents'][] = $form;
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

function custom_page_render_view($view) {
  $view_render_array = views_embed_view($view['target_id']);
  $view_builder = new ViewBuilder();
  $view_wrapper_from_template = $view_builder->getWrapperForView($view['target_id']);
  if($view_wrapper_from_template->children()->count()) {
    $view_wrapper_from_template->children()->setInnerHTML(drupal_render($view_render_array)->__toString());
  }else {
    $view_wrapper_from_template->setInnerHTML(drupal_render($view_render_array)->__toString());
  }
  return $view_wrapper_from_template;
}