<?php

/**
 * @file
 * Builds placeholder replacement tokens for node-related data.
 */

use Drupal\Core\Datetime\Entity\DateFormat;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\user\Entity\User;

/**
 * Implements hook_token_info().
 */
function component_token_info() {
  $type = array(
    'name' => t('Components'),
    'description' => t('Tokens related to individual content items, or "Components".'),
    'needs-data' => 'component',
  );

  // Core tokens for nodes.
  $component['name'] = array(
    'name' => t("Name"),
  );

  $component['type'] = array(
    'name' => t("Component type"),
  );
  dpm($component);
  return array(
    'types' => array('component' => $type),
    'tokens' => array('component' => $component),
  );
}

/**
 * Implements hook_tokens().
 */
function component_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
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

  if ($type == 'component' && !empty($data['component'])) {
    /** @var \Drupal\node\NodeInterface $node */
    $component = $data['component'];

    foreach ($tokens as $name => $original) {
      switch ($name) {
        // Simple key values on the node.
        case 'name':
          $replacements[$original] = $component->getName();
          break;

        case 'type':
          $replacements[$original] = $component->getType();
          break;

      }
    }
  }
  return $replacements;
}
