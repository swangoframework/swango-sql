<?php
namespace Sql\Predicate;
use Sql\Exception;
use Sql\Select;
use Sql\AbstractExpression;

class In extends AbstractExpression implements PredicateInterface {
    protected null|string|array $identifier = null;
    protected array|Select $valueSet;
    protected string $specification = '%s IN %s';
    protected string $valueSpecSpecification = '%%s IN (%s)';
    /**
     * Constructor
     *
     * @param null|string|array $identifier
     * @param null|array|Select $valueSet
     */
    public function __construct(string|array $identifier = null, array|Select $valueSet = null) {
        if ($identifier) {
            $this->setIdentifier($identifier);
        }
        if ($valueSet !== null) {
            $this->setValueSet($valueSet);
        }
    }
    /**
     * Set identifier for comparison
     *
     * @param string|array $identifier
     * @return self Provides a fluent interface
     */
    public function setIdentifier(string|array $identifier): self {
        $this->identifier = $identifier;

        return $this;
    }
    /**
     * Get identifier of comparison
     *
     * @return null|string|array
     */
    public function getIdentifier(): null|string|array {
        return $this->identifier;
    }
    /**
     * Set set of values for IN comparison
     *
     * @param array|Select $valueSet
     * @return self Provides a fluent interface
     * @throws Exception\InvalidArgumentException
     */
    public function setValueSet(array|Select $valueSet): self {
        $this->valueSet = $valueSet;
        return $this;
    }
    /**
     * Gets set of values in IN comparison
     *
     * @return array|Select
     */
    public function getValueSet(): array|Select {
        return $this->valueSet;
    }
    /**
     * Return array of parts for where statement
     *
     * @return array
     */
    public function getExpressionData(): array {
        $identifier = $this->getIdentifier();
        $values = $this->getValueSet();
        $replacements = [];

        if (is_array($identifier)) {
            $countIdentifier = count($identifier);
            $identifierSpecFragment = '(' . implode(', ', array_fill(0, $countIdentifier, '%s')) . ')';
            $types = array_fill(0, $countIdentifier, self::TYPE_IDENTIFIER);
            $replacements = $identifier;
        } else {
            $identifierSpecFragment = '%s';
            $replacements[] = $identifier;
            $types = [
                self::TYPE_IDENTIFIER
            ];
        }

        if ($values instanceof Select) {
            $specification = sprintf($this->specification, $identifierSpecFragment, '%s');
            $replacements[] = $values;
            $types[] = self::TYPE_VALUE;
        } else {
            foreach ($values as $argument) {
                [$replacements[], $types[]] = $this->normalizeArgument($argument, self::TYPE_VALUE);
            }
            $countValues = count($values);
            $valuePlaceholders = $countValues > 0 ? array_fill(0, $countValues, '%s') : [];
            $specification = sprintf($this->specification, $identifierSpecFragment,
                '(' . implode(', ', $valuePlaceholders) . ')');
        }

        return [
            [
                $specification,
                $replacements,
                $types
            ]
        ];
    }
}
