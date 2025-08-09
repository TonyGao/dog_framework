<?php

namespace App\Entity\Platform;

use App\Entity\Traits\CommonTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * 数据源
 */
#[ORM\Entity]
#[ORM\Table(name: "platform_datasource")]
class DataSource 
{
  use CommonTrait;

  #[ORM\Id]
  #[ORM\Column(type: "uuid", unique: true)]
  private $id;

  /**
   * 数据源名称
   */
  #[ORM\Column(type: "string", length: 255)]
  private $name;

  /**
   * 数据源类型
   * entity, custom_query, api, database_view ...
   */
  #[ORM\Column(type: "string", length: 50)]
  private $type;

  /**
   * 资源
   * 实体名称, 自定义查询, API 地址, 数据库视图名称 ...
   */
  #[ORM\Column(type: "string", length: 255, nullable: true)]
  private ?string $resource = null;

  /**
   * $query 用于自定义SQL或QueryBuilder表达式
   */
  #[ORM\Column(type: "text", nullable: true)]
  private ?string $query = null;

  /**
   * $params 动态参数配置(API认证、Query参数、请求头等)
   */
  #[ORM\Column(type: "json", nullable: true)]
  private ?array $params = null;

  /**
   * DataGrid，一个数据源可以关联多个数据表格
   */
  #[ORM\OneToMany(
    targetEntity: DataGrid::class,
    mappedBy: "dataSource",
    orphanRemoval: true,
    cascade: ["persist"]
  )]
  private Collection $dataGrid;

  /**
   * 数据库连接
   */
  #[ORM\ManyToOne(targetEntity: DatabaseConnection::class, inversedBy: "dataSources")]
  #[ORM\JoinColumn(nullable: true)]
  private ?DatabaseConnection $databaseConnection = null;

  public function __construct()
  {
    $this->dataGrid = new ArrayCollection();
    $this->id = Uuid::v4();
  }

  public function getId(): ?string
  {
    return $this->id;
  }

  public function getName(): ?string
  {
    return $this->name;
  }

  public function setName(string $name): self
  {
    $this->name = $name;
    return $this;
  }

  public function getType(): ?string
  {
    return $this->type;
  }

  public function setType(string $type): self
  {
    $this->type = $type;
    return $this;
  }

  public function getResource(): ?string
  {
    return $this->resource;
  }

  public function setResource(?string $resource): self
  {
    $this->resource = $resource;
    return $this;
  }

  public function getQuery(): ?string
  {
    return $this->query;
  }

  public function setQuery(?string $query): self
  {
    $this->query = $query;
    return $this;
  }

  public function getParams(): ?array
  {
    return $this->params;
  }

  public function setParams(?array $params): self
  {
    $this->params = $params;
    return $this;
  }

  public function getDataGrid(): Collection
  {
    return $this->dataGrid;
  }

  public function addDataGrid(DataGrid $dataGrid): self
  {
    if (!$this->dataGrid->contains($dataGrid)) {
      $this->dataGrid[] = $dataGrid;
      $dataGrid->setDataSource($this);
    }
    return $this;
  }

  public function removeDataGrid(DataGrid $dataGrid): self
  {
    if ($this->dataGrid->removeElement($dataGrid)) {
      if ($dataGrid->getDataSource() === $this) {
        $dataGrid->setDataSource(null);
      }
    }
    return $this;
  }

  public function getDatabaseConnection(): ?DatabaseConnection
  {
    return $this->databaseConnection;
  }

  public function setDatabaseConnection(?DatabaseConnection $databaseConnection): self
  {
    $this->databaseConnection = $databaseConnection;
    return $this;
  }
}