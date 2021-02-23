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
            } else {
                $valueParameterArray = null;
            }
            $exists = new Expression($exists, $valueParameterArray);
        } elseif (! $exists instanceof Select && ! $exists instanceof Expression) {
            throw new Exception\InvalidArgumentException('$exists must be either an string, a Sql\Select object or a Sql\Expression object, ' .
                gettype($exists) . ' given');
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
        $replacements = [];
        $identifierSpecFragment = '%s';

        $specification = vsprintf($exists instanceof Select ? $this->specification : str_replace('%s', '(%s)',
            $this->specification), [
            $identifierSpecFragment,
            '%s'
        ]);
        $replacements[] = $exists;
        $types[] = self::TYPE_VALUE;

        return [
            [
                $specification,
                $replacements,
                $types
            ]
        ];
    }
}
