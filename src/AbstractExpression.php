<?php
namespace Sql;
abstract class AbstractExpression implements ExpressionInterface {
    /**
     *
     * @var string[]
     */
    protected array $allowedTypes = [
        self::TYPE_IDENTIFIER,
        self::TYPE_LITERAL,
        self::TYPE_SELECT,
        self::TYPE_VALUE
    ];
    /**
     * Normalize Argument
     *
     * @param mixed $argument
     * @param string $defaultType
     *
     * @return array
     *
     * @throws Exception\InvalidArgumentException
     */
    protected function normalizeArgument(mixed $argument, string $defaultType = self::TYPE_VALUE): array {
        if ($argument instanceof ExpressionInterface || $argument instanceof SqlInterface) {
            return $this->buildNormalizedArgument($argument, self::TYPE_VALUE);
        }

        if (is_scalar($argument) || $argument === null) {
            return $this->buildNormalizedArgument($argument, $defaultType);
        }

        if (is_array($argument)) {
            $value = current($argument);

            if ($value instanceof ExpressionInterface || $value instanceof SqlInterface) {
                return $this->buildNormalizedArgument($value, self::TYPE_VALUE);
            }

            $key = key($argument);

            if (is_integer($key) && ! in_array($value, $this->allowedTypes)) {
                return $this->buildNormalizedArgument($value, $defaultType);
            }

            return $this->buildNormalizedArgument($key, $value);
        }

        if ($argument instanceof \BackedEnum) {
            return $this->buildNormalizedArgument($argument->value, $defaultType);
        }

        if ($argument instanceof \Swango\Model\IdIndexedModel) {
            return $this->buildNormalizedArgument($argument->getId(), $defaultType);
        }

        throw new Exception\InvalidArgumentException(sprintf('$argument should be %s or %s or %s or %s or %s or %s or %s, "%s" given',
            'null', 'scalar', 'array', 'Sql\ExpressionInterface', 'Sql\Sql\SqlInterface', 'BackedEnum', 'Swango\Model\IdIndexedModel',
            is_object($argument) ? get_class($argument) : gettype($argument)));
    }
    /**
     *
     * @param mixed $argument
     * @param string $argumentType
     *
     * @return array
     *
     * @throws Exception\InvalidArgumentException
     */
    private function buildNormalizedArgument(mixed $argument, string $argumentType): array {
        if (! in_array($argumentType, $this->allowedTypes)) {
            throw new Exception\InvalidArgumentException(sprintf('Argument type should be in array(%s)',
                implode(',', $this->allowedTypes)));
        }

        return [
            $argument,
            $argumentType
        ];
    }
}
