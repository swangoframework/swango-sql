<?php
namespace Sql\Predicate;
use Sql\Expression as BaseExpression;

class Expression extends BaseExpression implements PredicateInterface {
    /**
     * Constructor
     *
     * @param string $expression
     * @param int|float|bool|string|array $valueParameter
     */
    public function __construct(string $expression = null, $valueParameter = null /*[, $valueParameter, ... ]*/)
    {
        if ($expression) {
            $this->setExpression($expression);
        }

        if (! empty($valueParameter)) {
            $valueParameter1 = current($valueParameter);
            $valueParameterArray = is_array($valueParameter1) ? $valueParameter1 : $valueParameter;
        } else
            $valueParameterArray = null;

        $this->setParameters($valueParameterArray);
    }
}
