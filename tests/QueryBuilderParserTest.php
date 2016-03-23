<?php

namespace gadelat\test;

use Doctrine\ORM\Query\Parameter;

class QueryBuilderParserTest extends CommonQueryBuilderTests
{
    public function testSimpleQuery()
    {
        $builder = $this->createQueryBuilder();
        $qb = $this->getParserUnderTest();

        $qb->parse($this->simpleQuery, $builder);

        $this->assertEquals('SELECT e FROM gadelat\test\Entity\Opportunity e WHERE e.price < :price', $builder->getDQL());
    }

//    public function testMoreComplexQuery()
//    {
//        $builder = $this->createQueryBuilder();
//        $qb = $this->getParserUnderTest();
//
//        $test = $qb->parse($this->json1, $builder);
//
//        $this->assertEquals('select * where `price` < ? and (`name` LIKE ? or `name` = ?)', $builder->getDQL());
//    }
//
//    public function testBetterThenTheLastTime()
//    {
//        $builder = $this->createQueryBuilder();
//        $qb = $this->getParserUnderTest();
//
//        $json = '{"condition":"AND","rules":[{"id":"anchor_text","field":"anchor_text","type":"string","input":"text","operator":"contains","value":"www"},{"condition":"OR","rules":[{"id":"citation_flow","field":"citation_flow","type":"double","input":"text","operator":"greater_or_equal","value":"30"},{"id":"trust_flow","field":"trust_flow","type":"double","input":"text","operator":"greater_or_equal","value":"30"}]}]}';
//        $test = $qb->parse($json, $builder);
//
//        $this->assertEquals('select * where `anchor_text` LIKE ? and (`citation_flow` >= ? or `trust_flow` >= ?)', $builder->getDQL());
//    }
//
//    public function testCategoryIn()
//    {
//        $builder = $this->createQueryBuilder();
//        $qb = $this->getParserUnderTest();
//
//        $qb->parse($this->makeJSONForInNotInTest(), $builder);
//
//        $this->assertEquals('select * where `price` < ? and (`category` in (?, ?))', $builder->getDQL());
//    }
//
//    public function testCategoryNotIn()
//    {
//        $builder = $this->createQueryBuilder();
//        $qb = $this->getParserUnderTest();
//
//        $qb->parse($this->makeJSONForInNotInTest('not_in'), $builder);
//
//        $this->assertEquals('select * where `price` < ? and (`category` not in (?, ?))', $builder->getDQL());
//    }
//
//    /**
//     * @expectedException \gadelat\QBParseException
//     * @expectedExceptionMessage Field (category) should not be an array, but it is.
//     */
//    public function testCategoryInvalidArray()
//    {
//        $builder = $this->createQueryBuilder();
//        $qb = $this->getParserUnderTest();
//
//        $qb->parse($this->makeJSONForInNotInTest('contains'), $builder);
//
//        $this->assertEquals('select * where `price` < ?', $builder->getDQL());
//    }
//
//    public function testManyNestedQuery()
//    {
//        // $('#builder-basic').queryBuilder('setRules', /** This object */);
//        $json = '{
//           "condition":"AND",
//           "rules":[
//              {
//                 "id":"price",
//                 "field":"price",
//                 "type":"double",
//                 "input":"text",
//                 "operator":"less",
//                 "value":"10.25"
//              }, {
//                 "condition":"AND",
//                 "rules":[
//                    {
//                       "id":"category",
//                       "field":"category",
//                       "type":"integer",
//                       "input":"select",
//                       "operator":"in",
//                       "value":[
//                          "1", "2"
//                       ]
//                    }, {
//                       "condition":"OR",
//                       "rules":[
//                          {
//                             "id":"name",
//                             "field":"name",
//                             "type":"string",
//                             "input":"text",
//                             "operator":"equal",
//                             "value":"dgfssdfg"
//                          }, {
//                             "id":"name",
//                             "field":"name",
//                             "type":"string",
//                             "input":"text",
//                             "operator":"not_equal",
//                             "value":"dgfssdfg"
//                          }, {
//                             "condition":"AND",
//                             "rules":[
//                                {
//                                   "id":"name",
//                                   "field":"name",
//                                   "type":"string",
//                                   "input":"text",
//                                   "operator":"equal",
//                                   "value":"sadf"
//                                },
//                                {
//                                   "id":"name",
//                                   "field":"name",
//                                   "type":"string",
//                                   "input":"text",
//                                   "operator":"equal",
//                                   "value":"sadf"
//                                }
//                             ]
//                          }
//                       ]
//                    }
//                 ]
//              }
//           ]
//        }';
//
//        $builder = $this->createQueryBuilder();
//        $qb = $this->getParserUnderTest();
//
//        $qb->parse($json, $builder);
//
//        //$this->assertEquals('select * where `price` < ? AND (`category` in (?, ?) OR (`name` = ? AND (`name` = ?)))', $builder->getDQL());
//        $this->assertEquals('select * where `price` < ? and (`category` in (?, ?) and (`name` = ? or `name` != ? or (`name` = ? and `name` = ?)))', $builder->getDQL());
//        //$this->assertEquals('/* This test currently fails. This should be fixed. */', $builder->getDQL());
//    }
//
    /**
     * @expectedException \gadelat\QBParseException
     */
    public function testJSONParseException()
    {
        $builder = $this->createQueryBuilder();
        $qb = $this->getParserUnderTest();

        $qb->parse('{}]JSON', $builder);
    }

