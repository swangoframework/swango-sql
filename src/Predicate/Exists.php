<?php
namespace Sql\Predicate;
use Sql\Exception;
use Sql\Select;
use Sql\AbstractExpression;

class Exists extends AbstractExpression implements PredicateInterface {
    protected Expression|Select $exists;
    protected string $specification = 'EXISTS %s';
    protected string $valueSpecSpecification = 'EXISTS (%s)';
    /**
     * Constructor
     *
     * @param null|string|Select|Expression $exists
     * @param mixed ...$valueParameter
     */
    public function __construct(null|string|Select|Expression $exists = null, mixed ...$valueParameter) {
        if ($exists !== null) {
            $this->setExists($exists, ...$valueParameter);
        }
    }
    /**
     * Set exists for EXISTS comparison
     *
     * @param string|Select|Expression $exists
     * @param mixed ...$valueParameter
     * @return self Provides a fluent interface
     * @throws Exception\InvalidArgumentException
     */
    public function setExists(string|Select|Expression $exists, mixed ...$valueParameter): self {
        if (is_string($exists)) {
            if (! empty($valueParameter)) {
                $valueParameter1 = current($valueParameter);
                $valueParameterArray = is_array($valueParameter1) ? $valueParameter1 : $valueParameter;
                $exists = new Expression($exists, $valueParameterArray);
            } else {
                $exists = new Expression($exists);
            }
        }
        $this->exists = $exists;

        return $this;
    }
    /**
     * Gets set of values in IN comparison
     *
     * @return Expression|Select
     */
    public function getExists(): Expression|Select {
        return $this->exists;
    }
    /**
     * Return array of parts for where statement
     *
     * @return array
     */
    public function getExpressionData(): array {
        $exists = $this->getExists();
        return [
            [
                $exists instanceof Select ? $this->specification : $this->valueSpecSpecification,
                [$exists],
                [self::TYPE_VALUE]
            ]
        ];
    }
}
