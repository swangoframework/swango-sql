<?php
namespace Sql;
class Literal implements ExpressionInterface {
    /**
     *
     * @var string
     */
    protected string $literal = '';
    /**
     *
     * @param
     *            $literal
     */
    public function __construct(string $literal = '') {
        $this->literal = $literal;
    }
    /**
     *
     * @param string $literal
     * @return self Provides a fluent interface
     */
    public function setLiteral(string $literal): self {
        $this->literal = $literal;
        return $this;
    }
    /**
     *
     * @return string
     */
    public function getLiteral(): string {
        return $this->literal;
    }
    /**
     *
     * @return array
     */
    public function getExpressionData(): array {
        return [
            [
                str_replace('%', '%%', $this->literal),
                [],
                []
            ]
        ];
    }
}
