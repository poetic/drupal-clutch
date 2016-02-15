<?php

/**
 * @file
 * Contains \Drupal\custom_page\Entity\CustomPage.
 */

namespace Drupal\custom_page\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\custom_page\CustomPageInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Custom page entity.
 *
 * @ingroup custom_page
 *
 * @ContentEntityType(
 *   id = "custom_page",
 *   label = @Translation("Custom page"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\custom_page\CustomPageListBuilder",
 *     "views_data" = "Drupal\custom_page\Entity\CustomPageViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\custom_page\Form\CustomPageForm",
 *       "add" = "Drupal\custom_page\Form\CustomPageForm",
 *       "edit" = "Drupal\custom_page\Form\CustomPageForm",
 *       "delete" = "Drupal\custom_page\Form\CustomPageDeleteForm",
 *     },
 *     "access" = "Drupal\custom_page\CustomPageAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\custom_page\CustomPageHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "custom_page",
 *   admin_permission = "administer custom page entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/custom-page/{custom_page}",
 *     "add-form" = "/admin/structure/custom_page/add",
 *     "edit-form" = "/admin/structure/custom_page/{custom_page}/edit",
 *     "delete-form" = "/admin/structure/custom_page/{custom_page}/delete",
 *     "collection" = "/admin/structure/custom_page",
 *   },
 *   field_ui_base_route = "custom_page.settings"
 * )
 */
class CustomPage extends ContentEntityBase implements CustomPageInterface {
  use EntityChangedTrait;
  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += array(
      'user_id' => \Drupal::currentUser()->id(),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  public function getPath() {
    return $this->get('path')->value;
  }

  public function setPath($path) {
    $this->set('path', $path);
    return $this;
  }

  public function getMetaTitle() {
    return $this->get('meta_title')->value;
  }

  public function setMetaTitle($meta_title) {
    $this->set('meta_title', $meta_title);
    return $this;
  }

  public function getMetaDescription() {
    return $this->get('meta_description')->value;
  }

  public function setMetaDescription($meta_description) {
    $this->set('meta_description', $meta_description);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isPublished() {
    return (bool) $this->getEntityKey('status');
  }

  /**
   * {@inheritdoc}
   */
  public function setPublished($published) {
    $this->set('status', $published ? NODE_PUBLISHED : NODE_NOT_PUBLISHED);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the Custom page entity.'))
      ->setReadOnly(TRUE);
    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the Custom page entity.'))
      ->setReadOnly(TRUE);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Custom page entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDefaultValueCallback('Drupal\node\Entity\Node::getCurrentUserId')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => array(
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ),
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the Custom page entity.'))
      ->setSettings(array(
        'max_length' => 50,
        'text_processing' => 0,
      ))
      ->setDefaultValue('')
      ->setDisplayOptions('view', array(
        'label' => 'above',
        'type' => 'string',
        'weight' => -5,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'string_textfield',
        'weight' => -5,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['path'] = BaseFieldDefinition::create('path')
      ->setCustomStorage(TRUE)
      ->setLabel(t('URL alias'))
      ->setTranslatable(TRUE)
      ->setComputed(TRUE)
      ->setDisplayOptions('form', array(
        'type' => 'path',
        'weight' => 30,
      ))
      ->setDisplayConfigurable('form', TRUE);

    $fields['meta_title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Metatag Page Title '))
      ->setDescription(t('The metatag page title.'))
      ->setSettings(array(
        // 'max_length' => 50,
        'text_processing' => 0,
      ))
      ->setDefaultValue('')
      ->setDisplayOptions('form', array(
        'type' => 'string_textfield',
        'weight' => -3,
      ));

    $fields['meta_description'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Metatag Page Description '))
      ->setDescription(t('The metatag page description.'))
      ->setSettings(array(
        // 'max_length' => 50,
        'text_processing' => 0,
      ))
      ->setDefaultValue('')
      ->setDisplayOptions('form', array(
        'type' => 'string_textarea',
        'weight' => -2,
      ));

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Publishing status'))
      ->setDescription(t('A boolean indicating whether the Custom page is published.'))
      ->setDefaultValue(TRUE);

    $fields['langcode'] = BaseFieldDefinition::create('language')
      ->setLabel(t('Language code'))
      ->setDescription(t('The language code for the Custom page entity.'))
      ->setDisplayOptions('form', array(
        'type' => 'language_select',
        'weight' => 10,
      ))
      ->setDisplayConfigurable('form', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }

}
