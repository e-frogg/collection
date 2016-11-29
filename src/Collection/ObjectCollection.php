<?php
/**
 * Created by PhpStorm.
 * User: raph
 * Date: 08/01/16
 * Time: 10:32
 */

namespace Efrogg\Collection;


class ObjectCollection implements \Iterator, \Countable
{

    // data brutes indexées par la PK
    const TRANSFORM_SUM = 0;
    const TRANSFORM_AVG = 1;
    const TRANSFORM_COUNT = 2;
    const TRANSFORM_GROUP_CONCAT = 3;
    const TRANSFORM_VALUES = 4;
    const TRANSFORM_COLLECTION = 5;

    const SORT_ASC = 0;
    const SORT_DESC = 1;


    protected $data = array();

    // liste des PK dans l'ordre iterable
    protected $primary_index = array();

    // liste des PK dans l'ordre iterable
    protected $indexes = array();

    // pointeur pour iteration (PK courante)
    protected $current = 0;

    protected $autoIncrement = 0;

    // nom du champ pour la clé primaire
    protected $primary_key = null;

    /**
     * @var array
     * Liste des index é maintenir
     */
    protected $liste_index_keys = array();

    /**
     * @return \IteratorIterator
     */
    public function getIterator()
    {
        return new \IteratorIterator($this);
    }

    /**
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     * @since 5.0.0
     */
    public function current()
    {
        if (empty($this->data)) {
            return null;
        }
        return $this->data[$this->key()];
    }

    /**
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function next()
    {
        $this->current++;
    }


    public function getNext()
    {
        $this->next();
        return $this->current();
    }

    public function first()
    {
        $this->rewind();
        return $this->current();
    }

    /**
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     * @since 5.0.0
     */
    public function key()
    {
        if (!isset($this->primary_index[$this->current])) {
            return null;
        }
        return $this->primary_index[$this->current];
    }


    public function setKey($param)
    {
        $pos = array_search($param, $this->primary_index);
        if ($pos !== false) {
            $this->current = $pos;
        }
//        echo "setKey $param => $pos";
//        exit;

    }

    /**
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     * @since 5.0.0
     */
    public function valid()
    {
        return isset($this->data[$this->key()]);
    }

    /**
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function rewind()
    {
        $this->current = 0;
    }

    /**
     * @param $key
     * @return bool
     */
    public function exists($key)
    {
        if (is_array($key)) {
            return !is_null($this->getOneBy($key));
        } else {
            return isset($this->data[$key]);
        }
    }

    /**
     * @param $key_name
     * @return $this
     */
    public function setPrimary($key_name)
    {
        $this->primary_key = $key_name;
        if (!empty($this->data)) {
            throw new \Exception("Primary key doit être défini sur une collection vide");
        }
        return $this;
    }

    /**
     * Test si l'index est déclaré
     * @param $key_name
     * @return bool
     */
    public function hasIndex($key_name)
    {
        return isset($this->liste_index_keys[$key_name]) || $key_name == $this->primary_key;
    }

    public function addIndex($key_name, $index_type = "index")
    {
        if ($this->hasIndex($key_name)) {
            return $this;
        }

        $this->liste_index_keys[$key_name] = $index_type;

        if (!empty($this->data)) {
            foreach ($this->data AS $pk => $item) {
                $val = $item->{$key_name};
                $this->indexes[$key_name][$val][] = $pk;
            }
        }
        return $this;
    }

    public function removeIndex($key_name)
    {
        unset($this->liste_index_keys[$key_name]);
        unset($this->indexes[$key_name]);
        return $this;
    }

    /**
     * @param $item
     * @return bool
     */
    public function add($item)
    {
        if (!is_null($this->primary_key)) {
            $pk = $item->{$this->primary_key};
        } else {
            $pk = $this->autoIncrement++;
        }

        if (null === $pk) {
            pp($this->primary_key);
            pp($item);
            dd("no primary");
            // interdit d'ajouter un objet qui nous met une clé primaire nulle
            return false;
        }

        $this->data[$pk] = $item;
        $this->primary_index[] = $pk;

        foreach ($this->liste_index_keys as $key => $type_index) {
            $k = $item->{$key};

            $this->indexes[$key][$k][] = $pk;
        }

        return true;
    }

