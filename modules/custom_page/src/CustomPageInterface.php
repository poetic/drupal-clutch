<?php

/**
 * @file
 * Contains \Drupal\custom_page\CustomPageInterface.
 */

namespace Drupal\custom_page;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Custom page entities.
 *
 * @ingroup custom_page
 */
interface CustomPageInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {
  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Custom page name.
   *
   * @return string
   *   Name of the Custom page.
   */
  public function getName();

  /**
   * Sets the Custom page name.
   *
   * @param string $name
   *   The Custom page name.
   *
   * @return \Drupal\custom_page\CustomPageInterface
   *   The called Custom page entity.
   */
  public function setName($name);

  /**
   * Gets the Custom page path.
   *
   * @return string path
   *   Path of the Custom page.
   */
  public function getPath();

  /**
   * Sets the Custom page path.
   *
   * @param string $path
   *   The Custom page path.
   *
   * @return \Drupal\custom_page\CustomPageInterface
   *   The called Custom page entity.
   */
  public function setPath($path);


  /**
   * Gets the Metatag Page Title
   *
   * @return string
   *   Metatag Page Title of the Custom page.
   */
  public function getMetaTitle();

  /**
   * Sets the Metatag Page Title
   *
   * @param string $meta_title
   *   The Custom page metatag page title
   *
   * @return \Drupal\custom_page\CustomPageInterface
   *   The called Custom page entity.
   */
  public function setMetaTitle($meta_title);

  /**
   * Gets the Metatag Page Description
   *
   * @return string
   *   Metatag Page Description of the Custom page.
   */
  public function getMetaDescription();

  /**
   * Sets the Metatag Page Description
   *
   * @param string $meta_description
   *   The Custom page metatag page description
   *
   * @return \Drupal\custom_page\CustomPageInterface
   *   The called Custom page entity.
   */
  public function setMetaDescription($meta_description);

  /**
   * Gets the Custom page creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Custom page.
   */
  public function getCreatedTime();

  /**
   * Sets the Custom page creation timestamp.
   *
   * @param int $timestamp
   *   The Custom page creation timestamp.
   *
   * @return \Drupal\custom_page\CustomPageInterface
   *   The called Custom page entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Custom page published status indicator.
   *
   * Unpublished Custom page are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Custom page is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Custom page.
   *
   * @param bool $published
   *   TRUE to set this Custom page to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\custom_page\CustomPageInterface
   *   The called Custom page entity.
   */
  public function setPublished($published);

}
