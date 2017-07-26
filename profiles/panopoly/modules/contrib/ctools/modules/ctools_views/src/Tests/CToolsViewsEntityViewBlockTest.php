<?php
/**
 * @file
 * Contains \Drupal\ctools_views\Tests\CToolsViewsEntityViewBlockTest.
 */

namespace Drupal\ctools_views\Tests;

use Drupal\views_ui\Tests\UITestBase;
use Drupal\views\Tests\ViewTestData;
use Drupal\entity_test\Entity\EntityTest;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\taxonomy\Tests\TaxonomyTestTrait;
use Drupal\field\Tests\EntityReference\EntityReferenceTestTrait;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Entity\Element\EntityAutocomplete;

/**
 * Tests the ctools_views block display plugin
 * overriding settings from an entity-based View.
 *
 * @group ctools_views
 * @see \Drupal\ctools_views\Plugin\Display\Block
 */
class CToolsViewsEntityViewBlockTest extends UITestBase {

  use EntityReferenceTestTrait;
  use TaxonomyTestTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('ctools_views', 'ctools_views_test_views', 'taxonomy', 'options', 'datetime');

  /**
   * Views used by this test.
   *
   * @var array
   */
  public static $testViews = array('ctools_views_entity_test');

  /**
   * The block storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $storage;

  /**
   * The node entities used by the test.
   *
   * @var array
   */
  protected $entities = array();

  /**
   * The taxonomy_term entities used by the test.
   *
   * @var array
   */
  protected $terms = array();

  /**
   * @inheritdoc
   */
  protected function setUp() {
    parent::setUp();

    $this->drupalCreateContentType(array('type' => 'ctools_views', 'name' => 'Ctools views'));

    // Create test textfield
    entity_create('field_storage_config', array(
      'entity_type' => 'node',
      'field_name' => 'field_ctools_views_text',
      'type' => 'text',
      'cardinality' => 1,
    ))->save();
    entity_create('field_config', array(
      'entity_type' => 'node',
      'field_name' => 'field_ctools_views_text',
      'bundle' => 'ctools_views',
      'label' => 'Ctools Views test textfield',
      'translatable' => FALSE,
    ))->save();

    // Create a vocabulary named "Tags".
    $vocabulary = Vocabulary::create(array(
      'name' => 'Tags',
      'vid' => 'tags',
      'langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED,
    ));
    $vocabulary->save();
    $this->terms[] = $this->createTerm($vocabulary);
    $this->terms[] = $this->createTerm($vocabulary);
    $this->terms[] = $this->createTerm($vocabulary);

    $handler_settings = array(
      'target_bundles' => array(
        $vocabulary->id() => $vocabulary->id(),
      ),
    );
    $this->createEntityReferenceField('node', 'ctools_views', 'field_ctools_views_tags', 'Tags', 'taxonomy_term', 'default', $handler_settings, FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED);

    // Create list field
    entity_create('field_storage_config', array(
      'entity_type' => 'node',
      'field_name' => 'field_ctools_views_list',
      'type' => 'list_string',
      'cardinality' => FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED,
      'settings' => [
        'allowed_values' => [
          'item1' => "Item 1",
          'item2' => "Item 2",
          'item3' => "Item 3",
        ],
      ],
    ))->save();
    entity_create('field_config', array(
      'entity_type' => 'node',
      'field_name' => 'field_ctools_views_list',
      'bundle' => 'ctools_views',
      'label' => 'Ctools Views List',
      'translatable' => FALSE,
    ))->save();

    // Create date field
    entity_create('field_storage_config', array(
      'entity_type' => 'node',
      'field_name' => 'field_ctools_views_date',
      'type' => 'datetime',
      'cardinality' => 1,
      'settings' => [
        'datetime_type' => 'date',
      ],
    ))->save();
    entity_create('field_config', array(
      'entity_type' => 'node',
      'field_name' => 'field_ctools_views_date',
      'bundle' => 'ctools_views',
      'label' => 'Ctools Views Date',
      'translatable' => FALSE,
    ))->save();

    ViewTestData::createTestViews(get_class($this), array('ctools_views_test_views'));
    $this->storage = $this->container->get('entity.manager')->getStorage('block');

    // Create test entities
    $values = array(
      'type' => 'ctools_views',
      'title' => 'Test entity 1',
      'uid' => 1,
      'field_ctools_views_text' => array(
        'value' => 'text_1',
        'format' => 'plain_text',
      ),
      'field_ctools_views_tags' => array(
        'target_id' => $this->terms[0]->id(),
      ),
      'field_ctools_views_list' => array(
        'value' => 'item1',
      ),
      'field_ctools_views_date' => array(
        'value' => '1990-01-01',
      ),
    );
    $entity = entity_create('node', $values);
    $entity->save();
    $this->entities[] = $entity;

    $values = array(
      'type' => 'ctools_views',
      'title' => 'Test entity 2',
      'uid' => 1,
      'field_ctools_views_text' => array(
        'value' => 'text_2',
        'format' => 'plain_text',
      ),
      'field_ctools_views_tags' => array(
        'target_id' => $this->terms[1]->id(),
      ),
      'field_ctools_views_list' => array(
        'value' => 'item2',
      ),
      'field_ctools_views_date' => array(
        'value' => '2016-10-04',
      ),
    );
    $entity = entity_create('node', $values);
    $entity->save();
    $this->entities[] = $entity;

    $values = array(
      'type' => 'ctools_views',
      'title' => 'Test entity 3',
      'uid' => 0,
      'field_ctools_views_text' => array(
        'value' => 'text_1',
        'format' => 'plain_text',
      ),
      'field_ctools_views_tags' => array(
        'target_id' => $this->terms[2]->id(),
      ),
      'field_ctools_views_list' => array(
        'value' => 'item3',
      ),
      'field_ctools_views_date' => array(
        'value' => '2018-12-31',
      ),
    );
    $entity = entity_create('node', $values);
    $entity->save();
    $this->entities[] = $entity;
  }

