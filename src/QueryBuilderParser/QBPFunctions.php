<?php
namespace gadelat;

use Doctrine\ORM\QueryBuilder;
use \stdClass;

trait QBPFunctions
{
    protected $dateFormat ='d/m/Y';
    /**
     * @param stdClass $rule
     */
    abstract protected function checkRuleCorrect(stdClass $rule);

    protected $operators = array (
        'equal'            => array ('accept_values' => true,  'apply_to' => ['string', 'number', 'datetime']),
        'not_equal'        => array ('accept_values' => true,  'apply_to' => ['string', 'number', 'datetime']),
        'in'               => array ('accept_values' => true,  'apply_to' => ['string', 'number', 'datetime']),
        'not_in'           => array ('accept_values' => true,  'apply_to' => ['string', 'number', 'datetime']),
        'less'             => array ('accept_values' => true,  'apply_to' => ['number', 'datetime']),
        'less_or_equal'    => array ('accept_values' => true,  'apply_to' => ['number', 'datetime']),
        'greater'          => array ('accept_values' => true,  'apply_to' => ['number', 'datetime']),
        'greater_or_equal' => array ('accept_values' => true,  'apply_to' => ['number', 'datetime']),
        'between'          => array ('accept_values' => true,  'apply_to' => ['number', 'datetime']),
        'begins_with'      => array ('accept_values' => true,  'apply_to' => ['string']),
        'not_begins_with'  => array ('accept_values' => true,  'apply_to' => ['string']),
        'contains'         => array ('accept_values' => true,  'apply_to' => ['string']),
        'not_contains'     => array ('accept_values' => true,  'apply_to' => ['string']),
        'ends_with'        => array ('accept_values' => true,  'apply_to' => ['string']),
        'not_ends_with'    => array ('accept_values' => true,  'apply_to' => ['string']),
        'is_empty'         => array ('accept_values' => false, 'apply_to' => ['string']),
        'is_not_empty'     => array ('accept_values' => false, 'apply_to' => ['string']),
        'is_null'          => array ('accept_values' => false, 'apply_to' => ['string', 'number', 'datetime']),
        'is_not_null'      => array ('accept_values' => false, 'apply_to' => ['string', 'number', 'datetime'])
    );

    protected $operator_sql = array (
        'equal'            => array ('operator' => '='),
        'not_equal'        => array ('operator' => '!='),
        'in'               => array ('operator' => 'IN'),
        'not_in'           => array ('operator' => 'NOT IN'),
        'less'             => array ('operator' => '<'),
        'less_or_equal'    => array ('operator' => '<='),
        'greater'          => array ('operator' => '>'),
        'greater_or_equal' => array ('operator' => '>='),
        'between'          => array ('operator' => 'BETWEEN'),
        'begins_with'      => array ('operator' => 'LIKE',     'prepend'  => '%'),
        'not_begins_with'  => array ('operator' => 'NOT LIKE', 'prepend'  => '%'),
        'contains'         => array ('operator' => 'LIKE',     'append'  => '%', 'prepend' => '%'),
        'not_contains'     => array ('operator' => 'NOT LIKE', 'append'  => '%', 'prepend' => '%'),
        'ends_with'        => array ('operator' => 'LIKE',     'append' => '%'),
        'not_ends_with'    => array ('operator' => 'NOT LIKE', 'append' => '%'),
        'is_empty'         => array ('operator' => '='),
        'is_not_empty'     => array ('operator' => '!='),
        'is_null'          => array ('operator' => 'IS NULL'),
        'is_not_null'      => array ('operator' => 'IS NOT NULL')
    );

    protected $needs_array = array(
        'IN', 'NOT IN', 'BETWEEN',
    );

    public function setDateFormat($dateFormat)
    {
        $this->dateFormat = $dateFormat;

        return $this;
    }

    /**
     * Determine if an operator (LIKE/IN) requires an array.
     *
     * @param $operator
     *
     * @return bool
     */
    protected function operatorRequiresArray($operator)
    {
        return in_array($operator, $this->needs_array);
    }

