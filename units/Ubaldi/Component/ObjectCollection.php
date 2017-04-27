<?php
namespace Ubaldi\Component\tests\units;

require_once __DIR__ . '/../../../../src/Ubaldi/Component/ObjectCollection.php';

use atoum;

class ObjectCollection extends atoum {

    protected function getSimpleTestableObjectInstance($int=15) {
        $nested_item=new \stdClass();
        $nested_item->nested_string='nested_string';
        $nested_item->nested_int=$int;

        return $nested_item;
    }

    /**
     * Factory permettant de fabriquer une collection simple avec 3 items statiques
     *
     * @return \Ubaldi\Component\ObjectCollection
     */
    protected function factorySimpleCollectionWithItem($force_pk=false, $force_fk=false) {
        $collection = new \Ubaldi\Component\ObjectCollection();

        if($force_pk) {
            $collection->setPrimary("propertie");
        }

        if($force_fk) {
            $collection->addIndex('propertie_fk');
        }


        $item = new \stdClass();
        $item->propertie='value_0';
        $item->propertie2='value2_0';
        $item->propertie_fk='value_fk_0';
        $item->propertie_group='propertie_group_1';
        $item->propertie_order=2;
        $item->propertie_join=1;
        $item->propertie_group2=1;
        $item->propertie_transform=20;
        $item->nested_item=$this->getSimpleTestableObjectInstance(1);

        $collection->add($item);

        $item = new \stdClass();
        $item->propertie='value_1';
        $item->propertie2='value2_1';
        $item->propertie_fk='value_fk_1';
        $item->propertie_group='propertie_group_1';
        $item->propertie_order=1;
        $item->propertie_join=1;
        $item->propertie_group2=1;
        $item->propertie_transform=12;
        $item->nested_item=$this->getSimpleTestableObjectInstance(30);

        $collection->add($item);

        $item = new \stdClass();
        $item->propertie='value_2';
        $item->propertie2='value2_2';
        $item->propertie_fk='value_fk_2';
        $item->propertie_group='propertie_group_2';
        $item->propertie_order=3;
        $item->propertie_join=2;
        $item->propertie_group2=1;
        $item->propertie_transform=11;
        $item->nested_item=$this->getSimpleTestableObjectInstance(5);

        $collection->add($item);

        $item = new \stdClass();
        $item->propertie='value_3';
        $item->propertie2='value2_3';
        $item->propertie_fk='value_fk_3';
        $item->propertie_group='propertie_group_3';
        $item->propertie_order=2;
        $item->propertie_join=3;
        $item->propertie_group2=2;
        $item->propertie_transform=8;
        $item->nested_item=$this->getSimpleTestableObjectInstance(10);

        $collection->add($item);


        return $collection;
    }

    /**
     * Test la création d'une instance
     */

    public function testNewInstance() {
        $this
            ->object(new \Ubaldi\Component\ObjectCollection())
            ->isInstanceOf('\Ubaldi\Component\ObjectCollection');
    }

    /**
     * Test les methodes d'itération sur la collection
     */
    public function testAddAndIterableCollection() {
        $collection = $this->factorySimpleCollectionWithItem();

        foreach($collection as $k => $sdtobject) {
            // permet de tester le getter de la clef
            // en meme temps que le getter de l'iterable de la collection
            $this
                ->string($sdtobject->propertie)
                ->isEqualTo("value_".$k);
        }

        // test le add multiple
        $collec = new \Ubaldi\Component\ObjectCollection();
        $collec -> addMultiple(array(
            new \stdClass(),new \stdClass(),new \stdClass()
        ));


        // test le countable
        $this
            ->integer(count($collec))
            ->isEqualTo(3);
    }

    /**
     * Test les methodes d'itération sur la collection avec une PK
     */
    public function testAddAndIterableCollectionWithPrimaryKey() {
        $collection = $this->factorySimpleCollectionWithItem(true);

        foreach($collection as $pk => $sdtobject) {
            // permet de tester le getter de la clef
            // en meme temps que le getter de l'iterable de la collection
            $this
                ->string($sdtobject->propertie)
                ->isEqualTo($pk);
        }
    }

    /**
     * Test les methodes d'itération sur la collection avec une PK et une FK
     */
    public function testAddAndIterableCollectionWithPrimaryKeyAndSecondaryKey() {
        $collection = $this->factorySimpleCollectionWithItem(true,true);

        foreach($collection as $pk => $sdtobject) {
            // permet de tester le getter de la clef
            // en meme temps que le getter de l'iterable de la collection
            $this
                ->string($sdtobject->propertie)
                ->isEqualTo($pk);
        }

        $collection = $this->factorySimpleCollectionWithItem(true,true);

        foreach($collection as $pk => $sdtobject) {
            // permet de tester le getter de la clef
            // en meme temps que le getter de l'iterable de la collection
            $this
                ->string($sdtobject->propertie)
                ->isEqualTo($pk);
        }
    }

