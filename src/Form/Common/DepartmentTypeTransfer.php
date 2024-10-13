<?php

namespace App\Form\Common;

use Symfony\Component\Form\DataTransformerInterface;
use App\Entity\Organization\Department;
use Doctrine\ORM\EntityManagerInterface;

class DepartmentTypeTransfer implements DataTransformerInterface
{
  public function __construct(
    private EntityManagerInterface $entityManager,
  ) {
  }

  /**
   * 将模型数据（Department 实体对象）转换为表单数据。
   * 如果传入的是一个 Department 实体对象，则直接返回；如果是 ID（字符串），则查找对应的 Department 实体。
   *
   * @param mixed $department
   * @return Department|null
   */
  public function transform(mixed $department): ?Department
  {
    // 如果传入的对象是 Department 实体，直接返回
    if ($department instanceof Department) {
      return $department;
    }

    // 如果传入的 ID 是字符串，则尝试查找对应的 Department 实体
    if (is_string($department)) {
      return $this->entityManager->getRepository(Department::class)->find($department);
    }

    // 如果不符合上述情况，返回 null
    return null;
  }

  /**
   * 将表单数据（ID 或 Department 实体对象）转换为模型数据。
   * 如果传入的是 ID，则查找对应的 Department 实体并返回。
   *
   * @param mixed $id
   * @return Department|null
   */
  public function reverseTransform(mixed $id): ?Department
  {
    // 如果 ID 为空，直接返回 null
    if (!$id) {
      return null;
    }

    // 如果传入的是 Department 实体对象，直接返回
    if ($id instanceof Department) {
      return $id;
    }

    // 如果传入的是字符串 ID，查找对应的 Department 实体对象
    if (is_string($id)) {
      return $this->entityManager->getRepository(Department::class)->find($id);
    }

    // 不符合上述条件的情况，返回 null
    return null;
  }
}
