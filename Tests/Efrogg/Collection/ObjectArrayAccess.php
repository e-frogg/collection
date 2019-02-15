<?php

namespace Efrogg\Collection\tests\units;

require_once __DIR__ . '/../../../src/Collection/ObjectCollection.php';
require_once __DIR__ . '/Assets/FixedArrayAccess.php';
require_once __DIR__ . '/polyfill.php';

use atoum;
use Efrogg\Collection\tests\units\Assets\FixedArrayAccess;
use Efrogg\Collection\tests\units\Assets\FixedArrayAccessWithException;

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
}
