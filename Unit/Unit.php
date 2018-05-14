<?php

namespace AppBundle\Recommendation\Unit;

class Unit implements \JsonSerializable
{

    /**
     * @var string
     */
    private $entity;

    /**
     * @var array
     */
    private $properties;

    /**
     * @var string
     */
    private $uniqueId;


    /**
     * Unit constructor.
     *
     * @param $entity
     * @param $properties
     * @param $uniqueId
     */
    public function __construct($entity, $properties, $uniqueId)
    {
        $this->entity = $entity;
        $this->uniqueId = $uniqueId;
        $this->properties = $properties;
    }

    /**
     * @param array $properties
     *
     * @return $this
     */
    public function load($properties = [])
    {
        $p = [];

        foreach ($properties as $key => $property) {
            $p[$key] = $property;
        }

        $this->properties = $p;

        return $this;
    }

    /**
     * @param $property
     * @param $value
     *
     * @return $this
     */
    public function set($property, $value)
    {
        $this->properties[$property] = $value;

        return $this;
    }

    /**
     * @param $property
     *
     * @return $this
     */
    public function remove($property)
    {
        unset($this->properties[$property]);

        return $this;
    }

    /**
     * @param $property
     *
     * @return bool
     */
    public function has($property)
    {
        return array_key_exists($property, $this->properties);
    }

    public function get($property)
    {
        return $this->properties[$property];
    }

    /**
     * @return string
     */
    public function getUniqueId()
    {
        return $this->uniqueId;
    }

    /**
     * @return string
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @return array
     */
    public function getProperties()
    {
        return $this->properties;
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
        return [
          $this->entity,
          $this->properties,
          $this->uniqueId,
        ];
    }
}