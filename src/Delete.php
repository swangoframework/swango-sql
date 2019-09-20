<?php
namespace Sql;
use Sql\Adapter\Platform\PlatformInterface;

/**
 *
 * @property Where $where
 */
class Delete extends AbstractSql {
    /**
     * @#+
     * @const
     */
    const SPECIFICATION_DELETE = 'delete';
    const SPECIFICATION_WHERE = 'where';
    /**
     * @#-
     */

    /**
     *
     * {@inheritdoc}
     *
     */
    protected $specifications = [
        self::SPECIFICATION_DELETE => 'DELETE FROM %1$s',
        self::SPECIFICATION_WHERE => 'WHERE %1$s'
    ];

    /**
     *
     * @var string|TableIdentifier
     */
    protected $table = '';

    /**
     *
     * @var bool
     */
    protected $emptyWhereProtection = true;

    /**
     *
     * @var array
     */
    protected $set = [];

    /**
     *
     * @var null|string|Where
     */
    protected $where = null;

    /**
     * Constructor
     *
     * @param null|string|TableIdentifier $table
     */
    public function __construct($table = null) {
        if ($table) {
            $this->from($table);
        }
        $this->where = new Where();
    }

    /**
     * Create from statement
     *
     * @param string|TableIdentifier $table
     * @return self Provides a fluent interface
     */
    public function from($table): self {
        $this->table = $table;
        return $this;
    }

    /**
     *
     * @param null $key
     *
     * @return mixed
     */
    public function getRawState(string $key = null) {
        $rawState = [
            'emptyWhereProtection' => $this->emptyWhereProtection,
            'table' => $this->table,
            'set' => $this->set,
            'where' => $this->where
        ];
        return (isset($key) && array_key_exists($key, $rawState)) ? $rawState[$key] : $rawState;
    }

    /**
     * Create where clause
     *
     * @param Where|\Closure|string|array $predicate
     * @param string $combination
     *            One of the OP_* constants from Predicate\PredicateSet
     *
     * @return self Provides a fluent interface
     */
    public function where($predicate, $combination = Predicate\PredicateSet::OP_AND): self {
        if ($predicate instanceof Where) {
            $this->where = $predicate;
        } else {
            $this->where->addPredicates($predicate, $combination);
        }
        return $this;
    }

    /**
     *
     * @param PlatformInterface $platform
     * @return string
     */
    protected function processDelete(PlatformInterface $platform) {
        return sprintf($this->specifications[static::SPECIFICATION_DELETE],
            $this->resolveTable($this->table, $platform));
    }

    /**
     *
     * @param PlatformInterface $platform
     *
     * @return null|string
     */
    protected function processWhere(PlatformInterface $platform) {
        if ($this->where->count() == 0) {
            return;
        }

        return sprintf($this->specifications[static::SPECIFICATION_WHERE],
            $this->processExpression($this->where, $platform, 'where'));
    }

    /**
     * Property overloading
     *
     * Overloads "where" only.
     *
     * @param string $name
     *
     * @return Where|null
     */
    public function __get($name) {
        switch (strtolower($name)) {
            case 'where' :
                return $this->where;
        }
    }
}
