<?php
namespace Sql\Predicate;
use Sql\Exception\RuntimeException;
use Sql\Select;

/**
 *
 * @property Predicate $and
 * @property Predicate $or
 * @property Predicate $AND
 * @property Predicate $OR
 * @property Predicate $NEST
 * @property Predicate $UNNEST
 */
class Predicate extends PredicateSet {
    protected ?Predicate $unnest = null;
    protected ?string $nextPredicateCombineOperator = null;
    /**
     * Begin nesting predicates
     *
     * @return Predicate
     */
    public function nest(): Predicate {
        $predicateSet = new Predicate();
        $predicateSet->setUnnest($this);
        $this->addPredicate($predicateSet, $this->nextPredicateCombineOperator ?? $this->defaultCombination);
        $this->nextPredicateCombineOperator = null;
        return $predicateSet;
    }
    /**
     * Indicate what predicate will be unnested
     *
     * @param Predicate $predicate
     * @return void
     */
    public function setUnnest(Predicate $predicate): void {
        $this->unnest = $predicate;
    }
    /**
     * Indicate end of nested predicate
     *
     * @return Predicate
     * @throws RuntimeException
     */
    public function unnest(): Predicate {
        if ($this->unnest === null) {
            throw new RuntimeException('Not nested');
        }
        $unnest = $this->unnest;
        $this->unnest = null;
        return $unnest;
    }
    /**
     * Create "Equal To" predicate
     *
     * Utilizes Operator predicate
     *
     * @param int|float|bool|string $left
     * @param int|float|bool|string $right
     * @param string $leftType
     *            TYPE_IDENTIFIER or TYPE_VALUE by default TYPE_IDENTIFIER {@see allowedTypes}
     * @param string $rightType
     *            TYPE_IDENTIFIER or TYPE_VALUE by default TYPE_VALUE {@see allowedTypes}
     * @return self Provides a fluent interface
     */
    public function equalTo(int|float|bool|string $left, int|float|bool|string $right, string $leftType = self::TYPE_IDENTIFIER, string $rightType = self::TYPE_VALUE): self {
        $this->addPredicate(new Operator($left, Operator::OPERATOR_EQUAL_TO, $right, $leftType, $rightType),
            $this->nextPredicateCombineOperator ?? $this->defaultCombination);
        $this->nextPredicateCombineOperator = null;

        return $this;
    }
    /**
     * Create "Not Equal To" predicate
     *
     * Utilizes Operator predicate
     *
     * @param int|float|bool|string $left
     * @param int|float|bool|string $right
     * @param string $leftType
     *            TYPE_IDENTIFIER or TYPE_VALUE by default TYPE_IDENTIFIER {@see allowedTypes}
     * @param string $rightType
     *            TYPE_IDENTIFIER or TYPE_VALUE by default TYPE_VALUE {@see allowedTypes}
     * @return self Provides a fluent interface
     */
    public function notEqualTo(int|float|bool|string $left, int|float|bool|string $right, string $leftType = self::TYPE_IDENTIFIER, string $rightType = self::TYPE_VALUE): self {
        $this->addPredicate(new Operator($left, Operator::OPERATOR_NOT_EQUAL_TO, $right, $leftType, $rightType),
            $this->nextPredicateCombineOperator ?? $this->defaultCombination);
        $this->nextPredicateCombineOperator = null;

        return $this;
    }
    /**
     * Create "Less Than" predicate
     *
     * Utilizes Operator predicate
     *
     * @param int|float|bool|string $left
     * @param int|float|bool|string $right
     * @param string $leftType
     *            TYPE_IDENTIFIER or TYPE_VALUE by default TYPE_IDENTIFIER {@see allowedTypes}
     * @param string $rightType
     *            TYPE_IDENTIFIER or TYPE_VALUE by default TYPE_VALUE {@see allowedTypes}
     * @return self Provides a fluent interface
     */
    public function lessThan(int|float|bool|string $left, int|float|bool|string $right, string $leftType = self::TYPE_IDENTIFIER, string $rightType = self::TYPE_VALUE): self {
        $this->addPredicate(new Operator($left, Operator::OPERATOR_LESS_THAN, $right, $leftType, $rightType),
            $this->nextPredicateCombineOperator ?? $this->defaultCombination);
        $this->nextPredicateCombineOperator = null;

        return $this;
    }
    /**
     * Create "Greater Than" predicate
     *
     * Utilizes Operator predicate
     *
     * @param int|float|bool|string $left
     * @param int|float|bool|string $right
     * @param string $leftType
     *            TYPE_IDENTIFIER or TYPE_VALUE by default TYPE_IDENTIFIER {@see allowedTypes}
     * @param string $rightType
     *            TYPE_IDENTIFIER or TYPE_VALUE by default TYPE_VALUE {@see allowedTypes}
     * @return self Provides a fluent interface
     */
    public function greaterThan(int|float|bool|string $left, int|float|bool|string $right, string $leftType = self::TYPE_IDENTIFIER, string $rightType = self::TYPE_VALUE): self {
        $this->addPredicate(new Operator($left, Operator::OPERATOR_GREATER_THAN, $right, $leftType, $rightType),
            $this->nextPredicateCombineOperator ?? $this->defaultCombination);
        $this->nextPredicateCombineOperator = null;

        return $this;
    }
    /**
     * Create "Less Than Or Equal To" predicate
     *
     * Utilizes Operator predicate
     *
     * @param int|float|bool|string $left
     * @param int|float|bool|string $right
     * @param string $leftType
     *            TYPE_IDENTIFIER or TYPE_VALUE by default TYPE_IDENTIFIER {@see allowedTypes}
     * @param string $rightType
     *            TYPE_IDENTIFIER or TYPE_VALUE by default TYPE_VALUE {@see allowedTypes}
     * @return self Provides a fluent interface
     */
    public function lessThanOrEqualTo(int|float|bool|string $left, int|float|bool|string $right, string $leftType = self::TYPE_IDENTIFIER, string $rightType = self::TYPE_VALUE): self {
        $this->addPredicate(new Operator($left, Operator::OPERATOR_LESS_THAN_OR_EQUAL_TO, $right, $leftType,
            $rightType), $this->nextPredicateCombineOperator ?? $this->defaultCombination);
        $this->nextPredicateCombineOperator = null;

        return $this;
    }
    /**
     * Create "Greater Than Or Equal To" predicate
     *
     * Utilizes Operator predicate
     *
     * @param int|float|bool|string $left
     * @param int|float|bool|string $right
     * @param string $leftType
     *            TYPE_IDENTIFIER or TYPE_VALUE by default TYPE_IDENTIFIER {@see allowedTypes}
     * @param string $rightType
     *            TYPE_IDENTIFIER or TYPE_VALUE by default TYPE_VALUE {@see allowedTypes}
     * @return self Provides a fluent interface
     */
    public function greaterThanOrEqualTo(int|float|bool|string $left, int|float|bool|string $right, string $leftType = self::TYPE_IDENTIFIER, string $rightType = self::TYPE_VALUE): self {
        $this->addPredicate(new Operator($left, Operator::OPERATOR_GREATER_THAN_OR_EQUAL_TO, $right, $leftType,
            $rightType), $this->nextPredicateCombineOperator ?? $this->defaultCombination);
        $this->nextPredicateCombineOperator = null;

        return $this;
    }
    /**
     * Create "Like" predicate
     *
     * Utilizes Like predicate
     *
     * @param string|Expression $identifier
     * @param string $like
     * @return self Provides a fluent interface
     */
    public function like(string|Expression $identifier, string $like): self {
        $this->addPredicate(new Like($identifier, $like),
            $this->nextPredicateCombineOperator ?? $this->defaultCombination);
        $this->nextPredicateCombineOperator = null;

        return $this;
    }
    /**
     * Create "notLike" predicate
     *
     * Utilizes In predicate
     *
     * @param string|Expression $identifier
     * @param string $notLike
     * @return self Provides a fluent interface
     */
    public function notLike(string|Expression $identifier, string $notLike): self {
        $this->addPredicate(new NotLike($identifier, $notLike),
            $this->nextPredicateCombineOperator ?? $this->defaultCombination);
        $this->nextPredicateCombineOperator = null;
        return $this;
    }
    /**
     * Create an expression, with parameter placeholders
     *
     * @param
     *            $expression
     * @param
     *            $parameters
     * @return self Provides a fluent interface
     */
    public function exists(string|Select|Expression $expression, mixed ...$parameters): self {
        $this->addPredicate(new Exists($expression, ...$parameters),
            $this->nextPredicateCombineOperator ?? $this->defaultCombination);
        $this->nextPredicateCombineOperator = null;

        return $this;
    }
    /**
     * Create an expression, with parameter placeholders
     *
     * @param
     *            $expression
     * @param
     *            $parameters
     * @return self Provides a fluent interface
     */
    public function notExists(string|Select|Expression $expression, mixed ...$parameters): self {
        $this->addPredicate(new NotExists($expression, ...$parameters),
            $this->nextPredicateCombineOperator ?? $this->defaultCombination);
        $this->nextPredicateCombineOperator = null;

        return $this;
    }
    /**
     * Create an expression, with parameter placeholders
     *
     * @param
     *            $expression
     * @param
     *            $parameters
     * @return self Provides a fluent interface
     */
    public function expression(string|Select|Expression $expression, mixed ...$parameters): self {
        $this->addPredicate(new Expression($expression, ...$parameters),
            $this->nextPredicateCombineOperator ?? $this->defaultCombination);
        $this->nextPredicateCombineOperator = null;

        return $this;
    }
    /**
     * Create "Literal" predicate
     *
     * Literal predicate, for parameters, use expression()
     *
     * @param string $literal
     * @return self Provides a fluent interface
     */
    public function literal(string $literal): self {
        $predicate = new Literal($literal);
        $this->addPredicate($predicate, $this->nextPredicateCombineOperator ?? $this->defaultCombination);
        $this->nextPredicateCombineOperator = null;

        return $this;
    }
    /**
     * Create "IS NULL" predicate
     *
     * Utilizes IsNull predicate
     *
     * @param string|Expression $identifier
     * @return self Provides a fluent interface
     */
    public function isNull(string|Expression $identifier): self {
        $this->addPredicate(new IsNull($identifier), $this->nextPredicateCombineOperator ?? $this->defaultCombination);
        $this->nextPredicateCombineOperator = null;

        return $this;
    }
    /**
     * Create "IS NOT NULL" predicate
     *
     * Utilizes IsNotNull predicate
     *
     * @param string|Expression $identifier
     * @return self Provides a fluent interface
     */
    public function isNotNull(string|Expression $identifier): self {
        $this->addPredicate(new IsNotNull($identifier),
            $this->nextPredicateCombineOperator ?? $this->defaultCombination);
        $this->nextPredicateCombineOperator = null;

        return $this;
    }
    /**
     * Create "IN" predicate
     *
     * Utilizes In predicate
     *
     * @param string|Expression $identifier
     * @param array|\Sql\Select $valueSet
     * @return self Provides a fluent interface
     */
    public function in(string|Expression $identifier, array|\Sql\Select $valueSet = null): self {
        $this->addPredicate(new In($identifier, $valueSet),
            $this->nextPredicateCombineOperator ?? $this->defaultCombination);
        $this->nextPredicateCombineOperator = null;

        return $this;
    }
    /**
     * Create "NOT IN" predicate
     *
     * Utilizes NotIn predicate
     *
     * @param string|Expression $identifier
     * @param array|\Sql\Select $valueSet
     * @return self Provides a fluent interface
     */
    public function notIn(string|Expression $identifier, array|\Sql\Select $valueSet = null): self {
        $this->addPredicate(new NotIn($identifier, $valueSet),
            $this->nextPredicateCombineOperator ?? $this->defaultCombination);
        $this->nextPredicateCombineOperator = null;

        return $this;
    }
    /**
     * Create "between" predicate
     *
     * Utilizes Between predicate
     *
     * @param string|Expression $identifier
     * @param int|float|string $minValue
     * @param int|float|string $maxValue
     * @return self Provides a fluent interface
     */
    public function between(string|Expression $identifier, int|float|string $minValue, int|float|string $maxValue): self {
        $this->addPredicate(new Between($identifier, $minValue, $maxValue),
            $this->nextPredicateCombineOperator ?? $this->defaultCombination);
        $this->nextPredicateCombineOperator = null;

        return $this;
    }
    /**
     * Create "NOT BETWEEN" predicate
     *
     * Utilizes NotBetween predicate
     *
     * @param string|Expression $identifier
     * @param int|float|string $minValue
     * @param int|float|string $maxValue
     * @return self Provides a fluent interface
     */
    public function notBetween(string|Expression $identifier, int|float|string $minValue, int|float|string $maxValue): self {
        $this->addPredicate(new NotBetween($identifier, $minValue, $maxValue),
            $this->nextPredicateCombineOperator ?? $this->defaultCombination);
        $this->nextPredicateCombineOperator = null;

        return $this;
    }
    /**
     * Use given predicate directly
     *
     * Contrary to {@link addPredicate()} this method respects formerly set
     * AND / OR combination operator, thus allowing generic predicates to be
     * used fluently within where chains as any other concrete predicate.
     *
     * @param PredicateInterface $predicate
     * @return self Provides a fluent interface
     */
    public function predicate(PredicateInterface $predicate): self {
        $this->addPredicate($predicate, $this->nextPredicateCombineOperator ?? $this->defaultCombination);
        $this->nextPredicateCombineOperator = null;

        return $this;
    }
    /**
     * Overloading
     *
     * Overloads "or", "and", "nest", and "unnest"
     *
     * @param string $name
     * @return self Provides a fluent interface
     */
    public function __get(string $name) {
        switch (strtolower($name)) {
            case 'or' :
                $this->nextPredicateCombineOperator = self::OP_OR;
                break;
            case 'and' :
                $this->nextPredicateCombineOperator = self::OP_AND;
                break;
            case 'nest' :
                return $this->nest();
            case 'unnest' :
                return $this->unnest();
        }
        return $this;
    }
}
