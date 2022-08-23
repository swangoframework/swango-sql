<?php
namespace Sql\Predicate;
use Sql\AbstractExpression;
use Sql\Exception;
use Sql\Select;
class IfExpression extends AbstractExpression implements PredicateInterface {
    protected null|Expression|Select|PredicateInterface $condition = null;
    protected null|Expression|Select|PredicateInterface $value_if_true = null;
    protected null|Expression|Select|PredicateInterface $value_if_false = null;
    protected string $specification = 'IF(%1$s, %2$s, %3$s)';
    /**
     * Constructor
     *
     * @param null|string|Select|Expression|PredicateInterface $condition
     * @param mixed ...$valueParameter
     */
    public function __construct(null|string|Select|Expression|PredicateInterface $condition = null,
                                mixed                                            ...$valueParameter) {
        if ($condition !== null) {
            $this->setCondition($condition, ...$valueParameter);
        }
    }
    /**
     * Set condition for IF comparison
     *
     * @param null|string|Select|Expression|PredicateInterface $condition
     * @param mixed ...$valueParameter
     * @return self Provides a fluent interface
     * @throws Exception\InvalidArgumentException
     */
    public function setCondition(null|string|Select|Expression|PredicateInterface $condition,
                                 mixed                                            ...$valueParameter): self {
        if (is_string($condition)) {
            if (! empty($valueParameter)) {
                $valueParameter1 = current($valueParameter);
                $valueParameterArray = is_array($valueParameter1) ? $valueParameter1 : $valueParameter;
                $condition = new Expression($condition, $valueParameterArray);
            } else {
                $condition = new Expression($condition);
            }
        }
        $this->condition = $condition;

        return $this;
    }
    /**
     * Gets set of values in IN comparison
     *
     * @return null|Expression|Select
     */
    public function getCondition(): null|Expression|Select|PredicateInterface {
        return $this->condition;
    }
    /**
     * Set value_if_true for IF comparison
     *
     * @param null|string|Select|Expression|PredicateInterface $value_if_true
     * @param mixed ...$valueParameter
     * @return self Provides a fluent interface
     * @throws Exception\InvalidArgumentException
     */
    public function setValueIfTrue(null|string|Select|Expression|PredicateInterface $value_if_true,
                                   mixed                                            ...$valueParameter): self {
        if (is_string($value_if_true)) {
            if (! empty($valueParameter)) {
                $valueParameter1 = current($valueParameter);
                $valueParameterArray = is_array($valueParameter1) ? $valueParameter1 : $valueParameter;
                $value_if_true = new Expression($value_if_true, $valueParameterArray);
            } else {
                $value_if_true = new Expression($value_if_true);
            }
        }
        $this->value_if_true = $value_if_true;

        return $this;
    }
    /**
     * Gets set of values in IN comparison
     *
     * @return null|Expression|Select
     */
    public function getValueIfTrue(): null|Expression|Select|PredicateInterface {
        return $this->value_if_true;
    }
    /**
     * Set value_if_false for IF comparison
     *
     * @param null|string|Select|Expression|PredicateInterface $value_if_false
     * @param mixed ...$valueParameter
     * @return self Provides a fluent interface
     * @throws Exception\InvalidArgumentException
     */
    public function setValueIfFalse(null|string|Select|Expression|PredicateInterface $value_if_false,
                                    mixed                                            ...$valueParameter): self {
        if (is_string($value_if_false)) {
            if (! empty($valueParameter)) {
                $valueParameter1 = current($valueParameter);
                $valueParameterArray = is_array($valueParameter1) ? $valueParameter1 : $valueParameter;
                $value_if_false = new Expression($value_if_false, $valueParameterArray);
            } else {
                $value_if_false = new Expression($value_if_false);
            }
        }
        $this->value_if_false = $value_if_false;

        return $this;
    }
    /**
     * Gets set of values in IN comparison
     *
     * @return null|Expression|Select
     */
    public function getValueIfFalse(): null|Expression|Select|PredicateInterface {
        return $this->value_if_false;
    }
    /**
     * Return array of parts for where statement
     *
     * @return array
     */
    public function getExpressionData(): array {
        $condition = $this->getCondition();
        $specification = $this->specification;
        if (! $condition instanceof \Sql\Select && ! $condition instanceof \Sql\Where) {
            $specification = str_replace('%1$s', '(%1$s)', $specification);
        }
        $value_if_true = $this->getValueIfTrue();
        if (! $value_if_true instanceof \Sql\Select && ! $condition instanceof \Sql\Where) {
            $specification = str_replace('%2$s', '(%2$s)', $specification);
        }
        $value_if_false = $this->getValueIfFalse();
        if (! $value_if_false instanceof \Sql\Select && ! $condition instanceof \Sql\Where) {
            $specification = str_replace('%3$s', '(%3$s)', $specification);
        }

        $replacements = [
                $condition ?? new Expression('null'),
                $value_if_true ?? new Expression('null'),
                $value_if_false ?? new Expression('null')
        ];
        $types = [self::TYPE_VALUE, self::TYPE_VALUE, self::TYPE_VALUE];

        return [
            [
                $specification,
                $replacements,
                $types
            ]
        ];
    }
}
