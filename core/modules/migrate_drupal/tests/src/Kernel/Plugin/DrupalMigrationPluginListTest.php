<?php

/**
 * @file
 * Contains \Drupal\Tests\migrate_drupal\Kernel\Plugin\DrupalMigrationPluginListTest.
 */

namespace Drupal\Tests\migrate_drupal\Kernel\Plugin;

use Drupal\Tests\migrate\Kernel\Plugin\MigrationPluginListTest;

/**
 * Tests the migration manager plugin with migrate_drupal enabled.
 *
 * @coversDefaultClass \Drupal\migrate\Plugin\MigratePluginManager
 * @group migrate
 */
class DrupalMigrationPluginListTest extends MigrationPluginListTest {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['migrate_drupal'];

}
