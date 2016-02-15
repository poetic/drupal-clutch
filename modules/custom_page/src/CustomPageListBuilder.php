<?php

/**
 * @file
 * Contains \Drupal\custom_page\CustomPageListBuilder.
 */

namespace Drupal\custom_page;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Routing\LinkGeneratorTrait;
use Drupal\Core\Url;

/**
 * Defines a class to build a listing of Custom page entities.
 *
 * @ingroup custom_page
 */
class CustomPageListBuilder extends EntityListBuilder {
  use LinkGeneratorTrait;
  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Custom page ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\custom_page\Entity\CustomPage */
    $row['id'] = $entity->id();
    $row['name'] = $this->l(
      $entity->label(),
      new Url(
        'entity.custom_page.edit_form', array(
          'custom_page' => $entity->id(),
        )
      )
    );
    return $row + parent::buildRow($entity);
  }

}
