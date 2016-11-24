<?php
/**
 * Created by PhpStorm.
 * User: raph
 * Date: 22/04/16
 * Time: 09:36
 */

namespace Efrogg\Collection\Cache;



use Efrogg\Collection\ObjectCollection;
use Efrogg\Collection\Query\DecoratorInterface;
use Efrogg\Collection\Repository\RepositoryInterface;

abstract class CacheRepository
{
    /**
     * @var bool
     *
     */
    protected $select_on_demand = true;

    /**
     * @var RepositoryInterface
     */
    protected  $repository;

    /** @var  ObjectCollection */
    protected $collection;

    public function __construct(RepositoryInterface $repository=null) {
        if(null !== $repository) {
            $this->setRepository($repository);
        }
    }

    abstract public function prepare($liste_id);

    /**
     * Effectue une recherche parmi les résultats EN CACHE (préparés)
     * @return ObjectCollection
     */
    public function find($selector, $limit = null, $withIndexes = true) {
        return $this->collection->getBy($selector, $limit , $withIndexes);
    }

    /**
     * met en cache les résultats fournis par le repository
     * @param array $condition
     * @param DecoratorInterface $decorator
     * @return $this
     */
    public function prepareBy($condition=[],DecoratorInterface $decorator=null) {
        $this->collection -> addMultiple($this -> repository -> find($condition,$decorator));
        return $this;
    }

    protected function reset()
    {
        $this->collection = new ObjectCollection();
        $this->collection->setPrimary($this -> repository -> getPrimaryKey());
    }

    public function get($id) {
        if(!$this->collection->exists($id) && $this->select_on_demand) {
            $this->prepare(array($id));
        }
        return $this->collection->get($id);
    }

    public function only($ids) {
        //TODO
    }

//    public function selectOrCreate($conditions)
//    {
//        $result = $this->getOneBy($conditions);
//        if(!$result) {
////            create ???
//        }
//    }

    /**
     * @return ObjectCollection
     */
    public function all()
    {
        return $this->collection;
    }

    public function exists($id) {
        return $this->collection->exists($id);
    }

    /**
     * @param $selector
     * @return $this
     */
    public function getOneBy($selector)
    {
        $one = $this->collection -> getOneBy($selector);
        if(is_null($one) && $this -> select_on_demand) {
            $this -> prepareBy($selector);
            return $this->collection -> getOneBy($selector);
        }

        return $one;
    }

    /**
     * @param boolean $select_on_demand
     * @return $this
     */
    public function setSelectOnDemand($select_on_demand)
    {
        $this->select_on_demand = $select_on_demand;
        return $this;
    }

    /**
     * @return ObjectCollection
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * @param ObjectCollection $collection
     */
    public function setCollection($collection)
    {
        $this->collection = $collection;
    }

    /**
     * @param RepositoryInterface $repository
     * @return $this
     */
    public function setRepository($repository)
    {
        $this->repository = $repository;
        $this -> reset();
        return $this;
    }

    /**
     * @return RepositoryInterface
     */
    public function getRepository()
    {
        return $this->repository;
    }
}