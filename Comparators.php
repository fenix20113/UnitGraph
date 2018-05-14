<?php

namespace AppBundle\Recommendation;

class Comparators
{

    public static function is($a, $b)
    {
        return $a === $b;
    }

    public static function not($a, $b)
    {
        return $a !== $b;
    }

    public static function gt($a, $b)
    {
        return $a > $b;
    }

    public static function lt($a, $b)
    {
        return $a < $b;
    }

    public static function gte($a, $b)
    {
        return $a >= $b;
    }

    public static function lte($a, $b)
    {
        return $a <= $b;
    }

    public static function ilike($a, $b)
    {
        return mb_strpos(mb_strtolower($a), mb_strtolower($b)) !== false;
    }

    public static function like($a, $b)
    {
        return mb_strpos($a, $b) !== false;
    }

    public static function in($a, $b)
    {
        return mb_strpos($b, $a) !== false;
    }

    public static function notIn($a, $b)
    {
        return mb_strpos($b, $a) === false;
    }
}
