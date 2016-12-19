<?php

/**
 * @file
 * Contains Drupal\blockgroup\Plugin\Block\BlockGroup.
 */

namespace Drupal\blockgroup\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Render\Renderer;

/**
 * Provides a 'BlockGroup' block.
 *
 * @Block(
 *  id = "block_group",
 *  admin_label = @Translation("Block group"),
 *  deriver = "Drupal\blockgroup\Plugin\Derivative\BlockGroups"
 * )
 */
class BlockGroup extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The entity manager service.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * The entity renderer.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

  /**
   * Constructs a new BlockContentBlock.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityManagerInterface $entity_manager, Renderer $renderer) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->entityManager = $entity_manager;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
        $configuration, $plugin_id, $plugin_definition, $container->get('entity.manager'), $container->get('renderer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    $block_content = array();
    $uuid = $this->getDerivativeId();
    $block_data = $this->entityManager->loadEntityByUuid('block_group_content', $uuid);
    $blocks = entity_load_multiple_by_properties('block', array(
      'theme' => \Drupal::theme()->getActiveTheme()->getName(),
      'region' => $block_data->getOriginalId(),
    ));
    uasort($blocks, 'Drupal\block\Entity\Block::sort');
    foreach ($blocks as $block) {
      $block_loaded = \Drupal\block\Entity\Block::load($block->getOriginalId());
      $block_content[$block->getOriginalId()] = \Drupal::entityManager()
          ->getViewBuilder('block')
          ->view($block_loaded);
    }

    return array(
      '#markup' => $this->renderer->render($block_content),
      '#cache' => array('max-age' => 0),
    );
  }

}
