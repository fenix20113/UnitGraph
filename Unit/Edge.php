<?php

namespace AppBundle\Recommendation\Unit;

class Edge extends Unit
{

    /**
     * @var \AppBundle\Recommendation\Unit\Node
     */
    private $inputNode;

    /**
     * @var \AppBundle\Recommendation\Unit\Node
     */
    private $outputNode;

    /**
     * @var bool
     */
    private $duplex;

    /**
     * @var int
     */
    private $distance = 1;

    /**
     * Edge constructor.
     *
     * @param $entity
     * @param $properties
     * @param $uniqueId
     */
    public function __construct($entity, $properties, $uniqueId)
    {
        parent::__construct($entity, $properties, $uniqueId);
    }

    /**
     * @param $node
     * @param $direction
     *
     * @return bool
     */
    private function linkTo(Node $node, $direction)
    {
        if ($direction <= 0) {
            $node->addInputEdge($this);
        }

        if ($direction >= 0) {
            $node->addOutputEdge($this);
        }

        $node->addEdge($this);

        return true;
    }

    /**
     * @param Node $inputNode
     * @param Node $outputNode
     * @param $duplex
     *
     * @return $this
     */
    public function link(Node $inputNode, Node $outputNode, $duplex = false)
    {
        $this->unlink();

        $this->inputNode = $inputNode;
        $this->outputNode = $outputNode;
        $this->duplex = (bool)$duplex;

        if ($duplex) {
            $this->linkTo($inputNode, 0);
            $this->linkTo($outputNode, 0);

            return $this;
        }

        $this->linkTo($inputNode, 1);
        $this->linkTo($outputNode, -1);

        return $this;
    }

    /**
     * @param $v
     *
     * @return $this
     */
    public function setDistance($v)
    {
        $this->distance = abs(floatval($v) ?: 0);

        return $this;
    }

    /**
     * @param $v
     *
     * @return $this
     */
    public function setWeight($v)
    {
        $this->distance = 1 / abs(floatval($v) ?: 0);

        return $this;
    }

    /**
     * @param Node $node
     *
     * @return Node|bool
     */
    public function getOppositeNode(Node $node)
    {
        if ($this->inputNode === $node) {
            return $this->outputNode;
        } elseif ($this->outputNode === $node) {
            return $this->inputNode;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function unlink()
    {
        $pos = null;
        $inputNode = $this->inputNode;
        $outputNode = $this->outputNode;

        if (!($inputNode && $outputNode)) {
            return false;
        }

        ($pos = in_array($this, $inputNode->getEdges()) && array_splice($inputNode->getEdges(), $pos, 1));
        ($pos = in_array($this, $outputNode->getEdges()) && array_splice($outputNode->getEdges(), $pos, 1));
        ($pos = in_array($this, $inputNode->getOutputEdges()) && array_splice($inputNode->getOutputEdges(), $pos, 1));
        ($pos = in_array($this, $outputNode->getInputEdges()) && array_splice($outputNode->getInputEdges(), $pos, 1));

        if ($this->duplex) {
            ($pos = in_array($this, $inputNode->getInputEdges()) && array_splice($inputNode->getInputEdges(), $pos, 1));
            ($pos = in_array($this, $outputNode->getOutputEdges()) && array_splice($outputNode->getOutputEdges(), $pos, 1));
        }

        $this->inputNode = null;
        $this->outputNode = null;

        $this->duplex = false;

        return true;
    }

    /**
     * @return int
     */
    public function getDistance()
    {
        return $this->distance;
    }


    public function jsonSerialize()
    {
        return array_merge(parent::jsonSerialize(), [
          $this->inputNode->getUniqueId(),
          $this->outputNode->getUniqueId(),
          ($this->duplex | 0),
          $this->distance,
        ]);
    }
}