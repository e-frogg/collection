<?php
namespace Efrogg\Collection\Repository;


use Efrogg\Db\Adapters\DbAdapter;

abstract class DbRepository implements RepositoryInterface{
    /**
     * @var DbAdapter
     */
    protected $db;

    /**
     * @return DbAdapter
     */
    public function getDb()
    {
        return $this->db;
    }

    /**
     * @param DbAdapter $db
     * @return $this
     */
    public function setDb($db)
    {
        $this->db = $db;
        return $this;
    }

    /**
     * @param DbAdapter $db
     * @return $this
     */
    public static function factory(DbAdapter $db) {
        $instance = new static();
        $instance->setDb($db);
        return $instance;
    }

    abstract public function getTablePrimaryKey();
}