  /**
   * Test ctools_views 'configure_filters' configuration with text field values.
   */
  public function testConfigureFiltersTextfield() {
    $default_theme = $this->config('system.theme')->get('default');

    // Get the "Configure block" form for our Views block.
    $this->drupalGet('admin/structure/block/add/views_block:ctools_views_entity_test-block_filter_text/' . $default_theme);
    $this->assertFieldByXPath('//input[@name="settings[override][filters][field_ctools_views_text_value][form][field_ctools_views_text_value]"]');

    // Add block to sidebar_first region with default settings.
    $edit = array();
    $edit['region'] = 'sidebar_first';
    $this->drupalPostForm('admin/structure/block/add/views_block:ctools_views_entity_test-block_filter_text/' . $default_theme, $edit, t('Save block'));

    // Assert configure_filters default settings.
    $this->drupalGet('<front>');
    // Check that the default settings return all results
    $this->assertEqual(3, count($this->xpath('//div[contains(@class, "view-display-id-block_filter_text")]//table//tbody//tr')));
    $this->assertFieldByXPath('//input[@name="field_ctools_views_text_value"]');

    // Override configure_filters settings.
    $edit = array();
    $edit['region'] = 'sidebar_first';
    $edit['settings[override][filters][field_ctools_views_text_value][form][field_ctools_views_text_value]'] = 'text_1';
    $this->drupalPostForm('admin/structure/block/manage/views_block__ctools_views_entity_test_block_filter_text', $edit, t('Save block'));

    $block = $this->storage->load('views_block__ctools_views_entity_test_block_filter_text');
    $config = $block->getPlugin()->getConfiguration();
    $this->assertEqual('text_1', $config['filter']['field_ctools_views_text_value']['value']['field_ctools_views_text_value'], "'configure_filters' setting is properly saved.");

    // Assert configure_filters overridden settings.
    $this->drupalGet('<front>');
    // Check that the overridden settings return proper results
    $this->assertEqual(2, count($this->xpath('//div[contains(@class, "view-display-id-block_filter_text")]//table//tbody//tr')));
    $this->assertNoFieldByXPath('//input[@name="field_ctools_views_text_value"]');
  }

