<?php
namespace Sql;
use Sql\Adapter\Platform\PlatformInterface;

abstract class AbstractSql implements SqlInterface {
    /**
     * Specifications for Sql String generation
     *
     * @var string[]|array[]
     */
    protected array $specifications = [];
    protected array $processInfo = [
        'paramPrefix' => '',
        'subselectCount' => 0
    ];
    protected array $instanceParameterIndex = [];
    /**
     *
     * {@inheritdoc}
     *
     */
    public function getSqlString(PlatformInterface $adapterPlatform = null): string {
        return $this->buildSqlString($adapterPlatform ?? new \Sql\Adapter\Platform\Mysql());
    }
    /**
     *
     * @param PlatformInterface $platform
     * @return string
     */
    protected function buildSqlString(PlatformInterface $platform): string {
        $sqls = [];
        $parameters = [];

        foreach ($this->specifications as $name => $specification) {
            $parameters[$name] = $this->{'process' . $name}($platform, $sqls, $parameters);

            if ($specification && is_array($parameters[$name])) {
                $sqls[$name] = $this->createSqlFromSpecificationAndParameters($specification, $parameters[$name]);

                continue;
            }

            if (is_string($parameters[$name])) {
                $sqls[$name] = $parameters[$name];
            }
        }

        return rtrim(implode(' ', $sqls), "\n ,");
    }
    /**
     * Render table with alias in from/join parts
     *
     * @param string $table
     * @param string $alias
     * @return string
     * @todo move TableIdentifier concatenation here
     */
    protected function renderTable(string $table, string $alias = null): string {
        return $table . ($alias ? ' AS ' . $alias : '');
    }
    /**
     *
     * @param ExpressionInterface $expression
     * @param PlatformInterface $platform
     * @param null|string $namedParameterPrefix
     * @return string
     * @throws Exception\RuntimeException
     */
    protected function processExpression(ExpressionInterface $expression,
                                         PlatformInterface   $platform,
                                         ?string             $namedParameterPrefix = null): string {
        $namedParameterPrefix = ! $namedParameterPrefix ? $namedParameterPrefix : $this->processInfo['paramPrefix'] .
            $namedParameterPrefix;

        $namedParameterPrefix = preg_replace('/\s/', '__', $namedParameterPrefix);

        $sql = '';

        // initialize variables
        $parts = $expression->getExpressionData();

        $this->instanceParameterIndex[$namedParameterPrefix] ??= 1;

        $expressionParamIndex = &$this->instanceParameterIndex[$namedParameterPrefix];

        foreach ($parts as $part) {
            //
            // If it is a string, use $expression->getExpression() to get the unescaped, or simply tack it onto the return sql
            // "specification" string
            if (is_string($part)) {
                $sql .= $expression instanceof Expression ? $expression->getExpression() : $part;
                continue;
            }

            if (! is_array($part)) {
                throw new Exception\RuntimeException('Elements returned from getExpressionData() array must be a string or array.'
                );
            }

            // Process values and types (the middle and last position of the expression data)
            $values = $part[1];
            if (! empty($part[2])) {
                $types = $part[2];
                foreach ($values as $vIndex => &$value)
                    if (isset($types[$vIndex])) {
                        $value = match (true) {
                            $value instanceof Select => '(' . $this->processSubSelect($value, $platform) . ')',
                            $value instanceof ExpressionInterface => $this->processExpression($value,
                                $platform,
                                $namedParameterPrefix . $vIndex . 'subpart'
                            ),
                            default => match ($types[$vIndex]) {
                                ExpressionInterface::TYPE_IDENTIFIER => $platform->quoteIdentifierInFragment($value),
                                ExpressionInterface::TYPE_VALUE => $platform->quoteValue($value),
                                ExpressionInterface::TYPE_LITERAL => $value,
                                default => $value
                            }
                        };
                    }
                unset($value);
            }
            // After looping the values, interpolate them into the sql string
            // (they might be placeholder names, or values)
            $sql .= vsprintf($part[0], $values);
        }

        return $sql;
    }
    /**
     *
     * @param string|array $specifications
     * @param array $parameters
     *
     * @return string
     *
     * @throws Exception\RuntimeException
     */
    protected function createSqlFromSpecificationAndParameters(string|array $specifications, array $parameters): string {
        if (is_string($specifications)) {
            return vsprintf($specifications, $parameters);
        }

        $parametersCount = count($parameters);

        foreach ($specifications as $specificationString => $paramSpecs) {
            if ($parametersCount == count($paramSpecs)) {
                break;
            }

            unset($specificationString, $paramSpecs);
        }

        if (! isset($specificationString)) {
            throw new Exception\RuntimeException('A number of parameters was found that is not supported by this specification'
            );
        }

        $topParameters = [];
        foreach ($parameters as $position => $paramsForPosition) {
            if (isset($paramSpecs[$position]['combinedby'])) {
                $multiParamValues = [];
                foreach ($paramsForPosition as $multiParamsForPosition) {
                    if (is_array($multiParamsForPosition)) {
                        $ppCount = count($multiParamsForPosition);
                    } else {
                        $ppCount = 1;
                    }

                    if (! isset($paramSpecs[$position][$ppCount])) {
                        throw new Exception\RuntimeException(sprintf('A number of parameters (%d) was found that is not supported by this specification',
                                $ppCount
                            )
                        );
                    }
                    if (is_string($multiParamsForPosition)) {
                        $multiParamValues[] = sprintf($paramSpecs[$position][$ppCount], $multiParamsForPosition);
                    } else {
                        $multiParamValues[] = vsprintf($paramSpecs[$position][$ppCount], $multiParamsForPosition);
                    }
                }
                $topParameters[] = implode($paramSpecs[$position]['combinedby'], $multiParamValues);
            } elseif ($paramSpecs[$position] !== null) {
                $ppCount = count($paramsForPosition);
                if (! isset($paramSpecs[$position][$ppCount])) {
                    throw new Exception\RuntimeException(sprintf('A number of parameters (%d) was found that is not supported by this specification',
                            $ppCount
                        )
                    );
                }
                $topParameters[] = vsprintf($paramSpecs[$position][$ppCount], $paramsForPosition);
            } else {
                $topParameters[] = $paramsForPosition;
            }
        }
        return vsprintf($specificationString, $topParameters);
    }
    /**
     *
     * @param Select $subselect
     * @param PlatformInterface $platform
     * @return string
     */
    protected function processSubSelect(Select $subselect, PlatformInterface $platform): string {
        return $subselect->buildSqlString($platform);
    }
    /**
     *
     * @param Join $joins
     * @param PlatformInterface $platform
     * @return null|string[] Null if no joins present, array of JOIN statements
     *         otherwise
     * @throws Exception\InvalidArgumentException for invalid JOIN table names.
     */
    protected function processJoin(Join $joins, PlatformInterface $platform): ?array {
        if (! $joins->count()) {
            return null;
        }

        // process joins
        $joinSpecArgArray = [];
        foreach ($joins->getJoins() as $j => $join) {
            $joinName = null;
            $joinAs = null;

            // table name
            if (is_array($join['name'])) {
                $joinName = current($join['name']);
                $joinAs = $platform->quoteIdentifier(key($join['name']));
            } else {
                $joinName = $join['name'];
            }

            if ($joinName instanceof Expression) {
                $joinName = $joinName->getExpression();
            } elseif ($joinName instanceof TableIdentifier) {
                $joinName = $joinName->getTableAndSchema();
                $joinName = ($joinName[1] ? $platform->quoteIdentifier($joinName[1]) .
                        $platform->getIdentifierSeparator() : '') . $platform->quoteIdentifier($joinName[0]);
            } elseif ($joinName instanceof Select) {
                $joinName = '(' . $this->processSubSelect($joinName, $platform) . ')';
            } elseif (is_string($joinName) || (is_object($joinName) && method_exists($joinName, '__toString'))) {
                if ($platform->shoueldQuoteOtherTable()) {
                    $joinName = $platform->quoteIdentifier($joinName);
                } else {
                    $joinName = (string)$joinName;
                }
            } else {
                throw new Exception\InvalidArgumentException(sprintf('Join name expected to be Expression|TableIdentifier|Select|string, "%s" given',
                        gettype($joinName)
                    )
                );
            }

            $joinSpecArgArray[$j] = [
                strtoupper($join['type']),
                $this->renderTable($joinName, $joinAs)
            ];

            // on expression
            // note: for Expression objects, pass them to processExpression with a prefix specific to each join
            // (used for named parameters)
            if (($join['on'] instanceof ExpressionInterface)) {
                $joinSpecArgArray[$j][] = $this->processExpression($join['on'], $platform, 'join' . ($j + 1) . 'part');
            } else {
                // on
                $joinSpecArgArray[$j][] = $platform->quoteIdentifierInFragment($join['on'], [
                    '=',
                    'AND',
                    'OR',
                    '(',
                    ')',
                    'BETWEEN',
                    '<',
                    '>'
                ]);
            }
        }

        return [
            $joinSpecArgArray
        ];
    }
    /**
     *
     * @param null|array|ExpressionInterface|Select $column
     * @param PlatformInterface $platform
     * @param null|string $namedParameterPrefix
     * @return string
     */
    protected function resolveColumnValue(mixed             $column,
                                          PlatformInterface $platform,
                                          ?string           $namedParameterPrefix = null): string {
        $namedParameterPrefix = ! $namedParameterPrefix ? $namedParameterPrefix : $this->processInfo['paramPrefix'] .
            $namedParameterPrefix;
        $isIdentifier = false;
        $fromTable = '';
        if (is_array($column)) {
            if (isset($column['isIdentifier'])) {
                $isIdentifier = (bool)$column['isIdentifier'];
            }
            if (isset($column['fromTable']) && $column['fromTable'] !== null) {
                $fromTable = $column['fromTable'];
            }
            $column = $column['column'];
        }

        if ($column instanceof ExpressionInterface) {
            return $this->processExpression($column, $platform, $namedParameterPrefix);
        }
        if ($column instanceof Select) {
            return '(' . $this->processSubSelect($column, $platform) . ')';
        }
        if ($column === null) {
            return 'NULL';
        }
        if ($column instanceof \BackedEnum) {
            $column = $column->value;
        } elseif ($column instanceof \Swango\Model\IdIndexedModel) {
            $column = $column->getId();
        }
        $column = (string)$column;

        return $isIdentifier ? $fromTable . $platform->quoteIdentifierInFragment($column) : $platform->quoteValue($column);
    }
    /**
     *
     * @param string|TableIdentifier|Select $table
     * @param PlatformInterface $platform
     * @return string|array
     */
    protected function resolveTable(string|TableIdentifier|Select $table, PlatformInterface $platform): string|array {
        $schema = null;
        if ($table instanceof TableIdentifier) {
            [
                $table,
                $schema
            ] = $table->getTableAndSchema();
        }

        if ($table instanceof Select) {
            $table = '(' . $this->processSubselect($table, $platform) . ')';
        } elseif ($table) {
            $table = $platform->quoteIdentifier($table);
        }

        if ($schema && $table) {
            $table = $platform->quoteIdentifier($schema) . $platform->getIdentifierSeparator() . $table;
        }
        return $table;
    }
}
