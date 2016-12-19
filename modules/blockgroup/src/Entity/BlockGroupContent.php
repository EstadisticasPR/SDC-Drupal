<?php

/**
 * @file
 * Contains Drupal\blockgroup\Entity\BlockGroupContent.
 */

namespace Drupal\blockgroup\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\blockgroup\BlockGroupContentInterface;
use Drupal\Core\Entity\EntityStorageInterface;

/**
 * Defines the Block group content entity.
 *
 * @ConfigEntityType(
 *   id = "block_group_content",
 *   label = @Translation("Block group content"),
 *   handlers = {
 *     "list_builder" = "Drupal\blockgroup\BlockGroupContentListBuilder",
 *     "form" = {
 *       "add" = "Drupal\blockgroup\Form\BlockGroupContentForm",
 *       "edit" = "Drupal\blockgroup\Form\BlockGroupContentForm",
 *       "delete" = "Drupal\blockgroup\Form\BlockGroupContentDeleteForm"
 *     }
 *   },
 *   config_prefix = "block_group_content",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/block_group_content/{block_group_content}",
 *     "edit-form" = "/admin/structure/block_group_content/{block_group_content}/edit",
 *     "delete-form" = "/admin/structure/block_group_content/{block_group_content}/delete",
 *     "collection" = "/admin/structure/visibility_group"
 *   }
 * )
 */
class BlockGroupContent extends ConfigEntityBase implements BlockGroupContentInterface {

  /**
   * The Block group content ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Block group content label.
   *
   * @var string
   */
  protected $label;

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);

    if (!$update) {
      $this->clearBlocksCaches();
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function postDelete(EntityStorageInterface $storage, array $entities) {
    parent::postDelete($storage, $entities);

    $entity_keys = array_keys($entities);
    if ($entity_keys) {
      foreach ($entity_keys as $entity_key) {
        $block = \Drupal\block\Entity\Block::load($entity_key);
        if ($block) {
          $block->delete();
        }
      }
    }
    self::clearBlocksCaches();
  }

  /**
   * Clear caches on delete and create for blocks to show regions and blocks.
   */
  public static function clearBlocksCaches() {
    /** @var \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler */
    $theme_handler = \Drupal::service('theme_handler');
    $theme_handler->refreshInfo();
    $module_handler = \Drupal::moduleHandler();
    $module_handler->load('blockgroup');
    $module_handler->invokeAll('rebuild');
    \Drupal::service('plugin.cache_clearer')->clearCachedDefinitions();
  }

}
