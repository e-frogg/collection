<?php
/**
 * Created by PhpStorm.
 * User: raph
 * Date: 26/04/18
 * Time: 10:18
 */

namespace Efrogg\Collection;


class ObjectArrayAccess implements \ArrayAccess
{
    public function __construct($data)
    {
        $this->data=$data;
    }


    protected $data = [];
    /**
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        return isset($this->data[$offset]) && array_key_exists($offset,$this->data);
    }

    /**
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        return $this->data[$offset];
    }

    /**
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
    }

    /**
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    public function __isset($name)
    {
        return $this->offsetExists($name);
    }


    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    public function __get($name)
    {
        return $this->data[$name];
    }

    public function __call($name, $arguments)
    {
        // add : fluent setter
        if (strpos($name, "add") === 0) {
            // add to an array
            // addStep => $this->steps[]=...
            $property_name = $this->getSnakeCase(substr($name,3)).'s';
            foreach($arguments as $item) {
                if(property_exists($this,$property_name)) {
                    // propriété existante
                    $this->$property_name[]=$item;
                } elseif($this->__isset($property_name)) {
                    // propriété dynamique existante
                    $this->data[$property_name][]=$item;
                } else {
                    // création propriété dynamique
                    $this->data[$property_name]=[$item];
                }
            }
            return $this;
        }

        // get : fluent setter
        if(strpos($name, "set") === 0) {
            $property_name = $this->getSnakeCase(substr($name,3));
            $this->data[$property_name]=$arguments[0];
            return $this;
        }

        if(strpos($name, "get") === 0) {
            $property_name = $this->getSnakeCase(substr($name,3));
            if(property_exists($this,$property_name) || $this->__isset($property_name)) {
                return $this->$property_name;
            }
        }

        // rien trouvé
        return null;
    }

    public function getSnakeCase($camel_case)
    {
        return preg_replace_callback("/([A-Z]{1})/",function($majuscule) {
            return '_'.strtolower($majuscule[1]);
        },lcfirst($camel_case));
    }

    public function getCamelCase($snake_case)
    {
        $camel_case = preg_replace_callback("#_(.)#",function($minuscule) {
            return strtoupper($minuscule[1]);
        },lcfirst($snake_case));
        return $camel_case;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData(array $data)
    {
        $this->data = $data;
    }

    /**
     * détermine si la propriété existe et n'est pas vide
     * @param $property_name
     * @return bool
     */
    protected function propertyIsNotEmpty($property_name)
    {
        return (isset($this->$property_name) || $this->__isset($property_name)) && !empty($property_name);
    }


}