  /**
   * Test ctools_views 'configure_filters' configuration with taxonomy term field values.
   */
  public function testConfigureFiltersTaxonomy() {
    $default_theme = $this->config('system.theme')->get('default');
    $tid = $this->terms[0]->id();

    // Get the "Configure block" form for our Views block.
    $this->drupalGet('admin/structure/block/add/views_block:ctools_views_entity_test-block_filter_tax/' . $default_theme);
    $this->assertFieldByXPath('//select[@name="settings[override][filters][field_ctools_views_tags_target_id][form][field_ctools_views_tags_target_id]"]');

    // Add block to sidebar_first region with default settings.
    $edit = array();
    $edit['region'] = 'sidebar_first';
    $this->drupalPostForm('admin/structure/block/add/views_block:ctools_views_entity_test-block_filter_tax/' . $default_theme, $edit, t('Save block'));

    // Assert configure_filters default settings.
    $this->drupalGet('<front>');
    // Check that the default settings return all results
    $this->assertEqual(3, count($this->xpath('//div[contains(@class, "view-display-id-block_filter_tax")]//table//tbody//tr')));
    $this->assertFieldByXPath('//select[@name="field_ctools_views_tags_target_id"]');

    // Override configure_filters settings.
    $edit = array();
    $edit['region'] = 'sidebar_first';
    $edit['settings[override][filters][field_ctools_views_tags_target_id][form][field_ctools_views_tags_target_id]'] = $tid;
    $this->drupalPostForm('admin/structure/block/manage/views_block__ctools_views_entity_test_block_filter_tax', $edit, t('Save block'));

    $block = $this->storage->load('views_block__ctools_views_entity_test_block_filter_tax');
    $config = $block->getPlugin()->getConfiguration();
    $this->assertEqual([$tid], $config['filter']['field_ctools_views_tags_target_id']['value'], "'configure_filters' setting is properly saved.");

    // Assert configure_filters overridden settings.
    $this->drupalGet('<front>');
    // Check that the overridden settings return proper results
    $this->assertEqual(1, count($this->xpath('//div[contains(@class, "view-display-id-block_filter_tax")]//table//tbody//tr')));
    $this->assertNoFieldByXPath('//select[@name="field_ctools_views_tags_target_id"]');
  }

  /**
   * Test ctools_views 'configure_filters' configuration with taxonomy term autocomplete.
   */
  public function testConfigureFiltersTaxonomyAutocomplete() {
    $default_theme = $this->config('system.theme')->get('default');
    $tid = $this->terms[0]->id();

    // Get the "Configure block" form for our Views block.
    $this->drupalGet('admin/structure/block/add/views_block:ctools_views_entity_test-block_filter_auto/' . $default_theme);
    $this->assertFieldByXPath('//input[@name="settings[override][filters][field_ctools_views_tags_target_id][form][field_ctools_views_tags_target_id]"]');

    // Add block to sidebar_first region with default settings.
    $edit = array();
    $edit['region'] = 'sidebar_first';
    $this->drupalPostForm('admin/structure/block/add/views_block:ctools_views_entity_test-block_filter_auto/' . $default_theme, $edit, t('Save block'));

    // Assert configure_filters default settings.
    $this->drupalGet('<front>');
    // Check that the default settings return all results
    $this->assertEqual(3, count($this->xpath('//div[contains(@class, "view-display-id-block_filter_auto")]//table//tbody//tr')));
    $this->assertFieldByXPath('//input[@name="field_ctools_views_tags_target_id"]');

    // Override configure_filters settings.
    $edit = array();
    $edit['region'] = 'sidebar_first';
    $filter_term = $this->terms[0];
    $filter_value = EntityAutocomplete::getEntityLabels([$filter_term]);
    $edit['settings[override][filters][field_ctools_views_tags_target_id][form][field_ctools_views_tags_target_id]'] = $filter_value;
    $this->drupalPostForm('admin/structure/block/manage/views_block__ctools_views_entity_test_block_filter_auto', $edit, t('Save block'));

    $block = $this->storage->load('views_block__ctools_views_entity_test_block_filter_auto');
    $config = $block->getPlugin()->getConfiguration();
    $this->assertEqual([$tid], $config['filter']['field_ctools_views_tags_target_id']['value'], "'configure_filters' setting is properly saved.");

    // Assert configure_filters overridden settings.
    $this->drupalGet('<front>');
    // Check that the overridden settings return proper results
    $this->assertEqual(1, count($this->xpath('//div[contains(@class, "view-display-id-block_filter_auto")]//table//tbody//tr')));
    $this->assertNoFieldByXPath('//input[@name="field_ctools_views_tags_target_id"]');
  }

