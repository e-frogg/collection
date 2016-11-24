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
     */
    public function setDb($db)
    {
        $this->db = $db;
    }

    /**
     * @param DbAdapter $db
     * @return static
     */
    public static function factory(DbAdapter $db) {
        $instance = new static();
        $instance->setDb($db);
        return $instance;
    }

    abstract public function getTablePrimaryKey();
}