<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\MigrateDrupalServiceProvider.
 */

namespace Drupal\migrate_drupal;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Overrides the migration plugin manager service to add an annotation namespace
 * for Drupal source plugins.
 */
class MigrateDrupalServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    $definition = $container->getDefinition('plugin.manager.migrate.source');
    $definition->setClass('Drupal\migrate_drupal\Plugin\MigrateDrupalPluginManager');
  }

}