    /**
     * Test l'ajout et la suppression d'un index secondaire
     */
    public function testSecondaryIndexManagement() {
        $collection = new \Ubaldi\Component\ObjectCollection();

        // test la création d'un index
        $collection->addIndex("fk_index");
        $this
            ->boolean($collection->hasIndex("fk_index"))
            ->isTrue();

        // test la suppression d'un index
        $collection->removeIndex("fk_index");
        $this
            ->boolean($collection->hasIndex("fk_index"))
            ->isFalse();


        // Test l'ajout et la suppression d'un index avec des données déja présentent dans la collection
        $collection = $this->factorySimpleCollectionWithItem();
        $collection->addIndex("propertie_fk");
        $this
            ->boolean($collection->hasIndex("propertie_fk"))
            ->isTrue();

    }

    public function testGetBy() {
        $collection = $this->factorySimpleCollectionWithItem(true,true);

        // test le get by primary key
       $this
            ->string($collection->get('value_1')->propertie_fk)
            ->isEqualTo('value_fk_1');

        // test le get by primary key sur une clef inconnue
        $this
            ->variable($collection->get('unknow_key'))
            ->isEqualTo(null);

        // test le getOneBy primary key
        $this
            ->string($collection->getOneBy(array('propertie' => 'value_1'))->propertie_fk)
            ->isEqualTo('value_fk_1');

        // test le getOneBy sur une clef inconnue
        $this
            ->variable($collection->getOneBy(array('propertie' => 'unknow_value')))
            ->isEqualTo(null);

        // test le getOneBy par index foreign key
        $this
            ->string($collection->getOneBy(array('propertie_fk' => 'value_fk_1'))->propertie_fk)
            ->isEqualTo('value_fk_1');

        // test le getOneBy full scan
        $this
            ->string($collection->getOneBy(array('propertie2' => 'value2_1'))->propertie_fk)
            ->isEqualTo('value_fk_1');


        // test le getOneBy full scan
        $nested_col = $collection->getBy(array('nested_item.nested_int' => array(30,10,40)));
        $this
            ->integer(count($nested_col))
            ->isEqualTo(2);

        $nested_col = $collection->getBy(array('nested_item.nested_int' => 5));

        $this
            ->integer(count($nested_col))
            ->isEqualTo(1);

        // test une collection vide (where inconnu)
        $unknow_coll = $collection->getBy(array('unknow_key' => 'unknow_value'));
        $this
            ->integer(count($unknow_coll))
            ->isEqualTo(0);

        // test avec condition multiple (AND)
        $multiple_coll = $collection->getBy(array(
            'propertie' => 'value_3',
            'propertie2' => 'value2_3'
        ));
        $this
            ->integer(count($multiple_coll))
            ->isEqualTo(1)
            -> string($multiple_coll -> current() -> propertie2)
            -> isEqualTo("value2_3");


        // test getByCallback (AND)
        $result = $collection->getByCallback(function($item) {
            return ($item -> propertie == "value_3") || ($item -> propertie2 == "value2_1");
        }) -> orderBy(array("propertie" => \Ubaldi\Component\ObjectCollection::SORT_ASC));
        $this
            ->integer(count($result))
            ->isEqualTo(2)
            -> string($result -> current() -> propertie)
            -> isEqualTo("value_1");
        $result -> next();
        $this
            -> string($result -> current() -> propertie2)
            -> isEqualTo("value2_3");

        // test avec condition multiple (AND)
        $multiple_coll2 = $collection->getBy(array(
            'propertie_fk' => array('value_fk_1','value_fk_2')
        ))
            -> orderBy(array("propertie_fk" => \Ubaldi\Component\ObjectCollection::SORT_DESC))
            -> getColumn("propertie_fk");
        $this
            ->integer(count($multiple_coll2))
            ->isEqualTo(2)
            -> string(implode(",",$multiple_coll2))
            -> isEqualTo("value_fk_2,value_fk_1");

    }

