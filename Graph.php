<?php

namespace AppBundle\Recommendation;

use AppBundle\Recommendation\Collection\EdgeCollection;
use AppBundle\Recommendation\Collection\NodeCollection;
use AppBundle\Recommendation\Unit\Edge;
use AppBundle\Recommendation\Unit\Node;
use AppBundle\Recommendation\Unit\Unit;
use function foo\func;
use function Symfony\Component\VarDumper\Dumper\esc;

class Graph
{

    private $uniqueVal = PHP_INT_MAX;

    private $lookup = [];

    private $nodes = [];

    private $edges = [];

    private $nodeCollections = [];

    private $edgeCollections = [];

    public function __construct()
    {
    }

    /**
     * @param $uniqueId
     *
     * @return mixed
     */
    public function unit($uniqueId)
    {
        return $this->lookup[$uniqueId];
    }

    /**
     * @return int
     */
    public function nodeCount()
    {
        return count($this->nodes);
    }

    /**
     * @return int
     */
    public function edgeCount()
    {
        return count($this->edges);
    }

    /**
     * @param $entity
     * @param $properties
     *
     * @return Node
     */
    public function createNode($entity, $properties)
    {
        return $this->_createNode($entity, $properties, base_convert($this->uniqueVal--, 10, 16));
    }

    /**
     * @param $entity
     * @param $properties
     * @param $uniqueId
     *
     * @return Node
     */
    private function _createNode($entity, $properties, $uniqueId)
    {
        return $this->_addNode(new Node($entity, $properties, $uniqueId));
    }

    /**
     * @param Node $node
     *
     * @return Node
     */
    private function _addNode(Node $node)
    {
        $this->nodes[] = $node;
        $this->lookup[$node->getUniqueId()] = $node;
        $nodeList = $this->getNodes($node->getEntity());

        return $nodeList->add($node);
    }

    /**
     * @param $entity
     *
     * @return NodeCollection
     */
    public function getNodes($entity)
    {
        return array_key_exists($entity, $this->nodeCollections) ?  $this->nodeCollections[$entity] : ($this->nodeCollections[$entity] = new NodeCollection($entity));
    }

    /**
     * @param $entity
     *
     * @return EdgeCollection
     */
    public function getEdges($entity)
    {
        return array_key_exists($entity, $this->edgeCollections) ?  $this->edgeCollections[$entity] : ($this->edgeCollections[$entity] = new EdgeCollection($entity));
    }

    /**
     * @param Node $node
     * @param $traced
     *
     * @return array
     */
    public function getPath(Node $node, $traced)
    {
        $path = $traced[$node->getUniqueId()];

        while ($path[0] instanceof Edge) {
            $edge = $path[0];
            $node = $edge->getOppositeNode($path[1]);
            $path = array_merge($traced[$node->getUniqueId()], $path);
        }

        return $path;
    }

    /**
     * @param Node $node
     * @param $compare
     * @param array $opts
     *
     * @return array
     */
    public function getClosest(Node $node, $compare, $opts = [])
    {
        $count = 0;
        $minDepth = 0;
        $maxDepth = 0;
        $direction = 0;

        if (array_key_exists('count', $opts)) {
            $count = $opts['count'];
        }

        if (array_key_exists('minDepth', $opts)) {
            $minDepth = $opts['minDepth'];
        }

        if (array_key_exists('direction', $opts)) {
            $direction = $opts['direction'];
        }

        if (array_key_exists('maxDepth', $opts)) {
            $maxDepth = $opts['maxDepth'];
        }

        return $this->search($node, $compare, $count, $direction, $minDepth, $maxDepth);
    }

    /**
     * @param Node $node
     * @param $passCondition
     * @param int $count
     * @param int $direction
     * @param int $minDepth
     * @param int $maxDepth
     *
     * @return array
     */
    public function search(Node $node, $passCondition, $count = 0, $direction = 0, $minDepth = 0, $maxDepth = 0)
    {
        $passCondition = is_callable($passCondition) ? $passCondition : function(Node $node) {
            return true;
        };

        $nodePath = [];
        $nodePath[$node->getUniqueId()] = [$node];

        $depthMap = [];
        $depthMap[0] = [$node];

        $depthList = [0];

        $found = [];


        while (count($depthList)) {
            $curDepth = array_shift($depthList);
            $queue = $depthMap[$curDepth];

            while (count($queue)) {
                $path = $this->readNode(array_shift($queue), $curDepth, $direction, $maxDepth, $minDepth, $nodePath, $depthMap, $passCondition , $depthList);

                $path && ($found[] = $path);

                if ($count && count($found) >= $count) {
                    return $found;
                }
            }
        }

        return $found;
    }


