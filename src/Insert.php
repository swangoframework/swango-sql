<?php
namespace Sql;
use Sql\Adapter\Platform\PlatformInterface;

class Insert extends AbstractSql {
    /**
     * Constants
     *
     * @const
     */
    const SPECIFICATION_INSERT = 'insert';
    const SPECIFICATION_SELECT = 'select';
    const VALUES_MERGE = 'merge';
    const VALUES_SET = 'set';
    /**
     * #@-
     */

    /**
     *
     * @var array Specification array
     */
    protected $specifications = [
        self::SPECIFICATION_INSERT => 'INSERT INTO %1$s (%2$s) VALUES (%3$s)',
        self::SPECIFICATION_SELECT => 'INSERT INTO %1$s %2$s %3$s'
    ];

    /**
     *
     * @var string|TableIdentifier
     */
    protected $table = null;
    protected $columns = [];

    /**
     *
     * @var array|Select
     */
    protected $select = null;

    /**
     * Constructor
     *
     * @param null|string|TableIdentifier $table
     */
    public function __construct($table = null) {
        if ($table) {
            $this->into($table);
        }
    }

    /**
     * Create INTO clause
     *
     * @param string|TableIdentifier $table
     * @return self Provides a fluent interface
     */
    public function into($table): self {
        $this->table = $table;
        return $this;
    }

    /**
     * Specify columns
     *
     * @param array $columns
     * @return self Provides a fluent interface
     */
    public function columns(array $columns): self {
        $this->columns = array_flip($columns);
        return $this;
    }

    /**
     * Specify values to insert
     *
     * @param array|Select $values
     * @param string $flag
     *            one of VALUES_MERGE or VALUES_SET; defaults to VALUES_SET
     * @return self Provides a fluent interface
     * @throws Exception\InvalidArgumentException
     */
    public function values($values, string $flag = self::VALUES_SET): self {
        if ($values instanceof Select) {
            if ($flag == self::VALUES_MERGE) {
                throw new Exception\InvalidArgumentException(
                    'A Sql\Select instance cannot be provided with the merge flag');
            }
            $this->select = $values;
            return $this;
        }

        if (! is_array($values)) {
            throw new Exception\InvalidArgumentException('values() expects an array of values or Sql\Select instance');
        }

        if ($this->select && $flag == self::VALUES_MERGE) {
            throw new Exception\InvalidArgumentException(
                'An array of values cannot be provided with the merge flag when a Sql\Select instance already ' .
                     'exists as the value source');
        }

        if ($flag == self::VALUES_SET) {
            $this->columns = $this->isAssocativeArray($values) ? $values : array_combine(array_keys($this->columns),
                array_values($values));
        } else {
            foreach ($values as $column=>$value) {
                $this->columns[$column] = $value;
            }
        }
        return $this;
    }

    /**
     * Simple test for an associative array
     *
     * @link http://stackoverflow.com/questions/173400/how-to-check-if-php-array-is-associative-or-sequential
     * @param array $array
     * @return bool
     */
    private function isAssocativeArray(array $array) {
        return array_keys($array) !== range(0, count($array) - 1);
    }

    /**
     * Create INTO SELECT clause
     *
     * @param Select $select
     * @return self
     */
    public function select(Select $select): self {
        return $this->values($select);
    }

    /**
     * Get raw state
     *
     * @param string $key
     * @return mixed
     */
    public function getRawState(string $key = null) {
        $rawState = [
            'table' => $this->table,
            'columns' => array_keys($this->columns),
            'values' => array_values($this->columns)
        ];
        return (isset($key) && array_key_exists($key, $rawState)) ? $rawState[$key] : $rawState;
    }
    protected function processInsert(PlatformInterface $platform) {
        if ($this->select) {
            return;
        }
        if (! $this->columns) {
            throw new Exception\InvalidArgumentException('values or select should be present');
        }

        $columns = [];
        $values = [];
        $i = 0;

        foreach ($this->columns as $column=>$value) {
            $columns[] = $platform->quoteIdentifier($column);
            $values[] = $this->resolveColumnValue($value, $platform);
        }
        return sprintf($this->specifications[static::SPECIFICATION_INSERT],
            $platform->shoueldQuoteOtherTable() ? $this->resolveTable($this->table, $platform) : $this->table,
            implode(', ', $columns), implode(', ', $values));
    }
    protected function processSelect(PlatformInterface $platform) {
        if (! $this->select) {
            return;
        }
        $selectSql = $this->processSubSelect($this->select, $platform);

        $columns = array_map([
            $platform,
            'quoteIdentifier'
        ], array_keys($this->columns));
        $columns = implode(', ', $columns);

        return sprintf($this->specifications[static::SPECIFICATION_SELECT],
            $platform->shoueldQuoteOtherTable() ? $this->resolveTable($this->table, $platform) : $this->table,
            $columns ? "($columns)" : "", $selectSql);
    }

    /**
     * Overloading: variable setting
     *
     * Proxies to values, using VALUES_MERGE strategy
     *
     * @param string $name
     * @param mixed $value
     * @return self Provides a fluent interface
     */
    public function __set($name, $value) {
        $this->columns[$name] = $value;
        return $this;
    }

    /**
     * Overloading: variable unset
     *
     * Proxies to values and columns
     *
     * @param string $name
     * @throws Exception\InvalidArgumentException
     * @return void
     */
    public function __unset($name) {
        if (! array_key_exists($name, $this->columns)) {
            throw new Exception\InvalidArgumentException(
                'The key ' . $name . ' was not found in this objects column list');
        }

        unset($this->columns[$name]);
    }

    /**
     * Overloading: variable isset
     *
     * Proxies to columns; does a column of that name exist?
     *
     * @param string $name
     * @return bool
     */
    public function __isset($name) {
        return array_key_exists($name, $this->columns);
    }

    /**
     * Overloading: variable retrieval
     *
     * Retrieves value by column name
     *
     * @param string $name
     * @throws Exception\InvalidArgumentException
     * @return mixed
     */
    public function __get($name) {
        if (! array_key_exists($name, $this->columns)) {
            throw new Exception\InvalidArgumentException(
                'The key ' . $name . ' was not found in this objects column list');
        }
        return $this->columns[$name];
    }
}