    public function testGetColumn() {
        $collection = $this->factorySimpleCollectionWithItem(true,true);

        // test get column sur PK
        $pk_columns = $collection->getColumn('propertie');

        $this
            ->string($pk_columns[0])
            ->isEqualTo('value_0')
            ->string($pk_columns[2])
            ->isEqualTo('value_2');


        // test get column sur FK
        $fk_columns = $collection->getColumn('propertie_fk');
        $this
            ->string($fk_columns[0])
            ->isEqualTo('value_fk_0')
            ->string($fk_columns[2])
            ->isEqualTo('value_fk_2');

        // test get column en full scann
        $full_scan_columns = $collection->getColumn('propertie2');
        $this
            ->string($full_scan_columns[0])
            ->isEqualTo('value2_0')
            ->string($full_scan_columns[2])
            ->isEqualTo('value2_2');


        // test get column en full scan nested item
        $fk_columns = $collection->getColumn('nested_item.nested_int');
        $this
            ->integer($fk_columns[0])
            ->isEqualTo(1)
            ->integer($fk_columns[1])
            ->isEqualTo(30)
            ->integer($fk_columns[2])
            ->isEqualTo(5)
            ->integer($fk_columns[3])
            ->isEqualTo(10);

        // test get column sur colonnue inconnue
        $fk_columns = $collection->getColumn('unknow_key');
        $this
            ->array($fk_columns)
            ->isEmpty();

    }

    public function testGroupBy() {
        $collection = $this->factorySimpleCollectionWithItem(true,true);

        // on group par "propertie_group"
        $grouped_by_collection = $collection->groupBy("propertie_group");

        // test si on à bien que 2 elements dans la collection apres groupe
        $this
            ->integer(count($grouped_by_collection))
            ->isEqualTo(3);


        // on test les deux valeures attendues
        $this
            ->string($grouped_by_collection->current()->propertie_group)
            ->isEqualTo('propertie_group_1');

        $grouped_by_collection->next();

        $this
            ->string($grouped_by_collection->current()->propertie_group)
            ->isEqualTo('propertie_group_2');

        // test group by sur colonnue inconnue
        $collection = $this->factorySimpleCollectionWithItem(true,true);
        $grouped_by_collection = $collection->groupBy("unknowxxxxxxxxxxxxx_propertie_group");
        $this
            ->integer(count($grouped_by_collection))
            ->isEqualTo(0);


        // on group par "propertie_group"
        $grouped_by_collection = $collection
            -> orderBy(array("propertie_transform" => \Ubaldi\Component\ObjectCollection::SORT_ASC))
            ->groupBy("propertie_group2",array(
                "transform_average" => array("propertie_transform" , \Ubaldi\Component\ObjectCollection::TRANSFORM_AVG),
                "transform_sum" => array("propertie_transform" , \Ubaldi\Component\ObjectCollection::TRANSFORM_SUM),
                "transform_count" => array("propertie_transform" , \Ubaldi\Component\ObjectCollection::TRANSFORM_COUNT),
                "transform_concat" => array("propertie_transform" , \Ubaldi\Component\ObjectCollection::TRANSFORM_GROUP_CONCAT),
                "transform_values" => array("propertie_transform" , \Ubaldi\Component\ObjectCollection::TRANSFORM_VALUES),
                "transform_collection" => array("propertie_transform" , \Ubaldi\Component\ObjectCollection::TRANSFORM_COLLECTION)
            ))
            -> orderBy(array("transform_count" => \Ubaldi\Component\ObjectCollection::SORT_DESC));

        $this
            ->integer(count($grouped_by_collection))
            ->isEqualTo(2);

        $current = $grouped_by_collection -> current();
        $this
            ->integer($current -> transform_count)
            ->isEqualTo(3);
        $this
            ->integer($current -> transform_sum)
            ->isEqualTo(20+11+12);
        $this
            ->float((float)$current -> transform_average)
            ->isEqualTo((20+11+12)/3);
        $this
            ->string($current -> transform_concat)
            ->isEqualTo("11,12,20");
        $this
            ->array($current -> transform_values)
            ->isEqualTo(array(11,12,20));
        $this
            ->object($current -> transform_collection)
            ->isInstanceOf("Ubaldi\\Component\\ObjectCollection")
            -> integer(count($current -> transform_collection))
            -> isEqualTo(3)
            ->array($current -> transform_collection -> getColumn("propertie_transform"))
            -> isEqualTo(array(11,12,20));


        $grouped_by_collection -> next();
        $current = $grouped_by_collection -> current();
        $this
            -> integer($current -> transform_count)
            -> isEqualTo(1);
        $this
            -> integer($current -> transform_sum)
            -> isEqualTo(8);
        $this
            -> float((float)$current -> transform_average)
            -> isEqualTo(8);


    }

