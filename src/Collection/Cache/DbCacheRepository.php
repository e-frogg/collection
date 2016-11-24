<?php
/**
 * Created by PhpStorm.
 * User: raph
 * Date: 22/04/16
 * Time: 09:36
 */

namespace Efrogg\Collection\Cache;



use Efrogg\Db\Query\DbQueryDecorator;
use Efrogg\Db\Tools\DbTools;
use Efrogg\Collection\Query\DecoratorInterface;
use Efrogg\Collection\Repository\DbRepository;

class DbCacheRepository extends CacheRepository
{
    /**
     * @var DbRepository
     */
    protected $repository;

    public function prepare($liste_id) {
        $this -> prepareBy(array($this -> repository -> getTablePrimaryKey(),$liste_id));
    }

    /**
     * met en cache les résultats fournis par le repository
     * @param array $condition
     * @param DbQueryDecorator|null $decorator
     * @return $this
     */
    public function prepareBy($condition=[],DecoratorInterface $decorator=null) {
        // conversion d'un select collection en select DB (valeur multiple)
        $where = array();
        foreach($condition as $k => $v) {
            if(is_array($v)) {
                $v = array_map(function($value) {
                    if(is_string($value)) return DbTools::stringtohex($value);    // todo : remove dépendance
                    return $value;
                },$v);
                $where[$k] = array(" IN (".implode(",",$v).")");
            } else {
                $where[$k] = $v;
            }
        }

        return parent::prepareBy($where,$decorator);
    }

}