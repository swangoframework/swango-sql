<?php
namespace Sql;

/**
 */
class TableIdentifier {
    protected string $table;
    protected ?string $schema;
    /**
     *
     * @param string $table
     * @param null|string $schema
     */
    public function __construct(string $table, ?string $schema = null) {
        if ('' === $table) {
            throw new Exception\InvalidArgumentException("$table must be a valid table name, empty string given");
        }
        $this->table = $table;

        if (null === $schema) {
            $this->schema = null;
        } else {
            if (! (is_string($schema) || is_callable([
                    $schema,
                    '__toString'
                ]))) {
                throw new Exception\InvalidArgumentException(sprintf('$schema must be a valid schema name, parameter of type %s given',
                    is_object($schema) ? get_class($schema) : gettype($schema)));
            }

            $this->schema = (string)$schema;

            if ('' === $this->schema) {
                throw new Exception\InvalidArgumentException('$schema must be a valid schema name or null, empty string given');
            }
        }
    }
    /**
     *
     * @return string
     */
    public function getTable(): string {
        return $this->table;
    }
    /**
     *
     * @return bool
     */
    public function hasSchema(): bool {
        return $this->schema !== null;
    }
    /**
     *
     * @return null|string
     */
    public function getSchema(): ?string {
        return $this->schema;
    }
    public function getTableAndSchema(): array {
        return [
            $this->table,
            $this->schema
        ];
    }
}
