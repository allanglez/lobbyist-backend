<?php

namespace Drupal\Tests\feeds\Kernel\Feeds\Target;

use Drupal\feeds\Plugin\Type\Processor\ProcessorInterface;
use Drupal\node\Entity\Node;
use Drupal\Tests\feeds\Kernel\FeedsKernelTestBase;

/**
 * Tests for mapping to text and numeric fields.
 *
 * @group feeds
 */
class FieldTest extends FeedsKernelTestBase {

  /**
   * The feed type to test with.
   *
   * @var \Drupal\feeds\FeedTypeInterface
   */
  protected $feedType;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Add fields.
    $this->setUpBodyField();
    $this->createFieldWithStorage('field_alpha');
    $this->createFieldWithStorage('field_beta', [
      'type' => 'integer',
    ]);
    $this->createFieldWithStorage('field_gamma', [
      'type' => 'decimal',
    ]);
    $this->createFieldWithStorage('field_delta', [
      'type' => 'float',
    ]);

    // Create and configure feed type.
    $this->feedType = $this->createFeedTypeForCsv([
      'title' => 'title',
      'body' => 'body',
      'alpha' => 'alpha',
      'beta' => 'beta',
      'gamma' => 'gamma',
      'delta' => 'delta',
    ], [
      'mappings' => [
        [
          'target' => 'title',
          'map' => ['value' => 'title'],
        ],
        [
          'target' => 'body',
          'map' => ['value' => 'body'],
          'settings' => [
            'format' => 'plain_text',
          ],
        ],
        [
          'target' => 'field_alpha',
          'map' => ['value' => 'alpha'],
        ],
        [
          'target' => 'field_beta',
          'map' => ['value' => 'beta'],
        ],
        [
          'target' => 'field_gamma',
          'map' => ['value' => 'gamma'],
        ],
        [
          'target' => 'field_delta',
          'map' => ['value' => 'delta'],
        ],
      ],
    ]);
  }

  /**
   * Configures display of fields.
   */
  protected function setUpFieldDisplay() {
    $this->installConfig(['system']);

    $formats = $this->container->get('entity_type.manager')
      ->getStorage('date_format')
      ->loadMultiple(['long', 'medium', 'short']);
    $formats['long']->setPattern('l, j. F Y - G:i')->save();
    $formats['medium']->setPattern('j. F Y - G:i')->save();
    $formats['short']->setPattern('Y M j - g:ia')->save();

    entity_get_display('node', 'article', 'default')
      ->setComponent('field_alpha', [
        'type' => 'text_default',
        'label' => 'above',
      ])
      ->setComponent('field_beta', [
        'type' => 'number_integer',
        'label' => 'above',
      ])
      ->setComponent('field_gamma', [
        'type' => 'number_decimal',
        'label' => 'above',
      ])
      ->setComponent('field_delta', [
        'type' => 'number_decimal',
        'label' => 'above',
      ])
      ->save();
  }

  /**
   * Basic test loading a double entry CSV file.
   */
  public function test() {
    // Import CSV file.
    $feed = $this->createFeed($this->feedType->id(), [
      'source' => $this->resourcesPath() . '/csv/content.csv',
    ]);
    $feed->import();
    $this->assertNodeCount(2);

    // Check the two imported nodes.
    $expected_values_per_node = [
      1 => [
        'body' => 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat.',
        'field_alpha' => 'Lorem',
        'field_beta' => '42',
        'field_gamma' => '4.20',
        'field_delta' => '3.14159',
      ],
      2 => [
        'body' => 'Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat.',
        'field_alpha' => 'Ut wisi',
        'field_beta' => '32',
        'field_gamma' => '1.20',
        'field_delta' => '5.62951',
      ],
    ];
    $this->checkValues($expected_values_per_node);
  }

  /**
   * Tests if values are cleared out when an empty value is provided.
   */
  public function testClearOutValues() {
    $this->setUpFieldDisplay();

    // Add mapping to GUID and set that column as unique.
    $this->feedType->addCustomSource('guid', [
      'label' => 'GUID',
      'value' => 'guid',
    ]);
    $this->feedType->addMapping([
      'target' => 'feeds_item',
      'map' => ['guid' => 'guid'],
      'unique' => ['guid' => TRUE],
    ]);
    $this->feedType->save();

    // Import CSV file.
    $feed = $this->createFeed($this->feedType->id(), [
      'source' => $this->resourcesPath() . '/csv/content.csv',
    ]);
    $feed->import();
    $this->assertNodeCount(2);

    // Check the two imported nodes.
    $expected_values_per_node = [
      1 => [
        'body' => 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat.',
        'field_alpha' => 'Lorem',
        'field_beta' => '42',
        'field_gamma' => '4.20',
        'field_delta' => '3.14159',
      ],
      2 => [
        'body' => 'Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat.',
        'field_alpha' => 'Ut wisi',
        'field_beta' => '32',
        'field_gamma' => '1.20',
        'field_delta' => '5.62951',
      ],
    ];
    $this->checkValues($expected_values_per_node);

    // Configure feed type to update existing values.
    $this->feedType->getProcessor()->setConfiguration([
      'update_existing' => ProcessorInterface::UPDATE_EXISTING,
    ] + $this->feedType->getProcessor()->getConfiguration());
    $this->feedType->save();

    // Import CSV file with empty values.
    $feed = $this->createFeed($this->feedType->id(), [
      'source' => $this->resourcesPath() . '/csv/content_empty.csv',
    ]);
    $feed->import();
    $this->assertNodeCount(2);

    // Check if all values were cleared out for node 1 and
    // that for node 2 all values were set to '0'.
    $expected_values_per_node_empty = [
      1 => [
        'body' => '',
        'field_alpha' => '',
        'field_beta' => '',
        'field_gamma' => '',
        'field_delta' => '',
      ],
      2 => [
        'body' => 0,
        'field_alpha' => 0,
        'field_beta' => 0,
        'field_gamma' => 0,
        'field_delta' => 0,
      ],
    ];
    $this->checkValues($expected_values_per_node_empty);

    $field_labels = [
      'field_alpha label',
      'field_beta label',
      'field_gamma label',
      'field_delta label',
    ];

    // Check for node 1 if labels are no longer shown.
    $rendered_content = $this->renderNode(Node::load(1));
    foreach ($field_labels as $label) {
      $this->assertNotContains($label, $rendered_content);
    }

    // Check for node 2 if labels are still shown.
    $rendered_content = $this->renderNode(Node::load(2));
    foreach ($field_labels as $label) {
      $this->assertContains($label, $rendered_content);
    }

    // Re-import the first file again.
    $feed = $this->createFeed($this->feedType->id(), [
      'source' => $this->resourcesPath() . '/csv/content.csv',
    ]);
    $feed->import();

    // Check if the two imported nodes have content again.
    $this->checkValues($expected_values_per_node);

    // Import CSV file with non-existent values.
    $feed = $this->createFeed($this->feedType->id(), [
      'source' => $this->resourcesPath() . '/csv/content_non_existent.csv',
    ]);
    $feed->import();

    // Check if all values were cleared out for node 1.
    $expected_values_per_node_non_existent = [
      1 => [
        'body' => '',
        'field_alpha' => '',
        'field_beta' => '',
        'field_gamma' => '',
        'field_delta' => '',
      ],
    ];
    $this->checkValues($expected_values_per_node_non_existent);
    // Check if labels for fields that should be cleared out are not shown.
    $rendered_content = $this->renderNode(Node::load(1));
    foreach ($field_labels as $label) {
      $this->assertNotContains($label, $rendered_content);
    }
  }

  /**
   * Checks the field values.
   *
   * @param array $expected_values_per_node
   *   The expected field values, per node ID.
   */
  protected function checkValues(array $expected_values_per_node) {
    foreach ($expected_values_per_node as $node_id => $expected_values) {
      $node = Node::load($node_id);
      foreach ($expected_values as $field_name => $value) {
        $this->assertEquals($value, $node->{$field_name}->value);
      }
    }
  }

  /**
   * Renders the given node and returns the result.
   *
   * @param \Drupal\node\Entity\Node $node
   *   The node to render.
   *
   * @return string
   *   The rendered content.
   */
  protected function renderNode(Node $node) {
    $display = entity_get_display($node->getEntityTypeId(), $node->bundle(), 'default');
    $content = $display->build($node);
    return (string) $this->container->get('renderer')->renderRoot($content);
  }

}