    private function getBetweenJSON($hasTwoValues = true)
    {
        $v = '"2","3"'.((!$hasTwoValues ? ',"3"' : ''));

        $json = '{"condition":"AND","rules":['
            .'{"id":"price","field":"price","type":"double","input":"text",'
            .'"operator":"between","value":['.$v.']}]}';

        return $json;
    }

    public function testBetweenOperator()
    {
        $builder = $this->createQueryBuilder();
        $qb = $this->getParserUnderTest();

        $qb->parse($this->getBetweenJSON(), $builder);
        $this->assertEquals('SELECT e FROM gadelat\test\Entity\Opportunity e WHERE e.price BETWEEN :price1 AND :price2', $builder->getDQL());
    }

    private function noRulesOrEmptyRules($hasRules = false)
    {
        $builder = $this->createQueryBuilder();
        $qb = $this->getParserUnderTest();

        $rules = '{"condition":"AND"}';
        if ($hasRules) {
            $rules = '{"condition":"AND","rules":[]}';
        }

        $qb->parse($rules, $builder);

        $this->assertEquals('SELECT e FROM gadelat\test\Entity\Opportunity e', $builder->getDQL());
    }

    public function testNoRulesNoQuery()
    {
        $this->noRulesOrEmptyRules(false);
        $this->noRulesOrEmptyRules(true);
    }

    public function testIsNull()
    {
        $v = '1.23';
        $json = '{"condition":"AND","rules":['
            .'{"id":"price","field":"price","type":"double","input":"text",'
            .'"operator":"is_null","value":['.$v.']}]}';

        $builder = $this->createQueryBuilder();
        $qb = $this->getParserUnderTest();
        $qb->parse($json, $builder);

        $this->assertEquals('SELECT e FROM gadelat\test\Entity\Opportunity e WHERE e.price IS NULL', $builder->getDQL());
        $this->assertCount(0, $builder->getParameters());
    }

    public function testIsNotNull()
    {
        $v = '1.23';
        $json = '{"condition":"AND","rules":['
            .'{"id":"price","field":"price","type":"double","input":"text",'
            .'"operator":"is_not_null","value":['.$v.']}]}';

        $builder = $this->createQueryBuilder();
        $qb = $this->getParserUnderTest();
        $qb->parse($json, $builder);

        $this->assertEquals('SELECT e FROM gadelat\test\Entity\Opportunity e WHERE e.price IS NOT NULL', $builder->getDQL());
    }

