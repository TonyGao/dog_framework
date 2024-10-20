<?php

namespace App\Service\Platform;

use App\Service\BaseService;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ParameterService extends BaseService
{
    private $parameterBag;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->parameterBag = $parameterBag;
    }

    /**
     * 递归获取嵌套参数
     * 
     * @param string $path 用 `.` 分隔的路径，例如 "entity_gen_stuff.use_imports"
     * @return mixed
     */
    public function get(string $path)
    {
        $keys = explode('.', $path);
        $value = $this->parameterBag->all(); // 获取所有参数

        foreach ($keys as $key) {
            if (isset($value[$key])) {
                $value = $value[$key];
            } else {
                throw new \InvalidArgumentException(sprintf('Parameter "%s" does not exist.', $path));
            }
        }

        return $value;
    }
}