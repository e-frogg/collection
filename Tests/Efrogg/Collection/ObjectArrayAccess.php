<?php

namespace Efrogg\Collection\tests\units;

require_once __DIR__ . '/../../../src/Collection/ObjectCollection.php';
require_once __DIR__ . '/Assets/FixedArrayAccess.php';
require_once __DIR__ . '/Assets/FixedArrayAccessWithException.php';
require_once __DIR__ . '/Assets/StandardCaseArrayAccess.php';
require_once __DIR__ . '/Assets/StrictCamelCasePropertyArrayAccess.php';
require_once __DIR__ . '/Assets/NonStrictCamelCasePropertyArrayAccess.php';
require_once __DIR__ . '/polyfill.php';

use atoum;
use Efrogg\Collection\tests\units\Assets\FixedArrayAccess;
use Efrogg\Collection\tests\units\Assets\FixedArrayAccessWithException;
use Efrogg\Collection\tests\units\Assets\NonStrictCamelCasePropertyArrayAccess;
use Efrogg\Collection\tests\units\Assets\StandardCaseArrayAccess;
use Efrogg\Collection\tests\units\Assets\StrictCamelCasePropertyArrayAccess;

class ObjectArrayAccess extends atoum
{

    /**
     * Test la cr�ation d'une instance
     */

    public function testConstructor()
    {
        $this
            ->object(new FixedArrayAccess())
            ->isInstanceOf(\Efrogg\Collection\ObjectArrayAccess::class);
    }

    public function testSimpleAccess()
    {
        $variable = "variable";
        $valeur = "valeur";
        $flexible = new \Efrogg\Collection\ObjectArrayAccess([$variable => $valeur]);

        $this
            ->string($flexible->$variable)
            ->isEqualTo($valeur);

        // test camelcase
        $flexible->setCustomVariable(14);

        $this
            ->integer($flexible->custom_variable)
            ->isEqualTo(14)
            ->integer($flexible->custom_variable)
            ->isEqualTo($flexible->getCustomVariable());

        $flexible
            ->addAuthorizedArrayValue("value 1")
            ->addAuthorizedArrayValue("value 2")
            ->addUnauthorizedArrayValue("value 2");

        $this
            ->array($flexible->authorized_array_values)
            ->hasSize(2)
            ->isEqualTo(["value 1", "value 2"])
            ->array($flexible->getAttributes())
            ->contains("authorized_array_values")
            ->contains("unauthorized_array_values");
    }

    public function testFixedStructure()
    {
        $fixed = new FixedArrayAccess([
            "authorized_index" => 123,
            "unauthorized_index" => 456
        ]);

        $this
            ->variable($fixed->getAuthorizedIndex())
            ->isEqualTo(123)
            ->variable($fixed->getUnauthorizedIndex())
            ->isNull()
            ->array($fixed->getAttributes())
            ->contains("authorized_index")
            ->notContains("unauthorized_index");

        $fixed
            ->addAuthorizedArrayValue("value 1")
            ->addAuthorizedArrayValue("value 2")
            ->addUnauthorizedArrayValue("value 2");

        $this
            ->array($fixed->authorized_array_values)
            ->hasSize(2)
            ->isEqualTo(["value 1", "value 2"])
            ->array($fixed->getAttributes())
            ->contains("authorized_array_values")
            ->notContains("unauthorized_array_values");
    }

    public function testFixedStructureWithException()
    {
        $this
            ->exception(function () {
                $fixed = new FixedArrayAccessWithException([
                    "authorized_index" => 123,
                    "unauthorized_index" => 456
                ]);
            })
            ->isInstanceOf(\RuntimeException::class)
;
        $fixed = new FixedArrayAccessWithException([
            "authorized_index" => 123,
        ]);
        $this
            ->variable($fixed->getAuthorizedIndex())
            ->isEqualTo(123)
            ->array($fixed->getAttributes())
            ->contains("authorized_index")
            ->notContains("unauthorized_index");

        $fixed
            ->addAuthorizedArrayValue("value 1")
            ->addAuthorizedArrayValue("value 2");

        $this
            ->exception(function () use ($fixed) {
                $fixed
                    ->addUnauthorizedArrayValue("value 2");
            })
            ->isInstanceOf(\RuntimeException::class)
            ->exception(function () use ($fixed) {
                $fixed
                    ->setUnauthorizedValue("value 2");
            })
            ->isInstanceOf(\RuntimeException::class)
            ->exception(function () use ($fixed) {
                $fixed
                    ->unauthorized_value = "value";
            })
            ->isInstanceOf(\RuntimeException::class)
        ;
    }

