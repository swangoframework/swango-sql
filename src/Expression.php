<?php
namespace Sql;
class Expression extends AbstractExpression {
    const PLACEHOLDER = '?';
    protected string $expression = '';
    protected mixed $parameters = [];
    protected array $types = [];
    /**
     *
     * @param string $expression
     * @param string|array $parameters
     */
    public function __construct(string $expression = '', string|array|null $parameters = null) {
        if ($expression !== '') {
            $this->setExpression($expression);
        }

        if ($parameters) {
            $this->setParameters($parameters);
        }
    }
    /**
     *
     * @param
     *            $expression
     * @return self Provides a fluent interface
     * @throws Exception\InvalidArgumentException
     */
    public function setExpression(string $expression): self {
        if ($expression === '') {
            throw new Exception\InvalidArgumentException('Supplied expression must be a string.');
        }
        $this->expression = $expression;
        return $this;
    }
    /**
     *
     * @return string
     */
    public function getExpression(): string {
        return $this->expression;
    }
    /**
     *
     * @param
     *            $parameters
     * @return self Provides a fluent interface
     * @throws Exception\InvalidArgumentException
     */
    public function setParameters(mixed $parameters): self {
        if (! is_scalar($parameters) && ! is_array($parameters)) {
            throw new Exception\InvalidArgumentException('Expression parameters must be a scalar or array.');
        }
        $this->parameters = $parameters;
        return $this;
    }
    /**
     *
     * @return array
     */
    public function getParameters(): array {
        return $this->parameters;
    }
    /**
     *
     * @param array $types
     * @return self Provides a fluent interface
     * @deprecated
     *
     */
    public function setTypes(array $types): self {
        $this->types = $types;
        return $this;
    }
    /**
     *
     * @return array
     * @deprecated
     *
     */
    public function getTypes(): array {
        return $this->types;
    }
    /**
     *
     * @return array
     * @throws Exception\RuntimeException
     */
    public function getExpressionData(): array {
        $parameters = (is_scalar($this->parameters)) ? [
            $this->parameters
        ] : $this->parameters;
        $parametersCount = count($parameters);
        $expression = str_replace('%', '%%', $this->expression);

        if ($parametersCount == 0) {
            return [
                str_ireplace(self::PLACEHOLDER, '', $expression)
            ];
        }

        // assign locally, escaping % signs
        $expression = str_replace(self::PLACEHOLDER, '%s', $expression, $count);

        // test number of replacements without considering same variable begin used many times first, which is
        // faster, if the test fails then resort to regex which are slow and used rarely
        if ($count !== $parametersCount && $parametersCount === preg_match_all('/\:[a-zA-Z0-9_]*/', $expression)) {
            throw new Exception\RuntimeException('The number of replacements in the expression does not match the number of parameters');
        }

        foreach ($parameters as $parameter) {
            list($values[], $types[]) = $this->normalizeArgument($parameter, self::TYPE_VALUE);
        }
        return [
            [
                $expression,
                $values,
                $types
            ]
        ];
    }
}
