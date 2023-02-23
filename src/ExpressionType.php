<?php
namespace Sql;
enum ExpressionType {
    case IDENTIFIER;
    case VALUE;
    case LITERAL;
    case SELECT;
    public function isForOperator(): bool {
        return $this === self::IDENTIFIER || $this === self::VALUE;
    }
}