    public function get($key)
    {
        return isset($this->data[$key])?$this->data[$key]:null;
    }

    public function getColumn($column_name, $unique = true)
    {
        $column = array();
        if ($column_name == $this->primary_key) {
            // primary
            return array_values($this->primary_index);
        } elseif (isset($this->indexes[$column_name])) {
            // index
            return array_keys($this->indexes[$column_name]);
        } else {
            // full scan
            if (self:: isKeyNested($column_name)) {
                foreach ($this AS $item) {
                    $column[] = self:: getNestedValue($item, $column_name);
                }
            } else {
                foreach ($this AS $item) {
                    if (isset($item->$column_name)) {
                        $column[] = $item->{$column_name};
                    }
                }
            }
            if ($unique) {
                $column = array_values(array_unique($column));
            } else {
                $column = array_values($column);
            }
        }
        return $column;
    }

    public function getOneBy($selector)
    {
        //TODO : optimiser.... ne pas créer une collection pour extraire un seul elem....
        return $this->getBy($selector, 1)->current();
    }

    public function factoryFromThis($withIndexes = true)
    {
        $collection = new static();
        if ($withIndexes) {
            $collection->setPrimary($this->primary_key);
            foreach ($this->liste_index_keys as $key => $type_index) {
                $collection->addIndex($key, $type_index);
            }
        }
        return $collection;
    }

    /**
     * @param $selector
     * @param int $limit
     * @param bool $withIndexes
     * Indique si la collection retournée contient les indexes
     * @return ObjectCollection
     */
    public function getBy($selector, $limit = null, $withIndexes = true)
    {
        if (is_null($limit)) {
            $limit = 999999;
        }

        // préparation de la collection
        $collection = $this->factoryFromThis($withIndexes);
        if ($withIndexes) {
            // l'index sur la recherche effectuée ne sert a rien
            foreach (array_keys($selector) as $key_name) {
                $collection->removeIndex($key_name);
            }
        }

        // multiple pass pour trouver les items correspondants é une recherche multiple
        $combined = null;
        foreach ($selector as $key => $value) {
            $pks = $this->getPks($key, $value);

            if (is_null($combined)) {
                $combined = $pks;
            } else {
                $combined = array_intersect($combined, $pks);
            }
        }

        foreach ($combined as $k) {
            $collection->add($this->data[$k]);
            if ($limit-- <= 1) {
                break;
            }
        }

        return $collection;
    }

    /**
     * @param callable $user_filter_callback
     * @return ObjectCollection
     */
    public function getByCallback($user_filter_callback)
    {
        $collection = $this->factoryFromThis();
        foreach ($this as $item) {
            if ($user_filter_callback($item)) {
                $collection->add($item);
            }
        }
        return $collection;
    }

