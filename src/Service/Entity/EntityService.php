<?php

namespace App\Service\Entity;

use App\Service\FileResolver;
use App\Entity\Platform\Entity;
use App\Entity\Platform\EntityProperty;
use App\Entity\Platform\EntityPropertyGroup;
use App\Exception\EntityException;
use App\Lib\Str;
use App\Lib\Time;
use Nette\PhpGenerator\PhpFile;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\NamingStrategy;
use Doctrine\ORM\Mapping\DefaultNamingStrategy;

class EntityService
{
  private $entity;
  private $projectDir;
  private $filePath;
  private $class;
  private $file;
  private $em;
  private $fR;
  private $namespace;
  private $columnNames;
  private $namingStrategy;
  // EntityProperty
  private $eProperty;

  public function __construct(string $projectDir, EntityManagerInterface $em, FileResolver $fileResolver, $filePath = '')
  {
    $this->projectDir = $projectDir;
    $this->filePath = $filePath;
    $this->em = $em;
    $this->fR = $fileResolver;
    $this->namingStrategy = new DefaultNamingStrategy();
  }

  /**
   * 通过Entity模型的token加载EntityService
   */
  public function loadByToken($entityToken)
  {
    $enRepo = $this->em->getRepository(Entity::class);
    $entity = $enRepo->findOneBy(['token' => $entityToken]);
    if ($entity === null) {
      throw EntityException::noEntityTokenFound($entityToken);
    }
    $this->entity = $entity;
    $this->namespace = $entity->getFqn();
    $filePath = $this->fR->resolveFilePath($this->namespace);
    $this->setPath($filePath);

    $metadata = $this->em->getClassMetadata($this->namespace);
    $this->columnNames = $metadata->getColumnNames();
    return $this;
  }


  /**
   * 备份实体文件
   */
  public function backupEntity(): EntityService
  {
    // 备份目录
    $backupDir = $this->projectDir . '/var/backup/entity/';
    if (!is_dir($backupDir)) {
      mkdir($backupDir, 0777, true);
    }

    $backupFileName = basename($this->filePath, '.php') . str_replace('.', '', Time::curMicroSec()) . '.php';
    $backupFilePath = $backupDir . $backupFileName;
    $zipName = $backupFilePath . '.zip';
    copy($this->filePath, $backupFilePath);
    $this->fR->createZip($zipName, [$backupFilePath]);
    return $this;
  }

  /**
   * 通过路径加载php文件，返回实体类
   */
  public function loadEntity()
  {
    $file = PhpFile::fromCode(file_get_contents($this->filePath));
    $key = array_key_first($file->getClasses());
    $class = $file->getClasses()[$key];
    $this->file = $file;
    $this->class = $class;
    return $this;
  }

  /**
   * 为实体类添加属性，并添加相应的getter, setter
   * 下边为string类型的attribute示例
   * #[ORM\Column(type: 'string', length: 180)]
   */
  public function addProperty($property)
  {
    $pName = $property['name']['value'];
    $nullable = $property['nullable']['value'] === '1' ? true : false;
    $property['nullable']['value'] = $nullable;

    $unique = $property['unique']['value'] === '1' ? true : false;
    $property['unique']['value'] = $unique;

    // 检查属性是否已存在于数据库模型中以及EntityProperty中
    if ($this->isExisted($property)) {
      throw EntityException::alreadyExistsProperty($this->class, $pName);
    }

    $comment = $property['comment']['value'];
    if ($property['type']['value'] = 'string') {
      $finalType = 'string';

      $class = $this->class
        ->addProperty($pName)
        ->setVisibility('private')
        ->addComment($comment);

      $attributeArr = ['type' => 'string', 'length' => (int) $property['length']['value'], 'nullable' => $nullable];

      // 默认值
      if ($property['defaultValue']['value'] !== '') {
        $defaultValue = $property['defaultValue']['value'];
        $attributeArr['options'] = ['default' => $defaultValue];
        $this->class->setDefaultValue($pName, $defaultValue);
      }

      // 唯一性
      if ($unique) {
        $attributeArr['unique'] = $unique;
      }

      dump($attributeArr);

      $class->addAttribute('Doctrine\ORM\Mapping\Column', $attributeArr);
    }

    // 添加属性的 setter 方法
    $setterMethodName = 'set' . ucfirst($pName);
    $method = $this->class->addMethod($setterMethodName)
      ->setReturnType($this->namespace)
      ->addComment($comment . ' Setter')
      ->addComment('@return self')
      ->addBody('$this->' . $pName . ' = $' . $pName . ';')
      ->addBody('return $this;');

    $method->addParameter($pName);

    // 添加属性的 getter 方法
    $getterMethodName = 'get' . ucfirst($pName);
    $this->class->addMethod($getterMethodName)
      ->setReturnType($finalType)
      ->addComment($comment . ' Getter')
      ->addBody('return $this->' . $pName . ';');

    $this->insertEntityProperty($property);
    return $this;
  }

  /**
   * 将属性数据插件到数据库platform_entity_property表中
   */
  public function insertEntityProperty($property)
  {
    $groupId = $property['group']['value'];
    // 获取 EntityPropertyGroup 对象
    $groupRepo = $this->em->getRepository(EntityPropertyGroup::class);
    $group = $groupRepo->find($groupId);

    if ($group === null) {
      throw new \Exception("Group with ID '{$groupId}' not found.");
    }

    $prop = new EntityProperty();
    $prop->setToken(sha1(random_bytes(10)))
      ->setIsCustomized(true)
      ->setBusinessField(true)
      ->setPropertyName($property['name']['value'])
      ->setComment($property['comment']['value'])
      ->setType($property['type']['value'])
      ->setFieldName(Str::tableize($property['name']['value']))
      ->setNullable(true)
      ->setEntity($this->entity)
      ->setGroup($group);

    if ($property['type']['value'] === 'string') {
      $prop->setLength((int) $property['length']['value']);
    }

    $this->em->persist($prop);
  }

  public function isExisted($property)
  {
    // 判断属性的name(英文字段名)在模型中是否已经存在
    $propertyName = $property['name']['value'];
    $pName = $this->namingStrategy->propertyToColumnName($propertyName);
    $result = in_array($pName, $this->columnNames);

    // 判断中文名称是否在EntityProperty中已经存在
    $comment = $property['comment']['value'];
    $epRepo = $this->em->getRepository(EntityProperty::class);
    $ep = $epRepo->findOneBy(['comment' => $comment]);
    return $ep !== null && $result;
  }

  /**
   * 先备份，再保存实体
   */
  public function save()
  {
    $this->em->flush();
    $this->backupEntity();
    $fileContent = (string) $this->file;
    file_put_contents($this->filePath, $fileContent);
  }

  public function setPath($filePath)
  {
    $this->filePath = $filePath;
    return $this;
  }

  // 读取class
  public function getClass()
  {
    return $this->class;
  }

  // 去读file
  public function getFile()
  {
    return $this->file;
  }
}
