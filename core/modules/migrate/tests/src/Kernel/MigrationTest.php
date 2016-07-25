<?php

namespace Drupal\Tests\migrate\Kernel;

use Drupal\KernelTests\KernelTestBase;

/**
 * Tests the migration plugin.
 *
 * @group migrate
 *
 * @coversDefaultClass \Drupal\migrate\Plugin\Migration
 */
class MigrationTest extends KernelTestBase {

  /**
   * We only need migrate itself.
   *
   * @var array
   */
  public static $modules = ['migrate'];

  /**
   * Tests Migration::set().
   *
   * @covers ::set
   */
  public function testSetInvalidation() {
    $migration = \Drupal::service('plugin.manager.migration')->createStubMigration([
      'source' => ['plugin' => 'empty'],
      'destination' => ['plugin' => 'entity:entity_view_mode'],
    ]);
    $this->assertEqual('empty', $migration->getSourcePlugin()->getPluginId());
    $this->assertEqual('entity:entity_view_mode', $migration->getDestinationPlugin()->getPluginId());

    // Test the source plugin is invalidated.
    $data_rows = [
      ['key' => '1', 'field1' => 'f1value1'],
    ];
    $ids = ['key' => ['type' => 'integer']];
    $definition = [
      'plugin' => 'embedded_data',
      'data_rows' => $data_rows,
      'ids' => $ids,
    ];
    $migration->set('source', $definition);
    $this->assertEqual('embedded_data', $migration->getSourcePlugin()->getPluginId());

    // Test the destination plugin is invalidated.
    $migration->set('destination', ['plugin' => 'null']);
    $this->assertEqual('null', $migration->getDestinationPlugin()->getPluginId());
  }

}
