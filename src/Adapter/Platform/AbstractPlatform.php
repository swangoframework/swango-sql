<?php
namespace Sql\Adapter\Platform;
abstract class AbstractPlatform implements PlatformInterface {
    /**
     *
     * @var string[]
     */
    protected array $quoteIdentifier = [
        '"',
        '"'
    ];
    protected string $quoteIdentifierTo = '\'';
    protected bool $quoteIdentifiers = true;
    protected string $quoteIdentifierFragmentPattern = '/([^0-9,a-z,A-Z$_:])/i';
    /**
     *
     * {@inheritdoc}
     *
     */
    public function quoteIdentifierInFragment(string $identifier, array $safeWords = []): string {
        if (! $this->quoteIdentifiers) {
            return $identifier;
        }

        $safeWordsInt = [
            '*' => true,
            ' ' => true,
            '.' => true,
            'as' => true
        ];

        foreach ($safeWords as $sWord) {
            $safeWordsInt[strtolower($sWord)] = true;
        }

        $parts = preg_split($this->quoteIdentifierFragmentPattern, $identifier, -1,
            PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        $identifier = '';

        foreach ($parts as $part) {
            $identifier .= isset($safeWordsInt[strtolower($part)]) ? $part : $this->quoteIdentifier[0] .
                str_replace($this->quoteIdentifier[0], $this->quoteIdentifierTo, $part) . $this->quoteIdentifier[1];
        }

        return $identifier;
    }
    /**
     *
     * {@inheritdoc}
     *
     */
    public function quoteIdentifier(string $identifier): string {
        if (! $this->quoteIdentifiers) {
            return $identifier;
        }

        return $this->quoteIdentifier[0] .
            str_replace($this->quoteIdentifier[0], $this->quoteIdentifierTo, $identifier) . $this->quoteIdentifier[1];
    }
    /**
     *
     * {@inheritdoc}
     *
     */
    public function quoteIdentifierChain(string $identifierChain): string {
        return '"' . implode('"."', (array)str_replace('"', '\\"', $identifierChain)) . '"';
    }
    /**
     *
     * {@inheritdoc}
     *
     */
    public function getQuoteIdentifierSymbol(): string {
        return reset($this->quoteIdentifier);
    }
    /**
     *
     * {@inheritdoc}
     *
     */
    public function getQuoteValueSymbol(): string {
        return '\'';
    }
    /**
     *
     * {@inheritdoc}
     *
     */
    public function quoteValue(mixed $value): string {
        trigger_error('Attempting to quote a value in ' . get_class($this) .
            ' without extension/driver support can introduce security vulnerabilities in a production environment');
        return '\'' . addcslashes($value, "\x00\n\r\\'\"\x1a") . '\'';
    }
    /**
     *
     * {@inheritdoc}
     *
     */
    public function quoteTrustedValue(string $value): string {
        return '\'' . addcslashes($value, "\x00\n\r\\'\"\x1a") . '\'';
    }
    /**
     *
     * {@inheritdoc}
     *
     */
    public function quoteValueList(string|array $valueList): string {
        return implode(', ', array_map([
            $this,
            'quoteValue'
        ], (array)$valueList));
    }
    /**
     *
     * {@inheritdoc}
     *
     */
    public function getIdentifierSeparator(): string {
        return '.';
    }
    public function shoueldQuoteOtherTable(): bool {
        return true;
    }
}
