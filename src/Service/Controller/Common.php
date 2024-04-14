<?php

namespace App\Service\Controller;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class Common
{
    private $params;

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
    }

    public function p($key)
    {
        return $this->params->get($key);
    }
}
