<?php

namespace App\Service\Entity;

use App\Service\BaseService;
use App\Service\FileResolver;
use App\Service\Entity\MigrationService;
use App\Service\Entity\EntityCRUDService;
use App\Entity\Platform\Entity;
use App\Entity\Platform\EntityProperty;
use App\Entity\Platform\EntityPropertyGroup;
use App\Exception\EntityException;
use App\Lib\Str;
use App\Lib\Time;
use Nette\PhpGenerator\PhpFile;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\DefaultNamingStrategy;

class EntityService extends BaseService
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
  private $migrationService;
  private $entityDir;
  private $entityCRUDService;
  // EntityProperty
  private $eProperty;

  public function __construct(
    string $projectDir,
    EntityManagerInterface $em,
    FileResolver $fileResolver,
    MigrationService $migrationService,
    EntityCRUDService $entityCRUDService,
    $filePath = '',
  ) {
    $this->projectDir = $projectDir;
    $this->filePath = $filePath;
    $this->em = $em;
    $this->fR = $fileResolver;
    $this->namingStrategy = new DefaultNamingStrategy();
    $this->migrationService = $migrationService;
    $this->entityDir = $this->projectDir . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Entity';
    $this->entityCRUDService = $entityCRUDService;
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
    try {
      $this->em->flush();
      $this->backupEntity();
      $fileContent = (string) $this->file;
      file_put_contents($this->filePath, $fileContent);

      // 执行 doctrine migrations
      $this->migrationService->executeMigrationsDiff();
      $this->migrationService->executeMigrationsMigrate();
    } catch (\Exception $e) {
      return $e;
    }
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

  // 添加目录
  public function addFolderByEntity(EntityPropertyGroup $post, $type)
  {
    $post->setType('namespace');
    $parentDirectory = $this->getParentDirectoryPath($post);
    $newDirectoryName = $post->getName();
    $post->setLabel($newDirectoryName);
    // 物理目录完整路径
    $newDirectoryPath = $parentDirectory . DIRECTORY_SEPARATOR . $newDirectoryName;

    if ($this->fR->directoryExists($newDirectoryPath)) {
      throw new \RuntimeException(sprintf('目录 %s 已经存在。', $newDirectoryPath));
    }

    // 如果当前命名空间没有父级，则认为它是一个一级命名空间，可以设置一个默认的父级或者根节点
    if ($post->getParent() == null || $type == 'root') {
      // 假设 root 是默认的根节点
      $rootNamespace = $this->em->getRepository(EntityPropertyGroup::class)->findOneBy(['name' => 'root']);
      $post->setParent($rootNamespace);
    }

    $this->em->beginTransaction();
    try {
        $this->fR->createPath($newDirectoryPath);
        $this->em->persist($post);
        $this->em->flush();
        $this->em->commit();
    } catch (\Exception $e) {
      $this->em->rollback();
      throw new \RuntimeException(sprintf('创建目录失败：%s', $e->getMessage()));
    }
  }

  /**
   * 根据给定的目录节点获取父级目录路径
   * @param EntityPropertyGroup $entity
   * @return string
   */
  private function getParentDirectoryPath(EntityPropertyGroup $entity): string
  {
    $groupRepo = $this->em->getRepository(EntityPropertyGroup::class);

    if ($entity->getParent() && $entity->getParent() != 'root') {
      $path = implode(DIRECTORY_SEPARATOR, $groupRepo->getPath($entity->getParent()));
      $path = $this->entityDir . str_replace('root', '', $path);
    } else {
      // 设置默认路径，比如项目根目录或者某个基础路径
      $path = $this->entityDir;
    }

    return $path;
  }

  /**
   * 添加新的 Entity
   *
   * @param Entity $entity 新建的实体对象，
   *   里边包含了两个动态属性 EntityPropertyGroup 的 type 和 parent
   * @throws \Exception 当保存失败时抛出异常
   */
  public function addEntity(Entity $entity): void
  {
    $path = $this->getEntityPath($entity);
    $parentAtEPG = $entity->parentAtEPG;
    $type = $entity->type;
    $entity->setIsCustomized(true);

    if ($this->fR->directoryExists($path)) {
      throw new \RuntimeException(sprintf('目录 %s 已经存在。', $path));
    }

    $this->em->beginTransaction();
    try {
        $this->fR->createPath($path);
        $this->em->persist($entity);
        $this->em->flush();

        // 将 Entity 转换成 EntityPropertyGroup 所需的数据格式
        $data = $this->convertEntityToPropertyGroupData($entity);

        // 创建或获取 EntityPropertyGroup 实例
        $entityPropertyGroup = new EntityPropertyGroup();
        
        // 使用 EntityPropertyGroupService 处理转换后的数据
        $this->entityCRUDService->save($entityPropertyGroup, $data);

        $this->em->commit();
    } catch (\Exception $e) {
      $this->em->rollback();
      throw new \RuntimeException(sprintf('创建目录失败：%s', $e->getMessage()));
    }
  }

  /**
   * 通过Entity实体获取物理文件路径
   *
   * @param [type] $entity
   * @return string
   */
  private function getEntityPath($entity): string
  {
    $namespace = $this->convertFqnToPath($entity->getFqn(), 'App\Entity', $entity->getClassName());
    $path = $this->entityDir . DIRECTORY_SEPARATOR . $namespace .DIRECTORY_SEPARATOR . $entity->getName();
    return $path;
  }

/**
 * 将 FQN 转换为路径格式，去掉指定的命名空间前缀和类名
 *
 * @param string $fqn 完整的类名（FQN），如 "App\Entity\Organization\HelloWorld"
 * @param string $prefixToRemove 要去掉的命名空间前缀，如 "App\Entity"
 * @param string $suffixToRemove 要去掉的类名（或后缀），如 "HelloWorld"
 * @return string 转换后的路径格式
 */
private function convertFqnToPath(string $fqn, string $prefixToRemove, string $suffixToRemove): string
{
    // 去掉前缀命名空间部分
    if (strpos($fqn, $prefixToRemove) === 0) {
        $fqn = substr($fqn, strlen($prefixToRemove) + 1); // 去掉命名空间前缀，并去掉一个多余的 "\"
    }

    // 去掉后缀类名部分
    if (str_ends_with($fqn, $suffixToRemove)) {
        $fqn = substr($fqn, 0, -strlen($suffixToRemove));
    }

    // 将剩余的命名空间分隔符 "\" 转换为目录分隔符 DIRECTORY_SEPARATOR
    $path = str_replace('\\', DIRECTORY_SEPARATOR, $fqn);

    // 去掉路径两端的多余分隔符
    return trim($path, DIRECTORY_SEPARATOR);
}

  /**
   * 通过EntityPropertyGroup实例来拼出 App\Entity\Organization\Company 这种格式的fqn
   *
   * @param [type] $epg
   * @return void
   */
  public function getFqnByEntityPropertyGroup($epg)
  {
    $baseNamespace = 'App\\Entity';
    $groupRepo = $this->em->getRepository(EntityPropertyGroup::class);
    if (!$epg) {
      return $baseNamespace;
    }

    if ($epg !== null) {
      $pathArr = $groupRepo->getPath($epg);
      $path = implode('\\', $pathArr);
      $path = $baseNamespace . str_replace('root', '', $path);
    }

    return $path;
  }

  /**
   * 将Entity实例转换为EntityPropertyGroup实例
   *
   * @param Entity $entity
   * @return array
   * Entity    EntityPropertyGroup
   * $name  --> $name (remove .php extension)
   * $name  --> $label (remove .php extension)
   *            $type (entity)
   *            $token (auto generated)
   * $token --> $entityToken
   * $fqn   --> $fqn
   * $parentAtEPG --> $parent
   * $type  --> $type
   */
  public function convertEntityToPropertyGroupData(Entity $entity): array
  {
    // 生成自动 token
    $token = Str::generateFieldToken();

    // 获取 entity 的属性值并转换
    return [
        'name' => Str::removeExtension($entity->getName()),                
        'label' => Str::removeExtension($entity->getName()), 
        'type' => 'entity',                          
        'token' => $token,                           
        'entityToken' => $entity->getToken(),        
        'fqn' => $entity->getFqn(),                  
        'parent' => $entity->parentAtEPG ?? null,       // 动态属性
        'type' => 'entity',         // 动态属性
    ];
  }
}
