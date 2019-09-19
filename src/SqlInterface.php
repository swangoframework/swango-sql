<?php
namespace Sql;
use Sql\Adapter\Platform\PlatformInterface;

interface SqlInterface {
    /**
     * Get SQL string for statement
     *
     * @param null|PlatformInterface $adapterPlatform            
     *
     * @return string
     */
    public function getSqlString(PlatformInterface $adapterPlatform = null);
}
