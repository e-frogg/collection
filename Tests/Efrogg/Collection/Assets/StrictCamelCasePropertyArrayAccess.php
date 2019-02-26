<?php

namespace Efrogg\Collection\tests\units\Assets;

/**
 * @property string dynamicSnakeCaseProperty
 * @property string anotherDynamicPropertyCamelCase
 * @property mixed initialPropertySnakeCase
 * @property mixed initialPropertyCamelCase
 * @property mixed dynamicPropertyCamelCase
 *
 *
 * @method setDynamicPropertyCamelCase(string $string)
 * @method getInitialPropertySnakeCase()
 * @method getDynamicPropertyCamelCase()
 * @method getInitialPropertyCamelCase()
 * @method getAnotherDynamicPropertyCamelCase()
 */
class StrictCamelCasePropertyArrayAccess extends \Efrogg\Collection\ObjectArrayAccess
{

    protected static $property_case = self::CAMEL_CASE;
    protected static $strict_property_case = true;

}