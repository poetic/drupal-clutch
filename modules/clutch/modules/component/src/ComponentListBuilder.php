<?php

/**
 * @file
 * Contains \Drupal\component\ComponentListBuilder.
 */

namespace Drupal\component;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Routing\LinkGeneratorTrait;
use Drupal\Core\Url;

/**
 * Defines a class to build a listing of Component entities.
 *
 * @ingroup component
 */
class ComponentListBuilder extends EntityListBuilder {
  use LinkGeneratorTrait;
  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Component ID');
    $header['name'] = $this->t('Name');
    $header['uuid'] = $this->t('Component UUID');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\component\Entity\Component */
    $row['id'] = $entity->id();
    $row['name'] = $this->l(
      $entity->label(),
      new Url(
        'entity.component.canonical', array(
          'component' => $entity->id(),
        )
      )
    );
    $row['uuid'] = $entity->uuid();
    return $row + parent::buildRow($entity);
  }

}