    /**
     * Le premier item de chaque "groupe" est conservé.
     * Dans le cas d'une transformation (SUM, AVG....), l'enregistrement original est modifié
     * Si on a une transform en "COLLECTION", l'enregistrement original est cloné avant d'étre modifié, afin de conserver une liste des éléments originaux non modifiés
     *
     * @param $columnName
     * @param array $transforms
     * @return ObjectCollection
     */
    public function groupBy($columnName, $transforms = array())
    {
        $collection = $this->factoryFromThis();
        $collection->setPrimary($columnName);

        // pré-calcul des clés de transformation
        $keysTransform = array();
        $useClone = false;
        $useTransform = false;
        if (!empty($transforms)) {
            $useTransform = true;
            foreach ($transforms as $new_column_name => $type) {
                $keysTransform[] = $type[0];
                if ($type[1] == self::TRANSFORM_COLLECTION) {
                    $useClone = true;
                }
            }
            $keysTransform = array_unique($keysTransform);
        }

        $done = array();
        foreach ($this AS $one) {
            if (property_exists($one, $columnName)) {
                $key = $one->{$columnName};
                if (!isset($done[$key])) {
                    //                $one -> __count ++;

                    if ($useClone) {
                        $cloneOne = clone($one);
                    } else {
                        $cloneOne = $one;
                    }
                    $collection->add($cloneOne);

                    $done[$key] = $cloneOne;

                    // gestion des aggregs
                    if ($useTransform) {
                        foreach ($keysTransform as $col_name) {
                            $cloneOne->{'__' . $col_name . "_detail"} = array(
                                "values" => array($cloneOne->{$col_name}),
                                "original" => array($one)
                            );
                        }
                    }
                } elseif ($useTransform) {
                    // gestion des aggregs
                    $originalOne = $done[$key];
                    foreach ($keysTransform as $col_name) {
                        $detail = &$originalOne->{'__' . $col_name . "_detail"};
                        $detail["values"] [] = $one->{$col_name};
                        $detail["original"] [] = $one;
                    }
                }
            }
        }

        // gestion des aggregs
        if ($useTransform) {
            foreach ($collection as $one) {
                foreach ($transforms as $new_column_name => $type) {
                    $this->transformGroup($one->{'__' . $type[0] . "_detail"}, $type[1]);
                    $one->{$new_column_name} = $one->{'__' . $type[0] . "_detail"}[$type[1]];
                }
            }
        }
        $collection->rewind();

        return $collection;
    }


    /**
     * @param ObjectCollection $collection
     * @param $on_conditions
     * @param string|null $column_name
     * Nom de la nouvelle colonne créée si nécessaire (l'objet sera lié)
     * Si null, les données publiques de l'objet seront mergées
     * @param bool $singleJoin
     * Définit le comportement en cas de matching multiple :
     * true => on multiplie les lignes (idem SQL).
     * false => on ne renseigne qu'un item en jointure
     * @param bool $use_clone
     * Définit si un clone des items d'origine sont générés
     * @return ObjectCollection
     */
    public function leftJoin(
        ObjectCollection $collection,
        $on_conditions,
        $column_name = null,
        $singleJoin = true,
        $use_clone = false
    ) {
        // on ajoute éventuellement les index pour la jointure
        foreach ($on_conditions as $keyA => $keyB) {
            $this->addIndex($keyA);
            $collection->addIndex($keyB);
        }

        $newCollection = $this->factoryFromThis();

        foreach ($this AS $itemA) {
            foreach ($collection AS $itemB) {
                foreach ($on_conditions AS $keyA => $keyB) {
                    if ($itemA->{$keyA} == $itemB->{$keyB}) {
                        if (is_null($column_name)) {
                            if ($use_clone) {
                                $clone = clone($itemA);
                            } else {
                                $clone = $itemA;
                            }

                            // on copie les propriétés publiques
                            foreach ($itemB AS $k => $v) {
                                $clone->{$k} = $v;
                            }

                            if ($use_clone) {
                                $newCollection->add($clone);
                            }

                        } else {
                            if ($singleJoin) {
                                $itemA->{$column_name} = $itemB;
                            } else {
                                $itemA->{$column_name} [] = $itemB;
                            }
                        }

                        // jointure simple, on ne joint qu'une valeur
                        if ($singleJoin) {
                            break;
                        }
                    }
                }
            }
        }

        return $use_clone ? $newCollection : $this;
    }

    public function orderByCallback($callback_order)
    {
        $collection = $this->factoryFromThis();

        $arr = array();
        foreach ($this AS $item) {
            $arr[] = $item;
        }

        usort($arr, $callback_order);
        $collection->addMultiple($arr);
        return $collection;
    }

