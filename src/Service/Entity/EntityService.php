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
    $zipName = $backupFilePath.'.zip';
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
    $pName = $property['name'];

    // 检查属性是否已存在于数据库中
    if ($this->isExisted($pName)) {
      throw EntityException::alreadyExistsProperty($this->class, $pName);
    }

    $comment = $property['comment'];
    if ($property['type'] = 'string') {
      $finalType = 'string';

      $this->class
      ->addProperty($property['name'])
      ->setVisibility('private')
      ->addComment($comment)
      ->addAttribute('Doctrine\ORM\Mapping\Column',['type' => 'string', 'length' => (integer) $property['length'], 'nullable' => true])
      ;
    }

    // 添加属性的 setter 方法
    $setterMethodName = 'set' . ucfirst($pName);
    $method = $this->class->addMethod($setterMethodName)
      ->setReturnType($this->namespace)
      ->addComment($comment.' Setter')
      ->addComment('@return self')
      ->addBody('$this->' . $pName . ' = $' . $pName . ';')
      ->addBody('return $this;');

    $method->addParameter($pName);

    // 添加属性的 getter 方法
    $getterMethodName = 'get' . ucfirst($pName);
    $this->class->addMethod($getterMethodName)
      ->setReturnType($finalType)
      ->addComment($comment.' Getter')
      ->addBody('return $this->' . $pName . ';');

    $this->insertEntityProperty($property);
    return $this;
  }

  /**
   * 将属性数据插件到数据库platform_entity_property表中
   */
  public function insertEntityProperty($property)
  {
    $prop = new EntityProperty();
    $prop->setToken($property['fieldToken'])
      ->setIsCustomized(true)
      ->setBusinessField(true)
      ->setPropertyName($property['name'])
      ->setComment($property['comment'])
      ->setType($property['type'])
      ->setFieldName(Str::tableize($property['name']))
      ->setNullable(true)
      ->setEntity($this->entity)
      ;
    
    if ($property['type'] === 'string') {
      $prop->setLength($property['length']);
    }

    $this->em->persist($prop);
  }

  public function isExisted($propertyName)
  {
    $pName = $this->namingStrategy->propertyToColumnName($propertyName);
    return in_array($pName, $this->columnNames);
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
