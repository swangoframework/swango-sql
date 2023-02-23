<?php
namespace Sql\Predicate;
use Sql\Exception;
use Sql\AbstractExpression;
use Sql\ExpressionType;

class Operator extends AbstractExpression implements PredicateInterface {
    const OPERATOR_EQUAL_TO = '=';
    const OP_EQ = '=';
    const OPERATOR_NOT_EQUAL_TO = '!=';
    const OP_NE = '!=';
    const OPERATOR_LESS_THAN = '<';
    const OP_LT = '<';
    const OPERATOR_LESS_THAN_OR_EQUAL_TO = '<=';
    const OP_LTE = '<=';
    const OPERATOR_GREATER_THAN = '>';
    const OP_GT = '>';
    const OPERATOR_GREATER_THAN_OR_EQUAL_TO = '>=';
    const OP_GTE = '>=';
    /**
     *
     * {@inheritdoc}
     *
     */
    protected mixed $left;
    protected mixed $right;
    protected ExpressionType $leftType = self::TYPE_IDENTIFIER;
    protected ExpressionType $rightType = self::TYPE_VALUE;
    protected string $operator = self::OPERATOR_EQUAL_TO;
    /**
     * Constructor
     *
     * @param int|float|bool|string|\Sql\Expression $left
     * @param string $operator
     * @param int|float|bool|string|\Sql\Expression $right
     * @param ExpressionType $leftType
     *            TYPE_IDENTIFIER or TYPE_VALUE by default TYPE_IDENTIFIER
     * @param ExpressionType $rightType
     *            TYPE_IDENTIFIER or TYPE_VALUE by default TYPE_VALUE
     */
    public function __construct(mixed          $left = null,
                                string         $operator = self::OPERATOR_EQUAL_TO,
                                mixed          $right = null,
                                ExpressionType $leftType = self::TYPE_IDENTIFIER,
                                ExpressionType $rightType = self::TYPE_VALUE) {
        if ($left !== null) {
            $this->setLeft($left);
        }

        if ($operator !== self::OPERATOR_EQUAL_TO) {
            $this->setOperator($operator);
        }

        if ($right !== null) {
            $this->setRight($right);
        }

        if ($leftType !== self::TYPE_IDENTIFIER) {
            $this->setLeftType($leftType);
        }

        if ($rightType !== self::TYPE_VALUE) {
            $this->setRightType($rightType);
        }
    }
    /**
     * Set left side of operator
     *
     * @param int|float|bool|string|\Sql\Expression $left
     *
     * @return self Provides a fluent interface
     */
    public function setLeft(mixed $left): self {
        $this->left = $left;

        if (is_array($left)) {
            $left = $this->normalizeArgument($left, $this->leftType);
            $this->leftType = $left[1];
        }

        return $this;
    }
    /**
     * Get left side of operator
     *
     * @return int|float|bool|string|\Sql\Expression
     */
    public function getLeft(): mixed {
        return $this->left;
    }
    /**
     * Set parameter type for left side of operator
     *
     * @param string $type
     *            TYPE_IDENTIFIER or TYPE_VALUE
     *
     * @return self Provides a fluent interface
     *
     * @throws Exception\InvalidArgumentException
     */
    public function setLeftType(ExpressionType $type): self {
        if (! $type->isForOperator()) {
            throw new Exception\InvalidArgumentException(sprintf('Invalid type "%s" provided; must be of type "%s" or "%s"',
                $type,
                __CLASS__ . '::TYPE_IDENTIFIER',
                __CLASS__ . '::TYPE_VALUE'
            )
            );
        }

        $this->leftType = $type;

        return $this;
    }
    /**
     * Get parameter type on left side of operator
     *
     * @return string
     */
    public function getLeftType(): string {
        return $this->leftType;
    }
    /**
     * Set operator string
     *
     * @param string $operator
     * @return self Provides a fluent interface
     */
    public function setOperator(string $operator): self {
        $this->operator = $operator;

        return $this;
    }
    /**
     * Get operator string
     *
     * @return string
     */
    public function getOperator(): string {
        return $this->operator;
    }
    /**
     * Set right side of operator
     *
     * @param mixed $right
     *
     * @return self Provides a fluent interface
     */
    public function setRight(mixed $right): self {
        $this->right = $right;

        if (is_array($right)) {
            $right = $this->normalizeArgument($right, $this->rightType);
            $this->rightType = $right[1];
        }

        return $this;
    }
    /**
     * Get right side of operator
     *
     * @return mixed
     */
    public function getRight(): mixed {
        return $this->right;
    }
    /**
     * Set parameter type for right side of operator
     *
     * @param string $type
     *            TYPE_IDENTIFIER or TYPE_VALUE
     * @return self Provides a fluent interface
     * @throws Exception\InvalidArgumentException
     */
    public function setRightType(ExpressionType $type): self {
        if (! $type->isForOperator()) {
            throw new Exception\InvalidArgumentException(sprintf('Invalid type "%s" provided; must be of type "%s" or "%s"',
                $type,
                __CLASS__ . '::TYPE_IDENTIFIER',
                __CLASS__ . '::TYPE_VALUE'
            )
            );
        }

        $this->rightType = $type;

        return $this;
    }
    /**
     * Get parameter type on right side of operator
     *
     * @return string
     */
    public function getRightType(): string {
        return $this->rightType;
    }
    /**
     * Get predicate parts for where statement
     *
     * @return array
     */
    public function getExpressionData(): array {
        [$values[], $types[]] = $this->normalizeArgument($this->left, $this->leftType);
        [$values[], $types[]] = $this->normalizeArgument($this->right, $this->rightType);

        return [
            [
                '%s ' . $this->operator . ' %s',
                $values,
                $types
            ]
        ];
    }
}
