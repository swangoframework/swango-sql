<?php
namespace Sql\Predicate;
use Sql\AbstractExpression;

class Between extends AbstractExpression implements PredicateInterface {
    protected string $specification = '%1$s BETWEEN %2$s AND %3$s';
    protected null|string|Expression $identifier = null;
    protected null|int|float|string $minValue = null;
    protected null|int|float|string $maxValue = null;
    /**
     * Constructor
     *
     * @param string $identifier
     * @param int|float|string $minValue
     * @param int|float|string $maxValue
     */
    public function __construct(null|string|Expression $identifier = null, null|int|float|string $minValue = null, null|int|float|string $maxValue = null) {
        if ($identifier) {
            $this->setIdentifier($identifier);
        }
        if ($minValue !== null) {
            $this->setMinValue($minValue);
        }
        if ($maxValue !== null) {
            $this->setMaxValue($maxValue);
        }
    }
    /**
     * Set identifier for comparison
     *
     * @param string $identifier
     * @return self Provides a fluent interface
     */
    public function setIdentifier(string|Expression $identifier): self {
        $this->identifier = $identifier;
        return $this;
    }
    /**
     * Get identifier of comparison
     *
     * @return null|string
     */
    public function getIdentifier(): null|string|Expression {
        return $this->identifier;
    }
    /**
     * Set minimum boundary for comparison
     *
     * @param int|float|string $minValue
     * @return self Provides a fluent interface
     */
    public function setMinValue(int|float|string $minValue): self {
        $this->minValue = $minValue;
        return $this;
    }
    /**
     * Get minimum boundary for comparison
     *
     * @return null|int|float|string
     */
    public function getMinValue(): null|int|float|string {
        return $this->minValue;
    }
    /**
     * Set maximum boundary for comparison
     *
     * @param int|float|string $maxValue
     * @return self Provides a fluent interface
     */
    public function setMaxValue(int|float|string $maxValue): self {
        $this->maxValue = $maxValue;
        return $this;
    }
    /**
     * Get maximum boundary for comparison
     *
     * @return null|int|float|string
     */
    public function getMaxValue(): null|int|float|string {
        return $this->maxValue;
    }
    /**
     * Set specification string to use in forming SQL predicate
     *
     * @param string $specification
     * @return self Provides a fluent interface
     */
    public function setSpecification(string $specification): self {
        $this->specification = $specification;
        return $this;
    }
    /**
     * Get specification string to use in forming SQL predicate
     *
     * @return string
     */
    public function getSpecification(): string {
        return $this->specification;
    }
    /**
     * Return "where" parts
     *
     * @return array
     */
    public function getExpressionData(): array {
        list($values[], $types[]) = $this->normalizeArgument($this->identifier, self::TYPE_IDENTIFIER);
        list($values[], $types[]) = $this->normalizeArgument($this->minValue, self::TYPE_VALUE);
        list($values[], $types[]) = $this->normalizeArgument($this->maxValue, self::TYPE_VALUE);
        return [
            [
                $this->getSpecification(),
                $values,
                $types
            ]
        ];
    }
}
