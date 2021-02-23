<?php
namespace Sql\Adapter;
interface StatementContainerInterface {
    /**
     * Set sql
     *
     * @param
     *            $sql
     * @return mixed
     */
    public function setSql(string $sql);
    /**
     * Get sql
     *
     * @return mixed
     */
    public function getSql(): string;
}
