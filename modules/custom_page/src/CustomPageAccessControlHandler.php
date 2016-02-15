<?php

/**
 * @file
 * Contains \Drupal\custom_page\CustomPageAccessControlHandler.
 */

namespace Drupal\custom_page;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Custom page entity.
 *
 * @see \Drupal\custom_page\Entity\CustomPage.
 */
class CustomPageAccessControlHandler extends EntityAccessControlHandler {
  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\custom_page\CustomPageInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished custom page entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published custom page entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit custom page entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete custom page entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add custom page entities');
  }

}
