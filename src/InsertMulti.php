<?php
namespace Sql;
use Sql\Adapter\Platform\PlatformInterface;
use function Swlib\Http\stream_for;

class InsertMulti extends ReplaceMulti implements \countable {
    const SPECIFICATION_INSERT = 'insert';
    protected array $specifications = [
        self::SPECIFICATION_INSERT => 'INSERT INTO %1$s (%2$s) VALUES (%3$s)'
    ];
    protected bool $ignore = false;
    protected Expression|null $on_duplicate_key_update = null;
    public function withInsertIgnore(bool $ignore): self {
        $this->ignore = $ignore;
        return $this;
    }
    public function onDuplicateKeyUpdate(string|Expression|null $on_duplicate_key_update,
                                         mixed                  ...$parameters): self {
        if (is_string($on_duplicate_key_update)) {
            $this->on_duplicate_key_update = new Expression($on_duplicate_key_update, ...$parameters);
        } else {
            $this->on_duplicate_key_update = $on_duplicate_key_update;
        }
        return $this;
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
        if ($this->ignore) {
            $ret->write('INSERT IGNORE INTO ');
        } else {
            $ret->write('INSERT INTO ');
        }
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
        if (isset($this->on_duplicate_key_update)) {
            $ret->write(' ON DUPLICATE KEY UPDATE ');
            $ret->write($this->processExpression($this->on_duplicate_key_update, $platform));
        }
        return $ret->__toString();
    }
}
