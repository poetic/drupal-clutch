<?php

/**
 * @file
 * Contains \Drupal\component\Entity\Component.
 */

namespace Drupal\component\Entity;

use Drupal\views\EntityViewsData;
use Drupal\views\EntityViewsDataInterface;

/**
 * Provides Views data for Component entities.
 */
class ComponentViewsData extends EntityViewsData implements EntityViewsDataInterface {
  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['component']['table']['base'] = array(
      'field' => 'id',
      'title' => $this->t('Component'),
      'help' => $this->t('The Component ID.'),
    );

    return $data;
  }

}