    public function testManyIsNull()
    {
        $some_json_input = '{"condition":"AND","rules":[{"id":"sectors","field":"sectors","type":"string","input":"text","operator":"is_null","value":"1"}]}';

        $builder = $this->createQueryBuilder();
        $qb = $this->getParserUnderTest();

        $qb->parse($some_json_input, $builder);

        $dql = $builder->getQuery()->getDQL();

        $this->assertEquals('SELECT e FROM gadelat\test\Entity\Opportunity e LEFT JOIN e.sectors sectors WHERE sectors.id IS NULL', $dql);
    }

    public function testManyIsNotNull()
    {
        $some_json_input = '{"condition":"AND","rules":[{"id":"sectors","field":"sectors","type":"string","input":"text","operator":"is_not_null","value":"1"}]}';

        $builder = $this->createQueryBuilder();
        $qb = $this->getParserUnderTest();

        $qb->parse($some_json_input, $builder);

        $dql = $builder->getQuery()->getDQL();

        $this->assertEquals('SELECT e FROM gadelat\test\Entity\Opportunity e INNER JOIN e.sectors sectors WHERE sectors.id IS NOT NULL', $dql);
    }
    public function testValueBecomesEmpty()
    {
        $v = '1.23';
        $json = '{"condition":"AND","rules":['
            .'{"id":"price","field":"price","type":"double","input":"text",'
            .'"operator":"is_empty","value":['.$v.']}]}';

        $builder = $this->createQueryBuilder();
        $qb = $this->getParserUnderTest();
        $test = $qb->parse($json, $builder);

        $dqlBindings = $builder->getParameters()->getValues();
        $this->assertCount(1, $dqlBindings);
        /** @var Parameter $dqlBinding */
        $dqlBinding = $dqlBindings[0];
        $this->assertEquals($dqlBinding->getValue(), '');
    }

    public function testValueIsValid()
    {
        $v = '1.23';
        $json = '{"condition":"AND","rules":['
            .'{"id":"price","field":"price","type":"double","input":"text",'
            .'"operator":"is_truely_empty","value":['.$v.']}]}';

        $builder = $this->createQueryBuilder();
        $qb = $this->getParserUnderTest();
        $test = $qb->parse($json, $builder);

        $dqlBindings = $builder->getParameters();
        $this->assertCount(0, $dqlBindings);
    }

    private function beginsOrEndsWithTest($begins = 'begins', $not = false)
    {
        $operator = (!$not ? '' : 'not_') . $begins . '_with';
        $like = $not ? 'NOT LIKE' : 'LIKE';

        $builder = $this->createQueryBuilder();
        $qb = $this->getParserUnderTest();

        $json = '{"condition":"AND","rules":[{"id":"anchor_text","field":"anchor_text","type":"string","input":"text","operator":"' . $operator . '","value":"www"}]}';
        $test = $qb->parse($json, $builder);

        if ($begins == 'begins') {
            $binding_value_is = 'www%';
        } else {
            $binding_value_is = '%www';
        }

        $this->assertEquals('SELECT e FROM gadelat\test\Entity\Opportunity e WHERE e.anchor_text ' . $like . ' :anchor_text', $builder->getDQL());
        $this->assertEquals($binding_value_is, $builder->getParameters()->getValues()[0]->getValue());
    }

    public function testBeginsWith()
    {
        $this->beginsOrEndsWithTest('begins', false);
    }

    public function testBeginsNotWith()
    {
        $this->beginsOrEndsWithTest('begins', true);
    }

    public function testEndsWith()
    {
        $this->beginsOrEndsWithTest('ends', false);
    }

    public function testEndsNotWith()
    {
        $this->beginsOrEndsWithTest('ends', true);
    }