    public function testOrderBy() {
        $collection = $this->factorySimpleCollectionWithItem(true,true);

        // on tri par "propertie_order" ASC
        $ordonned_by_collection = $collection->orderBy(array("propertie_order" => \Ubaldi\Component\ObjectCollection::SORT_ASC));

        $this
            ->integer($ordonned_by_collection->current()->propertie_order)
            ->isEqualTo(1);
        $ordonned_by_collection->next();
        $this
            ->integer($ordonned_by_collection->current()->propertie_order)
            ->isEqualTo(2);
        $ordonned_by_collection->next();
        $this
            ->integer($ordonned_by_collection->current()->propertie_order)
            ->isEqualTo(2);
        $ordonned_by_collection->next();
        $this
            ->integer($ordonned_by_collection->current()->propertie_order)
            ->isEqualTo(3);

        // on tri par "propertie_order" DESC
        $ordonned_by_collection = $collection->orderBy(array("propertie_order" => \Ubaldi\Component\ObjectCollection::SORT_DESC));

        $this
            ->integer($ordonned_by_collection->current()->propertie_order)
            ->isEqualTo(3);
        $ordonned_by_collection->next();
        $this
            ->integer($ordonned_by_collection->current()->propertie_order)
            ->isEqualTo(2);
        $ordonned_by_collection->next();
        $this
            ->integer($ordonned_by_collection->current()->propertie_order)
            ->isEqualTo(2);
        $ordonned_by_collection->next();
        $this
            ->integer($ordonned_by_collection->current()->propertie_order)
            ->isEqualTo(1);

        // on tri par "nested_item.nested_int" ASC
        $ordonned_by_collection = $collection->orderBy(array("nested_item.nested_int" => \Ubaldi\Component\ObjectCollection::SORT_ASC));

        $this
            ->integer($ordonned_by_collection->current()->nested_item->nested_int)
            ->isEqualTo(1);
        $ordonned_by_collection->next();
        $this
            ->integer($ordonned_by_collection->current()->nested_item->nested_int)
            ->isEqualTo(5);
        $ordonned_by_collection->next();
        $this
            ->integer($ordonned_by_collection->current()->nested_item->nested_int)
            ->isEqualTo(10);
        $ordonned_by_collection->next();
        $this
            ->integer($ordonned_by_collection->current()->nested_item->nested_int)
            ->isEqualTo(30);

        // on tri par "nested_item.nested_int" DESC
        $ordonned_by_collection = $collection->orderBy(array("nested_item.nested_int" => \Ubaldi\Component\ObjectCollection::SORT_DESC));

        $this
            ->integer($ordonned_by_collection->current()->nested_item->nested_int)
            ->isEqualTo(30);
        $ordonned_by_collection->next();
        $this
            ->integer($ordonned_by_collection->current()->nested_item->nested_int)
            ->isEqualTo(10);
        $ordonned_by_collection->next();
        $this
            ->integer($ordonned_by_collection->current()->nested_item->nested_int)
            ->isEqualTo(5);
        $ordonned_by_collection->next();
        $this
            ->integer($ordonned_by_collection->current()->nested_item->nested_int)
            ->isEqualTo(1);


        // on tri par "nested_item.nested_int" DESC
        $ordonned_by_collection = $collection->orderByCallback(function($a,$b) {
            return ($a -> nested_item->nested_int - $b -> nested_item->nested_int);
        });

        $this
            ->integer($ordonned_by_collection->current()->nested_item->nested_int)
            ->isEqualTo(1);
        $ordonned_by_collection->next();
        $this
            ->integer($ordonned_by_collection->current()->nested_item->nested_int)
            ->isEqualTo(5);
        $ordonned_by_collection->next();
        $this
            ->integer($ordonned_by_collection->current()->nested_item->nested_int)
            ->isEqualTo(10);
        $ordonned_by_collection->next();
        $this
            ->integer($ordonned_by_collection->current()->nested_item->nested_int)
            ->isEqualTo(30);



        // test orderBy sur colonnue inconnue
        $collection = $this->factorySimpleCollectionWithItem(true,true);
        $grouped_by_collection = $collection->orderBy(array("unknowxxxxxxxxxxxxx_propertie_group" => \Ubaldi\Component\ObjectCollection::SORT_DESC));
        $this
            ->integer(count($grouped_by_collection))
            ->isEqualTo(4);
    }

