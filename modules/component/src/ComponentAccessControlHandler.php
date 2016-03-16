<?php

/**
 * @file
 * Contains \Drupal\component\ComponentAccessControlHandler.
 */

namespace Drupal\component;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Component entity.
 *
 * @see \Drupal\component\Entity\Component.
 */
class ComponentAccessControlHandler extends EntityAccessControlHandler {
  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\component\ComponentInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished component entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published component entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit component entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete component entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add component entities');
  }

}