    /**
     * @param $orders
     * ex : array(
     *      "key1" => ObjectCollection::SORT_ASC,
     *      "key2.subkey" => ObjectCollection::SORT_DESC
     * )
     * @return ObjectCollection
     */
    public function orderBy($orders)
    {
        return $this->orderByCallback(
            function ($a, $b) use ($orders) {
                foreach ($orders AS $k => $sens) {
                    if (ObjectCollection::isKeyNested($k)) {
                        $va = ObjectCollection::getNestedValue($a, $k);
                        $vb = ObjectCollection::getNestedValue($b, $k);
                    } else {
                        $va = $a->{$k};
                        $vb = $b->{$k};
                    }
                    if ($sens == ObjectCollection::SORT_DESC) {
                        if ($va > $vb) {
                            return -1;
                        }
                        if ($va < $vb) {
                            return 1;
                        }
                    } else {
                        if ($va > $vb) {
                            return 1;
                        }
                        if ($va < $vb) {
                            return -1;
                        }
                    }
                }
                return 0;
            });

    }

    protected function getPks($key_name, $key_value)
    {
//        var_dump($key_value);
        if (!is_array($key_value)) {
            $key_value = array($key_value);
        }

        if ($key_name == $this->primary_key) {
            return $key_value;
        }

        $index_values = array();
        if (isset($this->indexes[$key_name])) {
            // colonne indexée
            foreach ($key_value as $one_key_value) {
                if (isset($this->indexes[$key_name][$one_key_value])) {
                    $one_index_values = $this->indexes[$key_name][$one_key_value];

                    if (empty($index_values)) {
                        $index_values = $one_index_values;
                    } else {
                        $index_values = array_merge($index_values, $one_index_values);
                    }
                }
            }

        } else {
            // full scan
            foreach ($this as $k => $item) {
                if (self::isKeyNested($key_name)) {
                    $current_value = self::getNestedValue($item, $key_name);
                } else {
                    $current_value = $item->{$key_name};
                }

                foreach ($key_value as $one_key_value) {
                    if ($current_value == $one_key_value) {
                        $index_values[] = $k;
                        break; // match sur une value, on break
                    }
                }
            }
        }
//        var_dump($index_values);
        return $index_values;
    }

    /**
     * @param $items
     * @return $this
     */
    public function addMultiple($items)
    {
        foreach ($items as $item) {
            if(is_array($item)) {
                $item = new ArrayObjectItem($item);
            }
            $this->add($item);
        }
        return $this;
    }

    /**
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     * @since 5.1.0
     */
    public function count()
    {
        return count($this->primary_index);
    }

    public static function isKeyNested($column_name)
    {
        return strpos($column_name, ".") > 0;
    }

    /**
     * @param $item
     * ex : nested Object
     * @param $column_name
     * ex : data.ref
     * @return mixed
     */
    public static function getNestedValue($item, $column_name)
    {
        $split = explode(".", $column_name);
        $value = $item;
        foreach ($split as $k) {
            $value = $value->{$k};
        }
        return $value;
    }

    private function transformGroup(&$detail, $type)
    {
        switch (strtoupper($type)) {
            case self::TRANSFORM_SUM:
                $detail[$type] = array_sum($detail["values"]);
                break;
            case self::TRANSFORM_AVG:
                $detail[$type] = array_sum($detail["values"]) / count($detail["values"]);
                break;
            case self::TRANSFORM_COUNT:
                $detail[$type] = count($detail["values"]);
                break;
            case self::TRANSFORM_GROUP_CONCAT:
                $detail[$type] = implode(",", $detail["values"]);
                break;
            case self::TRANSFORM_VALUES:
                $detail[$type] = $detail["values"];
                break;
            case self::TRANSFORM_COLLECTION:
                $collect = new ObjectCollection();
                $collect->addMultiple($detail["original"]);
                $detail[$type] = $collect;
                break;
        }
    }

    public function __clone()
    {
        $new = $this->factoryFromThis(true);
        $new->addMultiple($this);
        return $new;
    }

    public function each(callable $callback) {
        foreach($this->data as $k => $item) {
            $callback($item);
        }
    }

}