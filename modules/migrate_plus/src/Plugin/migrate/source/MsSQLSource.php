<?php

namespace Drupal\migrate\Plugin\migrate\source;

use Drupal\Core\Database\Database;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\State\StateInterface;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Plugin\migrate\id_map\Sql;
use Drupal\migrate\Plugin\MigrateIdMapInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Sources whose data may be fetched via DBTNG.
 *
 * By default, an existing database connection with key 'migrate' and target
 * 'default' is used. These may be overridden with explicit 'key' and/or
 * 'target' configuration keys. In addition, if the configuration key 'database'
 * is present, it is used as a database connection information array to define
 * the connection.
 */
class MsSQLSource extends SourcePluginBase {

  /**
   * The query string.
   *
   * @var \Drupal\Core\Database\Query\SelectInterface
   */
  protected $query;

  /**
   * The database object.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;


  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration = NULL) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $migration,
      $container->get('state')
    );
  }

  /**
   * Prints the query string when the object is used as a string.
   *
   * @return string
   *   The query string.
   */
  public function __toString() {
    return (string) $this->query();
  }

  /**
   * Gets the database connection object.
   *
   * @return \Drupal\Core\Database\Connection
   *   The database connection.
   */
  public function getDatabase() {
    if (!isset($this->database)) {
      setUpDatabase();
    }
    return $this->database;
  }

  /**
   * Gets a connection to the referenced database.
   *
   * This method will add the database connection if necessary.
   *
  */
  protected function setUpDatabase() {
      ini_set('mssql.charset', 'UTF-8');
      try {
          $this->database = new \PDO(
              sprintf(
                  "dblib:host=%s;dbname=%s",
                  '192.168.1.107',
                  'iepr_db'
              ),
              'sa',
              'v13ws0n1c!'
          );
          $this->database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      } catch (PDOException $e) {
          echo "There was a problem connecting. " . $e->getMessage();
      }
  }

  /**
   * Wrapper for database select.
   */
  protected function select($table, $alias = NULL, array $options = array()) {
    $options['fetch'] = \PDO::FETCH_ASSOC;
    return $this->getDatabase()->select($table, $alias, $options);
  }


  /**
   * {@inheritdoc}
   */
  public function fields() {
    if ($this->count() > 0) {
      $first_row = reset($this->dataRows);
      $field_names = array_keys($first_row);
      return array_combine($field_names, $field_names);
    }
    else {
      return [];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function initializeIterator() {
    return new \ArrayIterator($this->dataRows);
  }

  /**
   * {@inheritdoc}
   */
  public function __toString() {
    return 'Embedded data';
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return $this->ids;
  }

  /**
   * {@inheritdoc}
   */
  public function count() {
    return count($this->dataRows);
  }

}
