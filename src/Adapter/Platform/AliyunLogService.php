<?php
namespace Sql\Adapter\Platform;
class AliyunLogService extends AbstractPlatform {
    /**
     *
     * {@inheritdoc}
     *
     */
    protected $quoteIdentifier = [
        '"',
        '"'
    ];

    /**
     *
     * {@inheritdoc}
     *
     */
    protected $quoteIdentifierTo = '\\"';

    /**
     * NOTE: Include dashes for MySQL only, need tests for others platforms
     *
     * @var string
     */
    protected $quoteIdentifierFragmentPattern = '/([^0-9,a-z,A-Z$_\-:])/i';
    public function quoteIdentifierInFragment(string $identifier, array $safeWords = []): string {
        return $identifier;
    }

    /**
     *
     * {@inheritdoc}
     *
     */
    public function quoteIdentifier(string $identifier): string {
        if ($identifier === 'log') {
            return $identifier;
        }

        return $this->quoteIdentifier[0] . str_replace($this->quoteIdentifier[0], $this->quoteIdentifierTo, $identifier) .
             $this->quoteIdentifier[1];
    }

    /**
     *
     * {@inheritdoc}
     *
     */
    public function getName(): string {
        return 'AliyunLogService';
    }

    /**
     *
     * {@inheritdoc}
     *
     */
    public function quoteIdentifierChain($identifierChain): string {
        return '"' . implode('"."', (array)str_replace('"', '\\"', $identifierChain)) . '"';
    }

    /**
     *
     * {@inheritdoc}
     *
     */
    public function quoteValue($value): string {
        if (is_string($value))
            return '\'' . addcslashes((string)$value, "\x00\n\r\\'\"\x1a") . '\'';
        if (is_bool($value))
            return (int)$value;
        return $value;
    }

    /**
     *
     * {@inheritdoc}
     *
     */
    public function quoteTrustedValue($value): string {
        return $this->quoteValue($value);
    }
    public function shoueldQuoteOtherTable(): bool {
        return false;
    }
}
