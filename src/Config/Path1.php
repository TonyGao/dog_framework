<?php

namespace App\Config;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Class Status
 * @package App\Constants
 */
class Path1
{
    private $params;
    public static $p = [];

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
        $pathArr = $this->params->get('twig_path');
        foreach($pathArr as $k => $v) {
            self::$p[$k] = $v;
        }
    }
}
