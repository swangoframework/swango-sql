<?php
namespace Sql\Predicate;
use Sql\AbstractExpression;

class IsNull extends AbstractExpression implements PredicateInterface {
    /**
     *
     * @var string
     */
    protected $specification = '%1$s IS NULL';
    
    /**
     *
     * @var
     *
     */
    protected $identifier;
    
    /**
     * Constructor
     *
     * @param string $identifier            
     */
    public function __construct($identifier = null) {
        if ($identifier) {
            $this->setIdentifier($identifier);
        }
    }
    
    /**
     * Set identifier for comparison
     *
     * @param string $identifier            
     * @return self Provides a fluent interface
     */
    public function setIdentifier(string $identifier) {
        $this->identifier = $identifier;
        return $this;
    }
    
    /**
     * Get identifier of comparison
     *
     * @return null|string
     */
    public function getIdentifier(): ?string {
        return $this->identifier;
    }
    
    /**
     * Set specification string to use in forming SQL predicate
     *
     * @param string $specification            
     * @return self Provides a fluent interface
     */
    public function setSpecification(string $specification) {
        $this->specification = $specification;
        return $this;
    }
    
    /**
     * Get specification string to use in forming SQL predicate
     *
     * @return string
     */
    public function getSpecification() {
        return $this->specification;
    }
    
    /**
     * Get parts for where statement
     *
     * @return array
     */
    public function getExpressionData(): array {
        $identifier = $this->normalizeArgument($this->identifier, self::TYPE_IDENTIFIER);
        return [
            [
                $this->getSpecification(),
                [
                    $identifier[0]
                ],
                [
                    $identifier[1]
                ]
            ]
        ];
    }
}
