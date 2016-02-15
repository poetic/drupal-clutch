<?php

/**
 * @file
 * Contains \Drupal\component\ComponentViewBuilder.
 */

namespace Drupal\component;

use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityViewBuilder;

/**
 * Render controller for component.
 */
class ComponentViewBuilder extends EntityViewBuilder {
  /**
   * {@inheritdoc}
   */
  protected function alterBuild(array &$build, EntityInterface $entity, EntityViewDisplayInterface $display, $view_mode) {
    
    parent::alterBuild($build, $entity, $display, $view_mode);
    if (!$entity->isNew()) {
      $build['#contextual_links']['component'] = array(
        'route_parameters' =>array('component' => $entity->id()),
        'metadata' => array('changed' => $entity->getChangedTime()),
      );
      $build['#theme'] = $entity->bundle();
    }
  }

}