    /**
     * Make sure that a condition is either 'or' or 'and'.
     *
     * @param $condition
     * @return string
     * @throws QBParseException
     */
    protected function validateCondition($condition)
    {
        $condition = trim(strtolower($condition));

        if ($condition !== 'and' && $condition !== 'or') {
            throw new QBParseException("Condition can only be one of: 'and', 'or'.");
        }

        return $condition;
    }

    /**
     * Enforce whether the value for a given field is the correct type
     *
     * @param bool $requireArray value must be an array
     * @param mixed $value the value we are checking against
     * @param string $field the field that we are enforcing
     * @return mixed value after enforcement
     * @throws QBParseException if value is not a correct type
     */
    protected function enforceArrayOrString($requireArray, $value, $field)
    {
        $this->checkFieldIsAnArray($requireArray, $value, $field);

        if (!$requireArray && is_array($value)) {
            return $this->convertArrayToFlatValue($field, $value);
        }

        return $value;
    }

    /**
     * If input value looks to be in date format we expect, convert it to datetime object(s)
     * TODO: no support for actual datetime, only date for now
     *
     * @param stdClass $rule
     * @param $values
     * @return mixed Returns $values, or \DateTime, or \DateTime[]
     */
    public function detectAndConvertDate(stdClass $rule, $values)
    {
        $operatorMayBeDate = in_array('datetime', $this->operators[$rule->operator]['apply_to']);
        $firstValueLooksLikeDate = \DateTime::createFromFormat(
            $this->dateFormat,
            is_array($values) ? $values[0] : $values[0]
        );

        // nope, not a date. Return original value
        if (!$operatorMayBeDate || !$firstValueLooksLikeDate) {
            return $values;
        }

        // there are some dates, let's convert all of it to \DateTime
        foreach ((array)$values as $key => $subValue) {
            $values[$key] = \DateTime::createFromFormat($this->dateFormat, $subValue)->setTime(0, 0);
        }

        return count($values) == 1 ? $values[0] : $values;
    }

    /**
     * Ensure that a given field is an array if required.
     *
     * @see enforceArrayOrString
     * @param boolean $requireArray
     * @param $value
     * @param string $field
     * @throws QBParseException
     */
    protected function checkFieldIsAnArray($requireArray, $value, $field)
    {
        if ($requireArray && !is_array($value)) {
            throw new QBParseException("Field ($field) should be an array, but it isn't.");
        }
    }

    /**
     * Convert an array with just one item to a string.
     *
     * In some instances, and array may be given when we want a string.
     *
     * @see enforceArrayOrString
     * @param string $field
     * @param $value
     * @return mixed
     * @throws QBParseException
     */
    protected function convertArrayToFlatValue($field, $value)
    {
        if (count($value) !== 1) {
            throw new QBParseException("Field ($field) should not be an array, but it is.");
        }

        return $value[0];
    }

    /**
     * Append or prepend a string to the query if required.
     *
     * @param bool $requireArray value must be an array
     * @param mixed $value the value we are checking against
     * @param mixed $sqlOperator
     * @return mixed $value
     */
    protected function appendOperatorIfRequired($requireArray, $value, $sqlOperator)
    {
        if (!$requireArray) {
            if (isset($sqlOperator['append'])) {
                $value = $sqlOperator['append'].$value;
            }

            if (isset($sqlOperator['prepend'])) {
                $value = $value.$sqlOperator['prepend'];
            }
        }

        return $value;
    }

    protected function addWhere(QueryBuilder $query, $content, $condition)
    {
        return $condition == 'AND' ? $query->andWhere($content) : $query->orWhere($content);
    }

    /**
     * Decode the given JSON
     *
     * @param string incoming json
     * @throws QBParseException
     * @return stdClass
     */
    private function decodeJSON($json)
    {
        $query = json_decode($json);

        if (json_last_error()) {
            throw new QBParseException('JSON parsing threw an error: '.json_last_error_msg());
        }

        if ($query && !is_object($query)) {
            throw new QBParseException('The query is not valid JSON');
        }

        return $query;
    }

    /**
     * get a value for a given rule.
     *
     * throws an exception if the rule is not correct.
     *
     * @param stdClass $rule
     * @throws QBRuleException
     */
    private function getRuleValue(stdClass $rule)
    {
        if (!$this->checkRuleCorrect($rule)) {
            throw new QBRuleException();
        }

        return $rule->value;
    }

