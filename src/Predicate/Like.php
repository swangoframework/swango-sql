<?php
namespace Sql\Predicate;
use Sql\AbstractExpression;

class Like extends AbstractExpression implements PredicateInterface {
    protected string $specification = '%1$s LIKE %2$s';
    protected string $identifier = '';
    protected string $like = '';
    /**
     *
     * @param string $identifier
     * @param string $like
     */
    public function __construct(?string $identifier = null, ?string $like = null) {
        if ($identifier) {
            $this->setIdentifier($identifier);
        }
        if ($like) {
            $this->setLike($like);
        }
    }
    /**
     *
     * @param string $identifier
     * @return self Provides a fluent interface
     */
    public function setIdentifier(string $identifier): self {
        $this->identifier = $identifier;
        return $this;
    }
    /**
     *
     * @return string
     */
    public function getIdentifier(): string {
        return $this->identifier;
    }
    /**
     *
     * @param string $like
     * @return self Provides a fluent interface
     */
    public function setLike(string $like): self {
        $this->like = $like;
        return $this;
    }
    /**
     *
     * @return string
     */
    public function getLike(): string {
        return $this->like;
    }
    /**
     *
     * @param string $specification
     * @return self Provides a fluent interface
     */
    public function setSpecification(string $specification): self {
        $this->specification = $specification;
        return $this;
    }
    /**
     *
     * @return string
     */
    public function getSpecification(): string {
        return $this->specification;
    }
    /**
     *
     * @return array
     */
    public function getExpressionData(): array {
        list($values[], $types[]) = $this->normalizeArgument($this->identifier, self::TYPE_IDENTIFIER);
        list($values[], $types[]) = $this->normalizeArgument($this->like, self::TYPE_VALUE);
        return [
            [
                $this->specification,
                $values,
                $types
            ]
        ];
    }
}