    protected function factoryJoinableCollection() {
        $join_collection = new \Ubaldi\Component\ObjectCollection();
        $item = new \stdClass();
        $item->propertie='joinvalue_0';
        $item->propertie_join=1;
        $item->joined_string_test="joined_test_string_1";
        $join_collection->add($item);

        $item = new \stdClass();
        $item->propertie='joinvalue_1';
        $item->propertie_join=1;
        $item->joined_string_test="joined_test_string_2";
        $join_collection->add($item);

        $item = new \stdClass();
        $item->propertie='joinvalue_2';
        $item->propertie_join=2;
        $item->joined_string_test="joined_test_string_3";
        $join_collection->add($item);

        return $join_collection;
    }

    public function testLeftJoin() {
    // simple join sans préciser de column de destination
    // => les objets seront mergés entre eux
    // => les objetst ne seront pas clonés (original modifié)
        $original_collection = $this->factorySimpleCollectionWithItem(true,true);
        $join_collection = $this->factoryJoinableCollection();
        $merged_collection = $original_collection->leftJoin($join_collection,array('propertie_join' => 'propertie_join'));

        $value_zero_item = $merged_collection->get('value_0');
        $this
            // test une propriété qui proviens de la collection d'origine
            ->integer($value_zero_item->propertie_order)
            ->isEqualTo('2')

            // test une propriété qui proviens de la collection joined
            ->string($value_zero_item->joined_string_test)
            ->isEqualTo('joined_test_string_2')

            // test que l'objet d'origine à bien été mergé
            ->string($original_collection->get('value_0')->joined_string_test)
            ->isEqualTo('joined_test_string_2');

    // simple join sans préciser de column de destination
    // => les objets seront clonés
    // => les objets d'origine ne seront PAS modifiés
    // => les objets d'arrivé seront un merge des propriétés de B dans A

        $original_collection = $this->factorySimpleCollectionWithItem(true,true);
        $join_collection = $this->factoryJoinableCollection();
        $merged_collection = $original_collection->leftJoin(
            $join_collection,array('propertie_join' => 'propertie_join'),
            null,true,true
        );

        // objets mergés => la pk à changé pour celle d'arrivé du join
        $value_zero_item = $merged_collection->get('joinvalue_0');

        $this
            // test une propriété qui proviens de la collection d'origine
            ->integer($value_zero_item->propertie_order)
            ->isEqualTo(1)

            // test une propriété qui proviens de la collection joined
            ->string($value_zero_item->joined_string_test)
            ->isEqualTo('joined_test_string_1')

            // test une propriété mergée depuis l'objet d'origine
            ->string($value_zero_item->propertie2)
            ->isEqualTo('value2_1')

            // test que l'objet d'origine n'a pas été touché
            ->variable($original_collection->get('value_0')->joined_string_test)
            ->isEqualTo(null);


    // simple join sur une colonne
    // l'objet de la seconde collection sera rappatrié dans la colonne spécifiée
        $original_collection = $this->factorySimpleCollectionWithItem(true,true);
        $join_collection = $this->factoryJoinableCollection();
        $merged_collection = $original_collection->leftJoin(
            $join_collection,
            array('propertie_join' => 'propertie_join'),
            'joined_item'
        );

        $value_zero_item = $merged_collection->get('value_0');
        $this
            // test une propriété qui proviens de la collection d'origine
            ->integer($value_zero_item->propertie_order)
            ->isEqualTo('2')

            // test que l'objet joined à bien été inséré dans la colonne demandé
            ->string($value_zero_item->joined_item->joined_string_test)
            ->isEqualTo('joined_test_string_2')

            // test que l'objet d'origine n'a PAS été mergé
            ->variable($original_collection->get('value_0')->joined_string_test)
            ->isEqualTo(null);

    // multiple join sur une colonne
    // les multiple objets de la seconde collection seront rappatriés dans la colonne spécifiée
        $original_collection = $this->factorySimpleCollectionWithItem(true,true);
        $join_collection = $this->factoryJoinableCollection();
        $merged_collection = $original_collection->leftJoin(
            $join_collection,
            array('propertie_join' => 'propertie_join'),
            'joined_item',
            false
        );

        $value_zero_item = $merged_collection->get('value_0');
        $this
            // test une propriété qui proviens de la collection d'origine
            ->integer($value_zero_item->propertie_order)
            ->isEqualTo('2')

            // test que les objets joined ont bien été insérés dans la colonne demandé
            ->string($value_zero_item->joined_item[0]->joined_string_test)
            ->isEqualTo('joined_test_string_1')
            ->string($value_zero_item->joined_item[1]->joined_string_test)
            ->isEqualTo('joined_test_string_2')

            // test que l'objet d'origine n'a PAS été mergé
            ->variable($original_collection->get('value_0')->joined_string_test)
            ->isEqualTo(null);
    }
}