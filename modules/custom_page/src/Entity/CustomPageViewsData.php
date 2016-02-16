<?php

/**
 * @file
 * Contains \Drupal\custom_page\Entity\CustomPage.
 */

namespace Drupal\custom_page\Entity;

use Drupal\views\EntityViewsData;
use Drupal\views\EntityViewsDataInterface;

/**
 * Provides Views data for Custom page entities.
 */
class CustomPageViewsData extends EntityViewsData implements EntityViewsDataInterface {
  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['custom_page']['table']['base'] = array(
      'field' => 'id',
      'title' => $this->t('Page'),
      'help' => $this->t('The Page ID.'),
    );

    return $data;
  }

}
