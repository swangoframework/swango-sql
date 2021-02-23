<?php
namespace Sql;

class PriorityList implements \Iterator, \Countable {
    const EXTR_DATA = 0x00000001;
    const EXTR_PRIORITY = 0x00000002;
    const EXTR_BOTH = 0x00000003;
    /**
     * Internal list of all items.
     *
     * @var array[]
     */
    protected array $items = [];
    /**
     * Serial assigned to items to preserve LIFO.
     */
    protected int $serial = 0;
    /**
     * Serial order mode
     */
    protected int $isLIFO = 1;
    /**
     * Internal counter to avoid usage of count().
     */
    protected int $count = 0;
    /**
     * Whether the list was already sorted.
     */
    protected bool $sorted = false;
    /**
     * Insert a new item.
     *
     * @param string $name
     * @param mixed $value
     * @param int $priority
     *
     * @return void
     */
    public function insert(string $name, mixed $value, int $priority = 0): void {
        if (! isset($this->items[$name])) {
            $this->count++;
        }

        $this->sorted = false;

        $this->items[$name] = [
            'data' => $value,
            'priority' => (int)$priority,
            'serial' => $this->serial++
        ];
    }
    /**
     *
     * @param string $name
     * @param int $priority
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function setPriority(string $name, int $priority): self {
        if (! isset($this->items[$name])) {
            throw new \Exception("item $name not found");
        }

        $this->items[$name]['priority'] = (int)$priority;
        $this->sorted = false;

        return $this;
    }
    /**
     * Remove a item.
     *
     * @param string $name
     * @return void
     */
    public function remove(string $name): void {
        if (isset($this->items[$name])) {
            $this->count--;
        }

        unset($this->items[$name]);
    }
    /**
     * Remove all items.
     *
     * @return void
     */
    public function clear(): void {
        $this->items = [];
        $this->serial = 0;
        $this->count = 0;
        $this->sorted = false;
    }
    /**
     * Get a item.
     *
     * @param string $name
     * @return mixed
     */
    public function get(string $name): mixed {
        if (! isset($this->items[$name])) {
            return null;
        }

        return $this->items[$name]['data'];
    }
    /**
     * Sort all items.
     *
     * @return void
     */
    protected function sort(): void {
        if (! $this->sorted) {
            uasort($this->items, [
                $this,
                'compare'
            ]);
            $this->sorted = true;
        }
    }
    /**
     * Compare the priority of two items.
     *
     * @param array $item1 ,
     * @param array $item2
     * @return int
     */
    protected function compare(array $item1, array $item2): int {
        return ($item1['priority'] === $item2['priority']) ? ($item1['serial'] > $item2['serial'] ? -1 : 1) *
            $this->isLIFO : ($item1['priority'] > $item2['priority'] ? -1 : 1);
    }
    /**
     * Get/Set serial order mode
     *
     * @param bool|null $flag
     *
     * @return bool
     */
    public function isLIFO(?bool $flag = null): bool {
        if ($flag !== null) {
            $isLifo = $flag === true ? 1 : -1;

            if ($isLifo !== $this->isLIFO) {
                $this->isLIFO = $isLifo;
                $this->sorted = false;
            }
        }

        return 1 === $this->isLIFO;
    }
    /**
     *
     * {@inheritdoc}
     *
     */
    public function rewind(): void {
        $this->sort();
        reset($this->items);
    }
    /**
     *
     * {@inheritdoc}
     *
     */
    public function current(): mixed {
        $this->sorted || $this->sort();
        $node = current($this->items);

        return $node ? $node['data'] : false;
    }
    /**
     *
     * {@inheritdoc}
     *
     */
    public function key(): mixed {
        $this->sorted || $this->sort();
        return key($this->items);
    }
    /**
     *
     * {@inheritdoc}
     *
     */
    public function next(): mixed {
        $node = next($this->items);

        return $node ? $node['data'] : false;
    }
    /**
     *
     * {@inheritdoc}
     *
     */
    public function valid(): mixed {
        return current($this->items) !== false;
    }
    /**
     *
     * @return self
     */
    public function getIterator(): self {
        return clone $this;
    }
    /**
     *
     * {@inheritdoc}
     *
     */
    public function count(): int {
        return $this->count;
    }
    /**
     * Return list as array
     *
     * @param int $flag
     *
     * @return array
     */
    public function toArray(int $flag = self::EXTR_DATA): array {
        $this->sort();

        if ($flag == self::EXTR_BOTH) {
            return $this->items;
        }

        return array_map(function ($item) use ($flag) {
            return ($flag == PriorityList::EXTR_PRIORITY) ? $item['priority'] : $item['data'];
        }, $this->items);
    }
}
