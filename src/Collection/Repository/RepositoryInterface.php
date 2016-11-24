<?php
/**
 * Created by PhpStorm.
 * User: raph
 * Date: 23/11/16
 * Time: 17:59
 */

namespace Efrogg\Collection\Repository;


use Efrogg\Collection\ObjectCollection;
use Efrogg\Collection\Query\DecoratorInterface;

interface RepositoryInterface
{
    public function findOne($conditions);

    public function selectOrCreate($conditions);

    public function all();

    /**
     * @param $conditions
     * @param DecoratorInterface $decorator
     * @return ObjectCollection
     */
    public function find($conditions, DecoratorInterface $decorator = null);

    /**
     * renvoie la clé primaire utilsée par les collections
     * @return mixed
     */
    public function getPrimaryKey();
}