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
    const FLEXIBLE_STRUCTURE = false;
    const STATIC_STRUCTURE = 1;
    const STATIC_STRUCTURE_WITH_EXCEPTION = 2;
    const SNAKE_CASE = "SNAKE";
    const CAMEL_CASE = "CAMEL";

    protected static $fixed_structure = self::FLEXIBLE_STRUCTURE;
    protected static $structure_properties = [];

    protected static $property_case = self::SNAKE_CASE;
    protected static $method_case = self::CAMEL_CASE;
    protected static $strict_property_case = false;

    public function __construct($data=[])
    {
        $this->setData($data);
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
//        $offset = $this->getOffsetName($offset);
        return property_exists($this, $offset) ||
            (isset($this->data[$offset]) && array_key_exists($offset, $this->data));
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
    public function & offsetGet($offset)
    {
//        $offset = $this->getOffsetName($offset);
        if (property_exists($this, $offset)) {
            return $this->$offset;
        }
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
     *
     * @throws \RuntimeException
     */
    public function offsetSet($offset, $value)
    {
        $offset = $this->getOffsetName($offset);
        if (property_exists($this, $offset)) {
            $this->$offset = $value;
        } else {
            if ($this->offsetIsAuthorized($offset)) {
                $this->data[$offset] = $value;
            }
        }
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
        if (property_exists($this, $offset)) {
            unset($this->$offset);
        } else {
            unset($this->data[$offset]);
        }
    }

    public function __isset($name)
    {
        return $this->offsetExists($name);
    }


    public function __set($name, $value)
    {
        $this->offsetSet($name, $value);
    }

    public function & __get($name)
    {
        return $this->offsetGet($name);
    }

    public function __call($name, $arguments)
    {
        // add : fluent setter
        if (strpos($name, "add") === 0) {
            // add to an array
            // addStep => $this->steps[]=...
            // addAddress => $this->addresses[]=...
            $property_name = Pluralizer::plural($this->methodNameToPropertyName(substr($name, 3)));
            if($this->offsetIsAuthorized($property_name)) {
                foreach ($arguments as $item) {
                    if (property_exists($this, $property_name)) {
                        // propriété existante
                        $this->$property_name[] = $item;
                    } elseif ($this->__isset($property_name)) {
                        // propriété dynamique existante
                        $this->data[$property_name][] = $item;
                    } else {
                        // création propriété dynamique
                        $this->data[$property_name] = [$item];
                    }
                }
            }
            return $this;
        }

        // get : fluent setter
        if(strpos($name, "set") === 0) {
            $property_name = $this->methodNameToPropertyName(substr($name, 3));
            $this->offsetSet($property_name, $arguments[0]);
            return $this;
        }

        if(strpos($name, "get") === 0) {
            $property_name = $this->methodNameToPropertyName(substr($name, 3),true);
            if(property_exists($this,$property_name) || $this->__isset($property_name)) {
                return $this->$property_name;
            }
        }

        // rien trouvé
        return null;
    }

    private function methodNameToPropertyName($method_name,$force_conversion=false)
    {
        if (!$force_conversion && static::$strict_property_case) {
            // la conversion sera faite ailleurs
            return lcfirst($method_name);
        }

        if (static::$property_case === static::$method_case) {
            // pas de conversion, lcfirst dans tous les cas
            return lcfirst($method_name);
        }
        if (static::$property_case === self::SNAKE_CASE) {
            // conversion camel -> snake
            return $this->getSnakeCase($method_name);
        }

        // conversion snake -> camel
        return $this->getCamelCase($method_name);
    }


    protected function getSnakeCase($camel_case)
    {
        return preg_replace_callback("/([A-Z]{1})/",function($majuscule) {
            return '_'.strtolower($majuscule[1]);
        },lcfirst($camel_case));
    }

    protected function getCamelCase($snake_case)
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
        foreach($data as $key => $value) {
            $this->offsetSet($key,$value);
        }
    }

    /**
     * renvoie la liste des propriétés accessibles dans l'objet automatique
     * @return array
     */
    public function getAttributes()
    {
        return array_keys($this->data);
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

    /**
     * @param $offset
     * @return bool
     *
     * @throws \RuntimeException
     */
    private function offsetIsAuthorized($offset)
    {
        if (static::$fixed_structure>0 && !\in_array($offset, static::$structure_properties, true)) {
            // set d'un offset non autorisé
            if (static::$fixed_structure === self::STATIC_STRUCTURE_WITH_EXCEPTION) {
                throw new \RuntimeException("The offset $offset is not allowed here");
            }
            // on ne sauvegarde pas la donnée
            return false;
        }

        return true;
    }

    protected function getOffsetName($offset_name)
    {
        if (static::$strict_property_case) {

            if (static::$property_case === self::SNAKE_CASE) {
                return $this->getSnakeCase($offset_name);
            }
            return $this->getCamelCase($offset_name);
        }
        return $offset_name;
    }

    public function recursiveGetData()
    {
        return $this->recursiveGet($this->data);

    }

    private function recursiveGet(array $data)
    {
        foreach ($data as $k=>$v) {
            if(is_array($v)) {
                $data[$k]=$this->recursiveGet($data[$k]);
            } elseif($v instanceof self) {
                $data[$k] = $v->recursiveGetData();
            }
        }
        return $data;
    }

}