    private function orderedSetInsert(&$arr, $val)
    {
        $n = count($arr);
        $i = $n >> 1;

        while ($n) {
            $n >>= 1;

            if (array_key_exists($i, $arr)) {
                if ($arr[$i] === $val) {
                    return $arr;
                } elseif ($arr[$i] < $val) {
                    $i+= $n;
                }
            } else {
                $i -= $n;
            }
        }

        $offset = 0;

        if (array_key_exists($i, $arr)) {
            $offset = $i + ($arr[$i] < $val);
        }
        return array_splice($arr , $offset, 0, $val);
    }

    private function readNode(Node $node, $curDepth, $direction, $maxDepth, $minDepth, &$nodePath, &$depthMap, $passCondition, &$depthList)
    {
        $edges = ($direction === 0 ? $node->getEdges() : $direction > 0 ? $node->getOutputEdges() : $node->getInputEdges());

        /** @var Edge $edge */
        foreach ($edges as $edge) {
            $depth = $curDepth + $edge->getDistance();

            if ($maxDepth && $depth > $maxDepth) {
                continue;
            }

            $tNode = $edge->getOppositeNode($node);

            if(!array_key_exists($tNode->getUniqueId(), $nodePath)) {
                $nodePath[$tNode->getUniqueId()] = [$edge, $tNode];
                $this->enqueue($depthMap, $tNode, $depth, $depthList);
            }
        }

        if ($curDepth >= $minDepth && $passCondition($node)) {
            return new Path($this->getPath($node, $nodePath));
        }

        return false;
    }


    private function enqueue(&$depthMap, Node $node, $depth, &$depthList)
    {

        if (array_key_exists($depth, $depthMap)) {
            $depthMap[$depth][] = $node;
        } else {
            $depthMap[$depth] = [$node];
        }

        $this->orderedSetInsert($depthList, $depth);
    }

    /**
     * @param $entity
     * @param $properties
     *
     * @return Edge
     */
    public function createEdge($entity, $properties = [])
    {
        return $this->_createEdge($entity, $properties, base_convert($this->uniqueVal--, 10, 16));
    }

    /**
     * @param $entity
     * @param $properties
     * @param $uniqueId
     *
     * @return Edge
     */
    private function _createEdge($entity, $properties, $uniqueId)
    {
        return $this->_addEdge(new Edge($entity, $properties, $uniqueId));
    }

    /**
     * @param Edge $edge
     *
     * @return Edge
     */
    private function _addEdge(Edge $edge)
    {
        $this->edges[] = $edge;
        $this->lookup[$edge->getUniqueId()] = $edge;
        $edgeList = $this->getEdges($edge->getEntity());
        return $edgeList->add($edge);
    }

    /**
     * @param $fromNode
     * @param $toNode
     * @param $direction
     *
     * @return Path|array
     */
    public function trace ($fromNode, $toNode, $direction)
    {
        $passCondition = function ($node) use ($toNode) {
            return $node === $toNode;
        };

        $searchRes = $this->search($fromNode, $passCondition, 1, $direction);
        return count($searchRes) ? $searchRes : new Path([]);
    }

    /**
     * @return string
     */
    public function toJSON()
    {
        $nodeCollections = $this->nodeCollections;

        $nc = array_map(function (NodeCollection $nodeCollection) {
            return $nodeCollection->jsonSerialize();
        }, $nodeCollections);

        $edgeCollections = $this->edgeCollections;

        $ec = array_map(function (EdgeCollection $edgeCollection) {
            return $edgeCollection->jsonSerialize();
        }, $edgeCollections);

        $nodes = array_map(function (Node $node) {
            return $node->jsonSerialize();
        }, $this->nodes);


        $edges = array_map(function (Edge $edge) {
            return $edge->jsonSerialize();
        }, $this->edges);

        return json_encode([
          'nc' => $nc,
          'ec' => $ec,
          'n' => $nodes,
          'e' => $edges,
        ]);
    }

}