    public function testDefaultCaseconversion()
    {
        $obj = new \Efrogg\Collection\ObjectArrayAccess([
            "initial_property_snake_case" => "snake case A",
            "initialPropertyCamelCase" => "camelCase B",
        ]);

        // par défaut : non strict, snake case propriétés, camel pour les méthodes
        $obj->setDynamicPropertyCamelCase("camelCase C");
        $obj->anotherDynamicPropertyCamelCase = "camelCase D";
        $obj->dynamic_snake_case_property = "snake_case E";

        // tests

        // A
        $this->string($obj->initial_property_snake_case)
            ->isEqualTo($obj->getInitialPropertySnakeCase())
            ->isEqualTo("snake case A");

        // B
        $this->string($obj->initialPropertyCamelCase)
            ->isEqualTo("camelCase B");
        $this->variable($obj->getInitialPropertyCamelCase())
            ->isNull();

        // C
        $this->string($obj->dynamic_property_camel_case)
            ->isEqualTo("camelCase C")
            ->isEqualTo($obj->getDynamicPropertyCamelCase());

        // D
        $this->string($obj->anotherDynamicPropertyCamelCase)
            ->isEqualTo("camelCase D");
        $this->variable($obj->getAnotherDynamicPropertyCamelCase())
            ->isNull();

        $this->string($obj->dynamic_snake_case_property)
            ->isEqualTo("snake_case E");
    }

    public function testStrictCamelCasePropertiesCaseconversion()
    {
        $obj = new StrictCamelCasePropertyArrayAccess([
            "initial_property_snake_case" => "snake case A",
            "initialPropertyCamelCase" => "camelCase B",
        ]);

        // par défaut : non strict, snake case propriétés, camel pour les méthodes
        $obj->setDynamicPropertyCamelCase("camelCase C");
        $obj->anotherDynamicPropertyCamelCase = "camelCase D";
        $obj->dynamic_snake_case_property = "snake_case E";

        // tests

        // A
        $this->string($obj->initialPropertySnakeCase)
            ->isEqualTo($obj->getInitialPropertySnakeCase())
            ->isEqualTo("snake case A");
        $this->variable($obj->initial_property_snake_case)
            ->isNull();

        // B
        $this->string($obj->initialPropertyCamelCase)
            ->isEqualTo($obj->getInitialPropertyCamelCase())
            ->isEqualTo("camelCase B");

        // C
        $this->string($obj->dynamicPropertyCamelCase)
            ->isEqualTo("camelCase C")
            ->isEqualTo($obj->getDynamicPropertyCamelCase());

        // D
        $this->string($obj->anotherDynamicPropertyCamelCase)
            ->isEqualTo($obj->getAnotherDynamicPropertyCamelCase())
            ->isEqualTo("camelCase D");

        $this->string($obj->dynamicSnakeCaseProperty)
            ->isEqualTo("snake_case E");
        $this->variable($obj->dynamic_snake_case_property)
            ->isNull();
    }


    public function testNonStrictCamelCasePropertiesCaseconversion()
    {
        $obj = new NonStrictCamelCasePropertyArrayAccess([
            "initial_property_snake_case" => "snake case A",
            "initialPropertyCamelCase" => "camelCase B",
        ]);

        // par défaut : non strict, snake case propriétés, camel pour les méthodes
        $obj->setDynamicPropertyCamelCase("camelCase C");
        $obj->anotherDynamicPropertyCamelCase = "camelCase D";
        $obj->dynamic_snake_case_property = "snake_case E";

        // tests

        // A
        $this->string($obj->initial_property_snake_case)
            ->isEqualTo("snake case A");
        $this->variable($obj->getInitialPropertySnakeCase())
            ->isNull();

        // B
        $this->string($obj->initialPropertyCamelCase)
            ->isEqualTo($obj->getInitialPropertyCamelCase())
            ->isEqualTo("camelCase B");

        // C
        $this->string($obj->dynamicPropertyCamelCase)
            ->isEqualTo("camelCase C")
            ->isEqualTo($obj->getDynamicPropertyCamelCase());

        // D
        $this->string($obj->anotherDynamicPropertyCamelCase)
            ->isEqualTo($obj->getAnotherDynamicPropertyCamelCase())
            ->isEqualTo("camelCase D");

        $this->string($obj->dynamic_snake_case_property)
            ->isEqualTo("snake_case E");
        $this->variable($obj->getDynamicSnakeCaseProperty())
            ->isNull();
    }

}
