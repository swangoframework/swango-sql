<?php
namespace Sql;
interface ExpressionInterface {
    const TYPE_IDENTIFIER = 'identifier';
    const TYPE_VALUE = 'value';
    const TYPE_LITERAL = 'literal';
    const TYPE_SELECT = 'select';
    public function getExpressionData(): array;
}
