<?php
namespace Sql;
use Sql\Adapter\Platform\PlatformInterface;

/**
 *
 * @property Where $where
 */
class Update extends AbstractSql {
    /**
     * @#++
     * @const
     */
    const SPECIFICATION_UPDATE = 'update';
    const SPECIFICATION_SET = 'set';
    const SPECIFICATION_WHERE = 'where';
    const SPECIFICATION_JOIN = 'joins';
    const VALUES_MERGE = 'merge';
    const VALUES_SET = 'set';
    /**
     * @#-*
     */
    protected array $specifications = [
        self::SPECIFICATION_UPDATE => 'UPDATE %1$s',
        self::SPECIFICATION_JOIN => [
            '%1$s' => [
                [
                    3 => '%1$s JOIN %2$s ON %3$s',
                    'combinedby' => ' '
                ]
            ]
        ],
        self::SPECIFICATION_SET => 'SET %1$s',
        self::SPECIFICATION_WHERE => 'WHERE %1$s'
    ];
    protected string|TableIdentifier $table = '';
    protected bool $emptyWhereProtection = true;
    protected PriorityList $set;
    protected null|string|Where $where = null;
    protected Join $joins;
    public function __construct(null|string|TableIdentifier $table = null) {
        if ($table) {
            $this->table($table);
        }
        $this->where = new Where();
        $this->joins = new Join();
        $this->set = new PriorityList();
        $this->set->isLIFO(false);
    }
    /**
     * Specify table for statement
     */
    public function table(string|TableIdentifier $table): self {
        $this->table = $table;
        return $this;
    }
    /**
     * Set key/value pairs to update
     *
     * @param array $values
     *            Associative array of key values
     * @param string $flag
     *            One of the VALUES_* constants
     * @return self Provides a fluent interface
     * @throws Exception\InvalidArgumentException
     */
    public function set(array $values, string $flag = self::VALUES_SET): self {
        if ($flag == self::VALUES_SET) {
            $this->set->clear();
        }
        $priority = is_numeric($flag) ? $flag : 0;
        foreach ($values as $k => $v) {
            if (! is_string($k)) {
                throw new Exception\InvalidArgumentException('set() expects a string for the value key');
            }
            $this->set->insert($k, $v, $priority);
        }
        return $this;
    }
    /**
     * Create where clause
     *
     * @param Where|\Closure|string|array $predicate
     * @param string $combination
     *            One of the OP_* constants from Predicate\PredicateSet
     * @return self Provides a fluent interface
     * @throws Exception\InvalidArgumentException
     */
    public function where(Where|\Closure|string|array $predicate, string $combination = Predicate\PredicateSet::OP_AND): self {
        if ($predicate instanceof Where) {
            $this->where = $predicate;
        } else {
            $this->where->addPredicates($predicate, $combination);
        }
        return $this;
    }
    /**
     * Create join clause
     *
     * @param string|array|TableIdentifier $name
     *            A table name on which to join, or a single
     *            element associative array, of the form alias => table, or TableIdentifier instance
     * @param string|Predicate\Expression $on
     *            A specification describing the fields to join on.
     * @param string $type
     *            The JOIN type to use; see the JOIN_* constants.
     * @return self Provides a fluent interface
     * @throws Exception\InvalidArgumentException for invalid $name values.
     */
    public function join(string|array|TableIdentifier $name, string|Predicate\Expression $on, string $type = Join::JOIN_INNER): self {
        $this->joins->join($name, $on, [], $type);

        return $this;
    }
    public function getRawState(?string $key = null) {
        $rawState = [
            'emptyWhereProtection' => $this->emptyWhereProtection,
            'table' => $this->table,
            'set' => $this->set->toArray(),
            'where' => $this->where,
            'joins' => $this->joins
        ];
        return (isset($key) && array_key_exists($key, $rawState)) ? $rawState[$key] : $rawState;
    }
    protected function processUpdate(PlatformInterface $platform): string {
        return sprintf($this->specifications[static::SPECIFICATION_UPDATE],
            $this->resolveTable($this->table, $platform));
    }
    protected function processSet(PlatformInterface $platform) {
        $setSql = [];
        $i = 0;
        foreach ($this->set as $column => $value) {
            $prefix = $this->resolveColumnValue([
                'column' => $column,
                'fromTable' => '',
                'isIdentifier' => true
            ], $platform, 'column');
            $prefix .= ' = ';
            $setSql[] = $prefix . $this->resolveColumnValue($value, $platform);
        }

        return sprintf($this->specifications[static::SPECIFICATION_SET], implode(', ', $setSql));
    }
    protected function processWhere(PlatformInterface $platform) {
        if ($this->where->count() == 0) {
            return;
        }
        return sprintf($this->specifications[static::SPECIFICATION_WHERE],
            $this->processExpression($this->where, $platform, 'where'));
    }
    protected function processJoins(PlatformInterface $platform) {
        return $this->processJoin($this->joins, $platform);
    }
    /**
     * Variable overloading
     * Proxies to "where" only
     *
     * @param string $name
     * @return mixed
     */
    public function __get(string $name): mixed {
        if (strtolower($name) == 'where') {
            return $this->where;
        }
    }
    /**
     * __clone
     * Resets the where object each time the Update is cloned.
     *
     * @return void
     */
    public function __clone() {
        $this->where = clone $this->where;
        $this->set = clone $this->set;
    }
}