    /**
     * @expectedException \gadelat\QBParseException
     * @expectedMessage Field (price) should not be an array, but it is.
     */
    public function testInputIsNotArray()
    {
        $v = '1.23';
        $json = '{"condition":"AND","rules":['
            .'{"id":"price","field":"price","type":"double","input":"text",'
            .'"operator":"equal","value":["tim","simon"]}]}';

        $builder = $this->createQueryBuilder();
        $qb = $this->getParserUnderTest();
        $test = $qb->parse($json, $builder);
    }

    public function testRuleHasInputAndType()
    {
        $v = '1.23';
        $json = '{"condition":"AND","rules":['
            .'{"id":"price","field":"price","type":"double","inputs":"text",'
            .'"operator":"is_truely_empty","value":['.$v.']}]}';

        $builder = $this->createQueryBuilder();
        $qb = $this->getParserUnderTest();
        $test = $qb->parse($json, $builder);

        $dqlBindings = $builder->getParameters();
        $this->assertCount(0, $dqlBindings);
    }

    /**
     * @expectedException \gadelat\QBParseException
     * @expectedExceptionMessage Field (price) does not exist in fields list
     */
    public function testFieldNotInittedNotAllowed()
    {
        $builder = $this->createQueryBuilder();
        $qb = $this->getParserUnderTest(array('this_field_is_allowed_but_is_not_present_in_the_json_string'));
        $test = $qb->parse($this->json1, $builder);
    }

    /**
     * @expectedException \gadelat\QBParseException
     * @expectedExceptionMessage Field (price) should be an array, but it isn't.
     */
    public function testBetweenMustBeArray($validJSON = true)
    {
        $json = '{"condition":"AND","rules":['
            .'{"id":"price","field":"price","type":"double","input":"text",'
            .'"operator":"between","value":"1"}]}';

        if (!$validJSON) {
            $json .= '[';
        }

        $builder = $this->createQueryBuilder();
        $qb = $this->getParserUnderTest();
        $test = $qb->parse($json, $builder);
    }

    /**
     * @expectedException \gadelat\QBParseException
     * @expectedExceptionMessage JSON parsing threw an error
     */
    public function testThrowExceptionInvalidJSON()
    {
        $this->testBetweenMustBeArray(false /*invalid json*/);
    }

    /**
     * This is a similar test to testBetweenOperator, however, this will throw an exception if
     * there is more then two values for the 'BETWEEN' operator.
     *
     * @expectedException \gadelat\QBParseException
     */
    public function testBetweenOperatorThrowsException()
    {
        $builder = $this->createQueryBuilder();
        $qb = $this->getParserUnderTest();

        $qb->parse($this->getBetweenJSON(false), $builder);
    }

    /**
     * QBP can only accept objects, not arrays.
     *
     * Make sure an exception is thrown if the JSON is valid, but after parsing,
     * we don't get back an object
     *
     * @expectedException \gadelat\QBParseException
     */
    public function testArrayDoesNotParse()
    {
        $builder = $this->createQueryBuilder();
        $qb = $this->getParserUnderTest();

        $qb->parse('["test1","test2"]', $builder);
    }

    /**
     * Just a quick test to make sure that QBP::isNested returns false when
     * there is no nested rules inside the rules...
     */
    public function testIsNestedReturnsFalseWhenEmptyNestedRules()
    {
        $some_json_input = '{
       "condition":"AND",
       "rules":[{
             "condition":"OR",
             "rules":[]
          }]}';

        $builder = $this->createQueryBuilder();
        $qb = $this->getParserUnderTest();

