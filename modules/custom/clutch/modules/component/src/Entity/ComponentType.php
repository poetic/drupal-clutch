<?php

/**
 * @file
 * Contains \Drupal\component\Entity\ComponentType.
 */

namespace Drupal\component\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\component\ComponentTypeInterface;

/**
 * Defines the Component type entity.
 *
 * @ConfigEntityType(
 *   id = "component_type",
 *   label = @Translation("Component type"),
 *   handlers = {
 *     "list_builder" = "Drupal\component\ComponentTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\component\Form\ComponentTypeForm",
 *       "edit" = "Drupal\component\Form\ComponentTypeForm",
 *       "delete" = "Drupal\component\Form\ComponentTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\component\ComponentTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "component_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "component",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/component_type/{component_type}",
 *     "add-form" = "/admin/structure/component_type/add",
 *     "edit-form" = "/admin/structure/component_type/{component_type}/edit",
 *     "delete-form" = "/admin/structure/component_type/{component_type}/delete",
 *     "collection" = "/admin/structure/component_type"
 *   }
 * )
 */
class ComponentType extends ConfigEntityBundleBase implements ComponentTypeInterface {
  /**
   * The Component type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Component type label.
   *
   * @var string
   */
  protected $label;

}
