<?php
namespace Sql\Predicate;
class NotBetween extends Between {
    protected string $specification = '%1$s NOT BETWEEN %2$s AND %3$s';
}
