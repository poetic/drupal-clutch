<?php

/**
 * @file
 * Contains \Drupal\component\ComponentInterface.
 */

namespace Drupal\component;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Component entities.
 *
 * @ingroup component
 */
interface ComponentInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {
  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Component type.
   *
   * @return string
   *   The Component type.
   */
  public function getType();

  /**
   * Gets the Component name.
   *
   * @return string
   *   Name of the Component.
   */
  public function getName();

  /**
   * Sets the Component name.
   *
   * @param string $name
   *   The Component name.
   *
   * @return \Drupal\component\ComponentInterface
   *   The called Component entity.
   */
  public function setName($name);

  /**
   * Gets the Component creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Component.
   */
  public function getCreatedTime();

  /**
   * Sets the Component creation timestamp.
   *
   * @param int $timestamp
   *   The Component creation timestamp.
   *
   * @return \Drupal\component\ComponentInterface
   *   The called Component entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Component published status indicator.
   *
   * Unpublished Component are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Component is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Component.
   *
   * @param bool $published
   *   TRUE to set this Component to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\component\ComponentInterface
   *   The called Component entity.
   */
  public function setPublished($published);

}
