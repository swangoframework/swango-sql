<?php
namespace Sql;
use Sql\Adapter\Platform\PlatformInterface;

class Insert extends Replace {
    const ON_DUPLICATE_KEY_UPDATE = 'onDuplicateKeyUpdate';
    protected array $specifications = [
        self::SPECIFICATION_INSERT => 'INSERT %1$sINTO %2$s (%3$s) VALUES (%4$s)',
        self::SPECIFICATION_SELECT => 'INSERT %1$sINTO %2$s %3$s %4$s',
        self::ON_DUPLICATE_KEY_UPDATE => 'ON DUPLICATE KEY UPDATE %1$s'
    ];
    protected Expression|null $on_duplicate_key_update = null;
    public function withInsertIgnore(bool $ignore): self {
        $this->ignore = $ignore;
        return $this;
    }
    public function onDuplicateKeyUpdate(string|Expression|null $on_duplicate_key_update,
                                         mixed                  ...$parameters): self {
        if (is_string($on_duplicate_key_update)) {
            $this->on_duplicate_key_update = new Expression($on_duplicate_key_update, ...$parameters);
        } else {
            $this->on_duplicate_key_update = $on_duplicate_key_update;
        }
        return $this;
    }
    protected function processOnDuplicateKeyUpdate(PlatformInterface $platform): ?string {
        if (! isset($this->on_duplicate_key_update)) {
            return null;
        }
        return 'ON DUPLICATE KEY UPDATE ' . $this->processExpression($this->on_duplicate_key_update, $platform);
    }
}
