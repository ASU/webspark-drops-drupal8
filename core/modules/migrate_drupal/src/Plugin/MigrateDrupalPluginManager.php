<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Plugin\MigrateDrupalPluginManager.
 */

namespace Drupal\migrate_drupal\Plugin;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\migrate\Plugin\MigratePluginManager;

/**
 * Plugin manager for migration plugins.
 */
class MigrateDrupalPluginManager extends MigratePluginManager {

  /**
   * Constructs a MigratePluginManager object.
   *
   * @param string $type
   *   The type of the plugin: row, source, process, destination, entity_field,
   *   id_map.
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   * @param string $annotation
   *   (optional) The annotation class name. Defaults to
   *   'Drupal\Component\Annotation\PluginID'.
   */
  public function __construct($type, \Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler, $annotation = 'Drupal\Component\Annotation\PluginID') {
    parent::__construct($type, $namespaces, $cache_backend, $module_handler, $annotation);
    $this->additionalAnnotationNamespaces[] = 'Drupal\migrate_drupal\Plugin\Annotation';
  }

}
