<?php

namespace Efrogg\Collection\tests\units\Assets;

/**
 * @property string anotherDynamicPropertyCamelCase
 * @property mixed initialPropertyCamelCase
 * @property mixed dynamicPropertyCamelCase
 * @property mixed initial_property_snake_case
 * @property string dynamic_snake_case_property
 *
 *
 * @method setDynamicPropertyCamelCase(string $string)
 * @method getDynamicPropertyCamelCase()
 * @method getInitialPropertyCamelCase()
 * @method getAnotherDynamicPropertyCamelCase()
 */
class NonStrictCamelCasePropertyArrayAccess extends \Efrogg\Collection\ObjectArrayAccess
{

    protected static $property_case = self::CAMEL_CASE;

}