        $qb->parse($some_json_input, $builder);
    }

    public function testStringContains()
    {
        $some_json_input = '{"condition":"AND","rules":[{"id":"heading","field":"heading","type":"string","input":"text","operator":"contains","value":"Johnny"}]}';

        $builder = $this->createQueryBuilder();
        $qb = $this->getParserUnderTest(['heading']);

        $qb->parse($some_json_input, $builder);

        $dql = $builder->getQuery()->getDQL();

        $this->assertEquals('SELECT e FROM gadelat\test\Entity\Opportunity e WHERE e.heading LIKE :heading', $dql);
    }

    public function testManyContains()
    {
        $some_json_input = '{"condition":"AND","rules":[{"id":"sectors","field":"sectors","type":"string","input":"text","operator":"contains","value":"1"}]}';

        $builder = $this->createQueryBuilder();
        $qb = $this->getParserUnderTest();

        $qb->parse($some_json_input, $builder);

        $dql = $builder->getQuery()->getDQL();

        $this->assertEquals('SELECT e FROM gadelat\test\Entity\Opportunity e WHERE :sectors MEMBER OF e.sectors', $dql);
    }

    public function testSingleDateValueConversion()
    {
        $builder = $this->createQueryBuilder();
        $qb = $this->getParserUnderTest();
        $qb->setDateFormat('Y-m-d');

        $qb->parse('{"condition":"AND","rules":[{"id":"price","field":"price","type":"date","input":"text","operator":"less","value":"2016-05-05"}]}', $builder);

        $this->assertEquals(new \DateTime('2016-05-05 00:00:00'), $builder->getParameters()->getValues()[0]->getValue());
    }

    public function testMultipleDatesValueConversion()
    {
        $builder = $this->createQueryBuilder();
        $qb = $this->getParserUnderTest();
        $qb->setDateFormat('Y-m-d');

        $qb->parse('{"condition":"AND","rules":[{"id":"price","field":"price","type":"date","input":"text","operator":"between","value":["2016-05-05", "2016-05-10"]}]}', $builder);

        $dates = array_map(function($e) {return $e->getValue();}, $builder->getParameters()->getValues());

        $this->assertEquals([new \DateTime('2016-05-05 00:00:00'), new \DateTime('2016-05-10 00:00:00')], $dates);
    }

//    /**
//     * QBP should successfully parse OR conditions.
//     *
//     * @throws \gadelat\QBParseException
//     */
//    public function testNestedOrGroup()
//    {
//        $json = '{"condition":"AND",
//        "rules":[
//        {"id":"email_pool","field":"email_pool","type":"string","input":"select","operator":"contains","value":["Fundraising"]},
//        {"condition":"OR","rules":[
//            {"id":"geo_constituency","field":"geo_constituency","type":"string","input":"select","operator":"in","value":["Aberdeen South"]},
//            {"id":"geo_constituency","field":"geo_constituency","type":"string","input":"select","operator":"in","value":["Banbury"]}]}]}';
//        $builder = $this->createQueryBuilder();
//        $qb = $this->getParserUnderTest();
//        $qb->parse($json, $builder);
//        $this->assertEquals('select * where `email_pool` LIKE ? and (`geo_constituency` in (?) or `geo_constituency` in (?))',
//            $builder->getDQL());
//    }
//
//    /**
//     * @throws \gadelat\QBParseException
//     * @expectedException \gadelat\QBParseException
//     * @expectedExceptionMessage Condition can only be one of: 'and', 'or'.
//     */
//    public function testIncorrectCondition()
//    {
//        $json = '{"condition":null,"rules":[
//            {"condition":"AXOR","rules":[
//                {"id":"geo_constituency","field":"geo_constituency","type":"string","input":"select","operator":"in","value":["Aberdeen South"]},
//                {"id":"geo_constituency","field":"geo_constituency","type":"string","input":"select","operator":"in","value":["Aberdeen South"]},
//                {"id":"geo_constituency","field":"geo_constituency","type":"string","input":"select","operator":"is_empty","value":["Aberdeen South"]},
//                {"condition":"AXOR","rules":[
//                    {"id":"geo_constituency","field":"geo_constituency","type":"string","input":"select","operator":"in","value":["Aberdeen South"]},
//                    {"id":"geo_constituency","field":"geo_constituency","type":"string","input":"select","operator":"in","value":["Aberdeen South"]}
//                ]}
//            ]}
//        ]}';
//
//        $builder = $this->createQueryBuilder();
//        $qb = $this->getParserUnderTest();
//        $qb->parse($json, $builder);
//
//        print_r($builder->getDQL());
//    }
}
