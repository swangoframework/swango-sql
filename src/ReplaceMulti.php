<?php
namespace Sql;
use Sql\Adapter\Platform\PlatformInterface;
use function Swlib\Http\stream_for;

class ReplaceMulti extends AbstractSql implements \countable {
    const SPECIFICATION_INSERT = 'insert';
    protected array $specifications = [
        self::SPECIFICATION_INSERT => 'REPLACE INTO %1$s (%2$s) VALUES (%3$s)'
    ];
    protected string|TableIdentifier|null $table = null;
    protected int $columns_count = 0;
    protected \SplFixedArray $columns;
    protected \SplQueue $values;
    /**
     * Constructor
     *
     * @param null|string|TableIdentifier $table
     */
    public function __construct(null|string|TableIdentifier $table = null) {
        if ($table) {
            $this->into($table);
        }
        $this->values = new \SplQueue();
    }
    public function count(): int {
        return count($this->values);
    }
    /**
     * Create INTO clause
     *
     * @param string|TableIdentifier $table
     * @return self Provides a fluent interface
     */
    public function into(string|TableIdentifier $table): self {
        $this->table = $table;
        return $this;
    }
    /**
     * Specify columns
     *
     * @param array $columns
     * @return self Provides a fluent interface
     */
    public function columns(string ...$columns): self {
        if (! $this->values->isEmpty()) {
            throw new Exception\RuntimeException('Must not change columns after add value');
        }
        $this->columns_count = count($columns);
        $this->columns = new \SplFixedArray($this->columns_count);
        foreach ($columns as $i => $key)
            $this->columns[$i] = $key;
        return $this;
    }
    public function addValue(array $values): self {
        if ($this->columns === null) {
            throw new Exception\RuntimeException('Must set columns before add value');
        }
        $container = new \SplFixedArray($this->columns_count);
        for ($i = 0; $i < $this->columns_count; ++$i) {
            $key = $this->columns[$i];
            $container[$i] = array_key_exists($key, $values) ? $values[$key] : null;
        }
        $this->values->enqueue($container);
        return $this;
    }
    /**
     * Get raw state
     *
     * @param string $key
     * @return mixed
     */
    public function getRawState(string $key = null): mixed {
        $rawState = [
            'table' => $this->table,
            'columns' => $this->columns,
            'values' => $this->values
        ];
        return (isset($key) && array_key_exists($key, $rawState)) ? $rawState[$key] : $rawState;
    }
    protected function processInsert(PlatformInterface $platform): string {
        if ($this->columns === null || $this->values->isEmpty()) {
            throw new Exception\InvalidArgumentException('columns and values should be present');
        }

        $columns_count = $this->columns_count;
        $columns = [];
        for ($i = 0; $i < $columns_count; ++$i)
            $columns[] = $platform->quoteIdentifier($this->columns[$i]);

        $ret = stream_for('');
        $ret->write('REPLACE INTO ');
        $ret->write($platform->shoueldQuoteOtherTable() ? $this->resolveTable($this->table, $platform) : $this->table);
        $ret->write(' (' . implode(', ', $columns) . ') VALUES ');

        $first = true;
        for ($container = $this->values->dequeue(); ; $container = $this->values->dequeue()) {
            if ($first) {
                $ret->write('(');
                $first = false;
            } else {
                $ret->write(',(');
            }
            $first2 = true;
            for ($i = 0; $i < $columns_count; ++$i) {
                if ($first2) {
                    $first2 = false;
                } else {
                    $ret->write(',');
                }
                $ret->write($this->resolveColumnValue($container[$i], $platform));
            }
            $ret->write(')');
            if ($this->values->isEmpty()) {
                break;
            }
        }
        return $ret->__toString();
    }
}
