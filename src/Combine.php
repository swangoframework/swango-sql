<?php
namespace Sql;
use Sql\Adapter\Platform\PlatformInterface;

/**
 * Combine SQL statement - allows combining multiple select statements into one
 */
class Combine extends AbstractSql {
    const COLUMNS = 'columns';
    const COMBINE = 'combine';
    const COMBINE_UNION = 'union';
    const COMBINE_EXCEPT = 'except';
    const COMBINE_INTERSECT = 'intersect';
    /**
     *
     * @var string[]
     */
    protected array $specifications = [
        self::COMBINE => '%1$s (%2$s) '
    ];
    /**
     *
     * @var Select[][]
     */
    private array $combine = [];
    /**
     *
     * @param Select|array|null $select
     * @param string $type
     * @param string $modifier
     */
    public function __construct(Select|array|null $select = null, string $type = self::COMBINE_UNION,
                                string            $modifier = '') {
        if ($select) {
            $this->combine($select, $type, $modifier);
        }
    }
    /**
     * Create combine clause
     *
     * @param Select|array $select
     * @param string $type
     * @param string $modifier
     *
     * @return self Provides a fluent interface
     *
     * @throws Exception\InvalidArgumentException
     */
    public function combine(Select|array $select, string $type = self::COMBINE_UNION, string $modifier = ''): self {
        if (is_array($select)) {
            foreach ($select as $combine) {
                if ($combine instanceof Select) {
                    $combine = [
                        $combine
                    ];
                }

                $this->combine($combine[0], isset($combine[1]) ? $combine[1] : $type,
                    isset($combine[2]) ? $combine[2] : $modifier);
            }
            return $this;
        }

        $this->combine[] = [
            'select' => $select,
            'type' => $type,
            'modifier' => $modifier
        ];
        return $this;
    }
    /**
     * Create union clause
     *
     * @param Select|array $select
     * @param string $modifier
     *
     * @return self
     */
    public function union(Select|array $select, string $modifier = ''): self {
        return $this->combine($select, self::COMBINE_UNION, $modifier);
    }
    /**
     * Create except clause
     *
     * @param Select|array $select
     * @param string $modifier
     *
     * @return self
     */
    public function except(Select|array $select, string $modifier = ''): self {
        return $this->combine($select, self::COMBINE_EXCEPT, $modifier);
    }
    /**
     * Create intersect clause
     *
     * @param Select|array $select
     * @param string $modifier
     * @return self
     */
    public function intersect(Select|array $select, string $modifier = ''): self {
        return $this->combine($select, self::COMBINE_INTERSECT, $modifier);
    }
    /**
     * Build sql string
     *
     * @param PlatformInterface $platform
     *
     * @return string
     */
    protected function buildSqlString(PlatformInterface $platform): string {
        if (! $this->combine) {
            return;
        }

        $sql = '';
        foreach ($this->combine as $i => $combine) {
            $type = $i == 0 ? '' : strtoupper($combine['type'] .
                ($combine['modifier'] ? ' ' . $combine['modifier'] : ''));
            $select = $this->processSubSelect($combine['select'], $platform);
            $sql .= sprintf($this->specifications[self::COMBINE], $type, $select);
        }
        return trim($sql, ' ');
    }
    /**
     *
     * @return self Provides a fluent interface
     */
    public function alignColumns(): self {
        if (! $this->combine) {
            return $this;
        }

        $allColumns = [];
        foreach ($this->combine as $combine) {
            $allColumns = array_merge($allColumns, $combine['select']->getRawState(self::COLUMNS));
        }

        foreach ($this->combine as $combine) {
            $combineColumns = $combine['select']->getRawState(self::COLUMNS);
            $aligned = [];
            foreach ($allColumns as $alias => $column) {
                $aligned[$alias] = isset($combineColumns[$alias]) ? $combineColumns[$alias] : new Predicate\Expression('NULL');
            }
            $combine['select']->columns($aligned, false);
        }
        return $this;
    }
    /**
     * Get raw state
     *
     * @param string $key
     *
     * @return array
     */
    public function getRawState(?string $key = null): array {
        $rawState = [
            self::COMBINE => $this->combine,
            self::COLUMNS => $this->combine ? $this->combine[0]['select']->getRawState(self::COLUMNS) : []
        ];
        return (isset($key) && array_key_exists($key, $rawState)) ? $rawState[$key] : $rawState;
    }
}
