<?php

namespace AppBundle\Recommendation\Unit;

class Node extends Unit
{

    /**
     * @var array
     */
    private $edges = [];

    /**
     * @var array
     */
    private $inputEdges = [];

    /**
     * @var array
     */
    private $outputEdges = [];

    public function __construct($entity, $properties, $uniqueId)
    {
        parent::__construct($entity, $properties, $uniqueId);
    }

    /**
     * @param Edge $edge
     */
    public function addInputEdge(Edge $edge)
    {
        $this->inputEdges[] = $edge;
    }

    /**
     * @param Edge $edge
     */
    public function addOutputEdge(Edge $edge)
    {
        $this->outputEdges[] = $edge;
    }

    /**
     * @param Edge $edge
     */
    public function addEdge(Edge $edge)
    {
        $this->edges[] = $edge;
    }

    /**
     * @return array
     */
    public function getEdges()
    {
        return $this->edges;
    }

    /**
     * @return array
     */
    public function getInputEdges()
    {
        return $this->inputEdges;
    }

    /**
     * @return array
     */
    public function getOutputEdges()
    {
        return $this->outputEdges;
    }

    public function unlink()
    {

        /** @var Edge $edge */
        foreach ($this->edges as $edge) {
            $edge->unlink();
        }

        return true;
    }
}