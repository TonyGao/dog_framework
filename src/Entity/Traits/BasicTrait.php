<?php

namespace App\Entity\Traits;

trait BasicTrait
{
  use SoftDeleteableTrait;
  use TimestampableTrait;
  use BlameableTrait;

  private $dynamicProperties = [];

  // 动态设置属性
//   public function __set($name, $value) {
//       $this->dynamicProperties[$name] = $value;
//   }

  // 动态获取属性
  public function __get($name) {
      return $this->dynamicProperties[$name] ?? null;
  }

  // 动态设置属性的方法（避免使用魔术方法）
  public function setDynamicProperty($name, $value) {
      $this->dynamicProperties[$name] = $value;
      return $this;
  }

  // 检查是否存在动态属性
  public function __isset($name) {
      return isset($this->dynamicProperties[$name]);
  }

  // 移除动态属性
  public function __unset($name) {
      unset($this->dynamicProperties[$name]);
  }

  // 可以添加获取所有动态属性的方法
  public function getDynamicProperties(): array {
      return $this->dynamicProperties;
  }
}