    /**
     * Check that a given field is in the allowed list if set.
     *
     * @param $fields
     * @param $field
     * @throws QBParseException
     */
    private function ensureFieldIsAllowed($fields, $field)
    {
        if (is_array($fields) && !in_array($field, $fields)) {
            throw new QBParseException("Field ({$field}) does not exist in fields list");
        }
    }

    /**
     * makeQuery, for arrays.
     *
     * Some types of SQL Operators (ie, those that deal with lists/arrays) have specific requirements.
     * This function enforces those requirements.
     *
     * @param QueryBuilder  $query
     * @param stdClass $rule
     * @param array    $sqlOperator
     * @param array    $value
     * @param string   $condition
     *
     * @throws QBParseException
     *
     * @return QueryBuilder
     */
    protected function makeQueryWhenArray(QueryBuilder $query, stdClass $rule, $sqlOperator, array $value, $condition)
    {
        if ($sqlOperator == 'IN' || $sqlOperator == 'NOT IN') {
            return $this->makeArrayQueryIn($query, $rule, $sqlOperator, $value, $condition);
        } elseif ($sqlOperator == 'BETWEEN') {
            return $this->makeArrayQueryBetween($query, $rule, $value, $condition);
        }

        throw new QBParseException('makeQueryWhenArray could not return a value');
    }

    /**
     * makeQuery, for *:N fields
     *
     * @param QueryBuilder $query
     * @param stdClass $rule
     * @param $sqlOperator
     * @param $value
     * @param $condition
     * @return QueryBuilder
     * @throws QBParseException
     */
    protected function makeQueryWhenMany(QueryBuilder $query, stdClass $rule, $sqlOperator, $value, $condition)
    {
        if (in_array($rule->operator, ['contains', 'not_contains'])) {
            $whereDQL = ':'.$rule->field.($rule->operator == 'contains' ? '' : ' NOT').' MEMBER OF e.'.$rule->field;
            $query->setParameter($rule->field, $value);
        }
        elseif (in_array($rule->operator, ['is_null', 'is_not_null'])) {
            if ($rule->operator == 'is_not_null') {
                $query->leftJoin('e.'.$rule->field, $rule->field);
            } else {
                $query->innerJoin('e.'.$rule->field, $rule->field);
            }
            $whereDQL = $rule->field.'.id '.$sqlOperator;
        }
        else {
            throw new QBParseException('Invalid operation for association');
        }

        return $this->addWhere($query, $whereDQL, $condition);
    }

    /**
     * makeArrayQueryIn, when the query is an IN or NOT IN...
     *
     * @see makeQueryWhenArray
     * @param QueryBuilder $query
     * @param stdClass $rule
     * @param string $operator
     * @param array $value
     * @param string $condition
     * @return QueryBuilder
     */
    private function makeArrayQueryIn(QueryBuilder $query, stdClass $rule, $operator, array $value, $condition)
    {
        $whereSql = 'e.'.$rule->field.' '.$operator.' (:'.$rule->field.')';

        return $this
            ->addWhere($query, $whereSql, $condition)
            ->setParameter($rule->field, $value);
    }


    /**
     * makeArrayQueryBetween, when the query is an IN or NOT IN...
     *
     * @see makeQueryWhenArray
     * @param QueryBuilder $query
     * @param stdClass $rule
     * @param array $value
     * @param string $condition
     * @throws QBParseException when more then two items given for the between
     * @return QueryBuilder
     */
    private function makeArrayQueryBetween(QueryBuilder $query, stdClass $rule, array $value, $condition)
    {
        if (count($value) !== 2) {
            throw new QBParseException("{$rule->field} should be an array with only two items.");
        }

        $whereSql = 'e.'.$rule->field.' '.$this->operator_sql['between']['operator'].' :'.$rule->field.'1 AND :'.$rule->field.'2';

        return $this
            ->addWhere($query, $whereSql, $condition)
            ->setParameter($rule->field.'1', $value[0])
            ->setParameter($rule->field.'2', $value[1]);
    }
}
