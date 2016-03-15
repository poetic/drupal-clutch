<?php

/**
 * @file
 * Builds placeholder replacement tokens for custom_page-related data.
 */

use Drupal\Core\Datetime\Entity\DateFormat;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Render\BubbleableMetadata;

/**
 * Implements hook_token_info().
 */
function custom_page_token_info() {
  $type = array(
    'name' => t('Page'),
    'description' => t('Tokens related to individual content items, or "Pages".'),
    'needs-data' => 'custom_page',
  );

  // Core tokens for nodes.
  $custom_page['id'] = array(
    'name' => t("Content ID"),
    'description' => t('The unique ID of the content item, or "node".'),
  );
  $custom_page['name'] = array(
    'name' => t("Name"),
  );
  $custom_page['path'] = array(
    'name' => t("URL Path"),
  );
  $custom_page['meta_title'] = array(
    'name' => t("Metatag Page Title"),
  );
  $custom_page['meta_description'] = array(
    'name' => t("Metatag Page Description"),
  );

  return array(
    'types' => array('custom_page' => $type),
    'tokens' => array('custom_page' => $custom_page),
  );
}

/**
 * Implements hook_tokens().
 */
function custom_page_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  $token_service = \Drupal::token();

  $url_options = array('absolute' => TRUE);
  if (isset($options['langcode'])) {
    $url_options['language'] = \Drupal::languageManager()->getLanguage($options['langcode']);
    $langcode = $options['langcode'];
  }
  else {
    $langcode = LanguageInterface::LANGCODE_DEFAULT;
  }
  $replacements = array();

  if ($type == 'custom_page' && !empty($data['custom_page'])) {
    /** @var Drupal\custom_page\Entity\CustomPage $custom_page */
    $custom_page = $data['custom_page'];

    foreach ($tokens as $name => $original) {
      switch ($name) {
        // Simple key values on the node.
        case 'id':
          $replacements[$original] = $custom_page->id();
          break;

        case 'name':
          $replacements[$original] = $custom_page->getName();
          break;

        case 'path':
          $replacements[$original] = $custom_page->getPath();
          break;
        case 'meta_title':
          $replacements[$original] = $custom_page->getMetaTitle();
          break;
        case 'meta_description':
          $replacements[$original] = $custom_page->getMetaDescription();
          break;
      }
    }
  }

  return $replacements;
}
