<?php
namespace Sql\Adapter\Platform;
interface PlatformInterface {
    /**
     * Get name
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get quote identifier symbol
     *
     * @return string
     */
    public function getQuoteIdentifierSymbol(): string;

    /**
     * Quote identifier
     *
     * @param string $identifier
     * @return string
     */
    public function quoteIdentifier(string $identifier): string;

    /**
     * Quote identifier chain
     *
     * @param string|string[] $identifierChain
     * @return string
     */
    public function quoteIdentifierChain($identifierChain): string;

    /**
     * Get quote value symbol
     *
     * @return string
     */
    public function getQuoteValueSymbol(): string;

    /**
     * Quote value
     *
     * Will throw a notice when used in a workflow that can be considered "unsafe"
     *
     * @param string $value
     * @return string
     */
    public function quoteValue(string $value): string;

    /**
     * Quote Trusted Value
     *
     * The ability to quote values without notices
     *
     * @param
     *            $value
     * @return string
     */
    public function quoteTrustedValue(string $value): string;

    /**
     * Quote value list
     *
     * @param string|string[] $valueList
     * @return string
     */
    public function quoteValueList($valueList): string;

    /**
     * Get identifier separator
     *
     * @return string
     */
    public function getIdentifierSeparator(): string;

    /**
     * Quote identifier in fragment
     *
     * @param string $identifier
     * @param array $additionalSafeWords
     * @return string
     */
    public function quoteIdentifierInFragment(string $identifier, array $additionalSafeWords = []): string;
    public function shoueldQuoteOtherTable(): bool;
}