  /**
   * Test ctools_views 'configure_filters' configuration with list field values.
   */
  public function testConfigureFiltersList() {
    $default_theme = $this->config('system.theme')->get('default');

    // Get the "Configure block" form for our Views block.
    $this->drupalGet('admin/structure/block/add/views_block:ctools_views_entity_test-block_filter_list/' . $default_theme);
    $this->assertFieldByXPath('//select[@name="settings[override][filters][field_ctools_views_list_value][form][field_ctools_views_list_value]"]');

    // Add block to sidebar_first region with default settings.
    $edit = array();
    $edit['region'] = 'sidebar_first';
    $this->drupalPostForm('admin/structure/block/add/views_block:ctools_views_entity_test-block_filter_list/' . $default_theme, $edit, t('Save block'));

    // Assert configure_filters default settings.
    $this->drupalGet('<front>');
    // Check that the default settings return all results
    $this->assertEqual(3, count($this->xpath('//div[contains(@class, "view-display-id-block_filter_list")]//table//tbody//tr')));
    $this->assertFieldByXPath('//select[@name="field_ctools_views_list_value"]');

    // Override configure_filters settings.
    $edit = array();
    $edit['region'] = 'sidebar_first';
    $edit['settings[override][filters][field_ctools_views_list_value][form][field_ctools_views_list_value]'] = 'item2';
    $this->drupalPostForm('admin/structure/block/manage/views_block__ctools_views_entity_test_block_filter_list', $edit, t('Save block'));

    $block = $this->storage->load('views_block__ctools_views_entity_test_block_filter_list');
    $config = $block->getPlugin()->getConfiguration();
    $this->assertEqual('item2', $config['filter']['field_ctools_views_list_value']['value']['field_ctools_views_list_value'], "'configure_filters' setting is properly saved.");

    // Assert configure_filters overridden settings.
    $this->drupalGet('<front>');
    // Check that the overridden settings return proper results
    $this->assertEqual(1, count($this->xpath('//div[contains(@class, "view-display-id-block_filter_list")]//table//tbody//tr')));
    $this->assertNoFieldByXPath('//select[@name="field_ctools_views_list_value"]');
  }

  /**
   * Test ctools_views 'configure_filters' configuration with date field values.
   */
  public function testConfigureFiltersDate() {
    $default_theme = $this->config('system.theme')->get('default');

    // Get the "Configure block" form for our Views block.
    $this->drupalGet('admin/structure/block/add/views_block:ctools_views_entity_test-block_filter_date/' . $default_theme);
    $this->assertFieldByXPath('//input[@name="settings[override][filters][field_ctools_views_date_value][form][field_ctools_views_date_value][min]"]');
    $this->assertFieldByXPath('//input[@name="settings[override][filters][field_ctools_views_date_value][form][field_ctools_views_date_value][max]"]');

    // Add block to sidebar_first region with default settings.
    $edit = array();
    $edit['region'] = 'sidebar_first';
    $this->drupalPostForm('admin/structure/block/add/views_block:ctools_views_entity_test-block_filter_date/' . $default_theme, $edit, t('Save block'));

    // Assert configure_filters default settings.
    $this->drupalGet('<front>');
    // Check that the default settings return all results
    $this->assertEqual(3, count($this->xpath('//div[contains(@class, "view-display-id-block_filter_date")]//table//tbody//tr')));
    $this->assertFieldByXPath('//input[@name="field_ctools_views_date_value[min]"]');
    $this->assertFieldByXPath('//input[@name="field_ctools_views_date_value[max]"]');

    // Override configure_filters settings.
    $edit = array();
    $edit['region'] = 'sidebar_first';
    $edit['settings[override][filters][field_ctools_views_date_value][form][field_ctools_views_date_value][min]'] = '2016-01-01';
    $edit['settings[override][filters][field_ctools_views_date_value][form][field_ctools_views_date_value][max]'] = '2016-12-31';
    $this->drupalPostForm('admin/structure/block/manage/views_block__ctools_views_entity_test_block_filter_date', $edit, t('Save block'));

    $block = $this->storage->load('views_block__ctools_views_entity_test_block_filter_date');
    $config = $block->getPlugin()->getConfiguration();
    $this->assertEqual(['min' => '2016-01-01', 'max' => '2016-12-31'], $config['filter']['field_ctools_views_date_value']['value']['field_ctools_views_date_value'], "'configure_filters' setting is properly saved.");

    // Assert configure_filters overridden settings.
    $this->drupalGet('<front>');
    // Check that the overridden settings return proper results
    $this->assertEqual(1, count($this->xpath('//div[contains(@class, "view-display-id-block_filter_date")]//table//tbody//tr')));
    $this->assertNoFieldByXPath('//input[@name="field_ctools_views_date_value[min]"]');
    $this->assertNoFieldByXPath('//input[@name="field_ctools_views_date_value[max]"]');
  }

}
