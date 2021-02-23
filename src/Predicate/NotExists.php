<?php
namespace Sql\Predicate;
class NotExists extends Exists {
    protected string $specification = 'NOT EXISTS %s';
    protected string $valueSpecSpecification = 'NOT EXISTS (%s)';
}
