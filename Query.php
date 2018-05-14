<?php

namespace AppBundle\Recommendation;

use AppBundle\Recommendation\Unit\Unit;

class Query
{
    private $units;

    public function __construct($units)
    {
        $this->units = $units;
    }

    /**
     * @param $filterArray
     * @param $exclude
     *
     * @return Query
     * @throws \Exception
     */
    private function __filter($filterArray, $exclude)
    {
        $exclude = (bool) $exclude;

        foreach ($filterArray as &$filters) {
            if (!is_array($filters) || $filters === null) {
                $filters = [];
            }
        }

        if (!count($filterArray)) {
            $filterArray = [[]];
        }

        $data = $this->units;

        foreach ($filterArray as &$filters) {
            $filterData = [];

            foreach ($filters as $key => $value) {
                $filter = explode('__', $key);
                if (count($filter) < 2) {
                    $filter[] = 'is';
                }
                $filterType = array_pop($filter);

                if (!method_exists(Comparators::class, $filterType)) {
                    throw new \InvalidArgumentException('Filter type "' . $filterType . '" not supported.');
                }

                $filterData[] = [Comparators::class . '::' . $filterType, $filter, $filters[$key]];
            }

            $filters = $filterData;
        }

        $tmp = array_fill(0, 9, null);
        $n = 0;

        try {
            /** @var Unit $unit */
            foreach ($data as $unit) {
                $datum = $unit->getProperties();
                $excludeCurrent = true;

                foreach ($filterArray as $filterData) {
                    if (!$excludeCurrent) {
                        continue;
                    }

                    $excludeCurrent = false;

                    foreach ($filterData as $tmpFilter) {

                        if ($excludeCurrent) {
                            continue;
                        }

                        $compareFn = $tmpFilter[0];
                        $d = $datum;
                        $key = $tmpFilter[1];

                        foreach ($key as $item) {
                            $d = $d[$item];
                        }
                        $val = $tmpFilter[2];
                        ($compareFn($d, $val) === $exclude) && ($excludeCurrent = true);
                    }


                    !$excludeCurrent && ($tmp[$n++] = $unit);
                }

            }
        } catch (\Exception $exception) {
            throw new \Exception('Nested field ' . implode('__', $key) . ' does not exist');
        }

        $tmp = array_slice($tmp, 0 , $n);

        return new Query($tmp);
     }

    /**
     * @return Query
     * @throws \Exception
     */
     public function filter()
     {
         $args = func_get_args();
         $args = is_array($args[0]) ? $args[0] : $args;

         return $this->__filter($args, false);
     }

     public function first()
     {
         return $this->units[0];
     }
}