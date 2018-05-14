<?php

namespace AppBundle\Recommendation\Collection;

use AppBundle\Recommendation\Query;
use AppBundle\Recommendation\Unit\Edge;
use AppBundle\Recommendation\Unit\Node;
use AppBundle\Recommendation\Unit\Unit;

class Collection implements \JsonSerializable
{

    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $units = [];

    /**
     * @var array
     */
    private $indices = [];

    /**
     * @var array
     */
    private $indicesList = [];

    /**
     * Collection constructor.
     *
     * @param $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getIndices()
    {
        return $this->indicesList;
    }


    /**
     * Specify data which should be serialized to JSON
     *
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return [$this->name, $this->indicesList];
    }

    /**
     * @param $field
     *
     * @return Collection
     */
    public function createIndex($field)
    {
        return $this->createIndices([$field]);
    }

    public function createIndices($fieldList)
    {
        $this->indicesList = array_merge($this->indicesList, $fieldList);
        $indices = $this->indices;
        $units = $this->units;

        foreach ($fieldList as $key => $item) {
            $lookup = ($indices[$item] = []);
            /** @var \AppBundle\Recommendation\Unit\Unit $unit */
            foreach ($units as $unit) {
                $id = $unit->get($item);
                $id && ($lookup[$id] = $unit);
            }
        }

        return $this;
    }

    public function add($unit)
    {
        if ($unit) {
            $this->units[] = $unit;

            $list = $this->indicesList;
            $indices = $this->indices;

            foreach ($list as $key => $item) {
                $lookup = $indices[$item];
                $id = $unit->get($item);
                $id && ($lookup[$id] = $unit);
            }
        }

        return $unit;
    }


    /**
     * @param Unit $unit
     *
     * @return Unit
     */
    public function remove(Unit $unit)
    {
        $pos = array_search($unit, $this->units);
        $pos !== false && array_splice($this->units, $pos, 1);

        $list = $this->indicesList;
        $indices = $this->indices;

        foreach ($list as $item) {
            $lookup = $indices[$item];
            $id = $unit->get($item);
            unset($lookup[$id]);
        }

        return $unit;
    }

    /**
     * @param $index
     * @param $id
     *
     * @return bool
     */
    public function find($index, $id)
    {
        if (!$id) {
            $id = $index;
            $index = $this->indicesList[0];
        }

        $lookup = $this->indices[$index];
        return $lookup && $lookup[$id];
    }

    /**
     * @param $index
     * @param $id
     *
     * @return bool
     */
    public function destroy ($index, $id)
    {
        if (!$id) {
            $id = $index;
            $index = $this->indicesList[0];
        }

        $lookup = $this->indices[$index];
        return $lookup && $this->remove($lookup[$id]);
    }


    public function query()
    {
        return new Query($this->units);
    }

}
