<?php

namespace App\Lib;

class Arr
{
    // 将验证的数组转换为html属性，以便于twig模板使用
    public static function transValtoAttr($arr)
    {
      $resultArr = [];
      $resultArr['required'] = false;
      foreach($arr as $itemKey => $itemVal) {
        switch ($itemKey) {
          case 'LessThan':
            $resultArr['numValuemax'] = $itemVal;
            break;
          case 'GreaterThan':
            $resultArr['numValuemin'] = $itemVal;
            break;
          case 'step':
            $resultArr['step'] = $itemVal;
            break;
          case 'required':
            $resultArr['required'] = $itemVal;
            break;
        }
      }

      return $resultArr;
    }
}
