<?php
namespace Sql;
interface ExpressionInterface {
    const TYPE_IDENTIFIER = ExpressionType::IDENTIFIER;
    const TYPE_VALUE = ExpressionType::VALUE;
    const TYPE_LITERAL = ExpressionType::LITERAL;
    const TYPE_SELECT = ExpressionType::SELECT;
    public function getExpressionData(): array;
}
