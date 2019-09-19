<?php
namespace Sql\Adapter\Platform;
use Sql\Adapter\Exception;

class Mysql extends AbstractPlatform {
    /**
     *
     * {@inheritdoc}
     *
     */
    protected $quoteIdentifier = [
        '`',
        '`'
    ];

    /**
     *
     * {@inheritdoc}
     *
     */
    protected $quoteIdentifierTo = '``';

    /**
     *
     * @var \Swoole\Coroutine\MySQL|\mysqli|\PDO
     */
    protected $resource = null;

    /**
     * NOTE: Include dashes for MySQL only, need tests for others platforms
     *
     * @var string
     */
    protected $quoteIdentifierFragmentPattern = '/([^0-9,a-z,A-Z$_\-:])/i';

    /**
     *
     * @param null|\Swoole\Coroutine\MySQL|\Sql\Adapter\Driver\Mysqli\Mysqli|\Sql\Adapter\Driver\Pdo\Pdo|\mysqli|\PDO $driver
     */
    public function __construct($driver = null) {
        if ($driver) {
            $this->setDriver($driver);
        }
    }

    /**
     *
     * @param \Swoole\Coroutine\MySQL|\Sql\Adapter\Driver\Mysqli\Mysqli|\Sql\Adapter\Driver\Pdo\Pdo|\mysqli|\PDO $driver
     * @return self Provides a fluent interface
     * @throws \Sql\Adapter\Exception\InvalidArgumentException
     */
    public function setDriver($driver): self {
        // handle Mysql drivers
        if ($driver instanceof \Swoole\Coroutine\MySQL || ($driver instanceof \mysqli) ||
             ($driver instanceof \PDO && $driver->getAttribute(\PDO::ATTR_DRIVER_NAME) == 'mysql')) {
            $this->resource = $driver;
            return $this;
        }

        throw new Exception\InvalidArgumentException(
            '$driver must be a Mysqli or Mysql PDO Sql\Adapter\Driver, Mysqli instance or MySQL PDO instance');
    }

    /**
     *
     * {@inheritdoc}
     *
     */
    public function getName(): string {
        return 'MySQL';
    }

    /**
     *
     * {@inheritdoc}
     *
     */
    public function quoteIdentifierChain($identifierChain): string {
        return '`' . implode('`.`', (array)str_replace('`', '``', $identifierChain)) . '`';
    }

    /**
     *
     * {@inheritdoc}
     *
     */
    public function quoteValue(string $value): string {
        if ($this->resource instanceof \Swoole\Coroutine\MySQL) {
            return '\'' . $this->resource->escape($value) . '\'';
        }
        if ($this->resource instanceof \mysqli) {
            return '\'' . $this->resource->real_escape_string($value) . '\'';
        }
        if ($this->resource instanceof \PDO) {
            return $this->resource->quote($value);
        }
        return parent::quoteValue($value);
    }

    /**
     *
     * {@inheritdoc}
     *
     */
    public function quoteTrustedValue(string $value): string {
        if ($this->resource instanceof \Swoole\Coroutine\MySQL) {
            return '\'' . $this->resource->escape($value) . '\'';
        }
        if ($this->resource instanceof \mysqli) {
            return '\'' . $this->resource->real_escape_string($value) . '\'';
        }
        if ($this->resource instanceof \PDO) {
            return $this->resource->quote($value);
        }
        return parent::quoteTrustedValue($value);
    }
}
