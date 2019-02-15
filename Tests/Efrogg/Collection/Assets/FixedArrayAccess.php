<?php

namespace Efrogg\Collection\tests\units\Assets;

/**
 * @property int $authorized_index
 * @property int $another_authorized_index
 * @property string[] $authorized_array_values
 *
 * @method  int getAuthorizedIndex()
 * @method  int getAnotherAuthorizedIndex()
 * @method  string[] getAuthorizedArrayValues()
 *
 * @method  $this setAuthorizedIndex(int $values)
 * @method  $this setAnotherAuthorizedIndex(int $values)
 * @method  $this addAuthorizedArrayValue(string $value)
 * @method  $this setAuthorizedArrayValues(string[] $values)
 */
class FixedArrayAccess extends \Efrogg\Collection\ObjectArrayAccess
{

    protected static $fixed_structure = self::STATIC_STRUCTURE;
    protected static $structure_properties = [
        "authorized_index",
        "another_authorized_index",
        "authorized_array_values"
    ];

}