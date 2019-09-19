<?php
namespace Sql\Predicate;
class NotExists extends Exists {
    protected $specification = 'NOT EXISTS %s';
    protected $valueSpecSpecification = 'NOT EXISTS (%s)';
}
