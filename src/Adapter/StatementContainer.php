<?php
namespace Sql\Adapter;
class StatementContainer implements StatementContainerInterface {
    /**
     *
     * @var string
     */
    protected $sql = '';
    /**
     *
     * @param string|null $sql            
     */
    public function __construct($sql = null) {
        if ($sql) {
            $this->setSql($sql);
        }
    }
    
    /**
     *
     * @param
     *            $sql
     * @return self Provides a fluent interface
     */
    public function setSql($sql) {
        $this->sql = $sql;
        return $this;
    }
    
    /**
     *
     * @return string
     */
    public function getSql() {
        return $this->sql;
    }
}
