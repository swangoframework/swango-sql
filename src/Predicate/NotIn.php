<?php
namespace Sql\Predicate;
class NotIn extends In {
    protected $specification = '%s NOT IN %s';
}
