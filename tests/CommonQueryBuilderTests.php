<?php

namespace gadelat\test;

use Doctrine\Tests\OrmTestCase;
use gadelat\QueryBuilderParser;

abstract class CommonQueryBuilderTests extends OrmTestCase
{
    protected $simpleQuery = '{"condition":"AND","rules":[{"id":"price","field":"price","type":"double","input":"text","operator":"less","value":"10.25"}]}';
    protected $json1 = '{
       "condition":"AND",
       "rules":[
          {
             "id":"price",
             "field":"price",
             "type":"double",
             "input":"text",
             "operator":"less",
             "value":"10.25"
          },
          {
             "condition":"OR",
             "rules":[
                {
                   "id":"name",
                   "field":"name",
                   "type":"string",
                   "input":"text",
                   "operator":"begins_with",
                   "value":"Thommas"
                },
                {
                   "id":"name",
                   "field":"name",
                   "type":"string",
                   "input":"text",
                   "operator":"equal",
                   "value":"John Doe"
                }
             ]
          }
       ]
    }';

    protected function setUp()
    {
    }

    protected function getParserUnderTest($fields = null)
    {
        return new QueryBuilderParser($fields);
    }

    protected function createQueryBuilder()
    {
        return $this->_getTestEntityManager()->getRepository('gadelat\test\Entity\Opportunity')->createQueryBuilder('e');
    }

    protected function makeJSONForInNotInTest($operator = 'in')
    {
        return '{
           "condition":"AND",
           "rules":[
              {
                 "id":"price",
                 "field":"price",
                 "type":"double",
                 "input":"text",
                 "operator":"less",
                 "value":"10.25"
              },
              {
                 "condition":"OR",
                 "rules":[{
                   "id":"category",
                   "field":"category",
                   "type":"integer",
                   "input":"select",
                   "operator":"'.$operator.'",
                   "value":[
                      "1", "2"
                   ]}
                 ]
              }
           ]
        }
        ';
    }
}
