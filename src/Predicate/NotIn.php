<?php
namespace Sql\Predicate;
class NotIn extends In {
    protected string $specification = '%s NOT IN %s';
}
