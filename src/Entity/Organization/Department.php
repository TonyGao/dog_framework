<?php

namespace App\Entity\Organization;

use App\Annotation\Ef;
use App\Entity\CommonTrait;
use App\Repository\Organization\DepartmentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Tree\Node as GedmoNode;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * 部门
 * isBusinessEntity
 */
#[Gedmo\Tree(type: 'nested')]
#[ORM\Table(name: 'org_department')]
#[ORM\Entity(repositoryClass: DepartmentRepository::class)]
class Department implements GedmoNode
{
	use CommonTrait;

	/** @Groups({"api"}) */
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: 'integer')]
	private $id;

	/**
	 * 部门全称
	 * @Groups({"api"})
	 * @Ef(
	 *     group="department_base_info",
	 *     isBF=true
	 * )
	 */
	#[ORM\Column(type: 'string', length: 180)]
	private $name;

	/**
	 * 部门简称
	 * @Groups({"api"})
	 * @Ef(
	 *     group="department_base_info",
	 *     isBF=true
	 * )
	 */
	#[ORM\Column(type: 'string', length: 80, nullable: true)]
	private $alias;

	/**
	 * 部门显示名称
	 * @Groups({"api"})
	 * @Ef(
	 *     group="department_base_info_displayname",
	 *     isBF=true
	 * )
	 */
	#[ORM\Column(type: 'string', length: 200, nullable: true)]
	private $displayName;

	/**
	 * 所属公司
	 * @Groups({"api"})
	 * @Ef(
	 *   group="department_base_info",
	 *   isBF=true
	 * )
	 */
	#[ORM\ManyToOne(targetEntity: Company::class)]
	#[ORM\JoinColumn(name: 'company_id', referencedColumnName: 'id')]
	private $company;

	/**
	 * 在树状中的类型
	 * 类型包括：集团、公司、部门
	 * @Groups({"api"})
	 */
	#[ORM\Column(type: 'string')]
	private $type = 'department';

	/**
	 * 部门负责人
	 * @Ef(
	 *     group="department_management_info",
	 *     isBF=true
	 * )
	 */
	#[ORM\ManyToMany(targetEntity: 'App\Entity\OrgUser', mappedBy: 'managedDepartments')]
	private $manager;

	/**
	 * 排序号
	 * @Ef(
	 *     group="department_base_info",
	 *     isBF=true
	 * )
	 */
	#[ORM\Column(type: 'integer', nullable: true)]
	private $orderNum;

	/**
	 * 编码
	 * @Groups({"api"})
	 * @Ef(
	 *    group="department_base_info",
	 *    isBF=true
	 * )
	 */
	#[ORM\Column(type: 'string', length: 180, nullable: true)]
	private $code;

	/**
	 * 状态: 启用、停用
	 * @Ef(
	 *     group="department_base_info",
	 *     isBF=true
	 * )
	 */
	#[ORM\Column(type: 'boolean', options: ['default' => 1])]
	private $state = true;

	/**
	 * 上级部门(树状)
	 * @Ef(
	 *     group="department_associated_info",
	 *     isBF=true
	 * )
	 */
	#[Gedmo\TreeParent]
	#[ORM\ManyToOne(targetEntity: Department::class, inversedBy: 'children')]
	#[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
	private $parent;

	#[Gedmo\TreeLeft]
	#[ORM\Column(name: 'lft', type: 'integer')]
	private $lft;

	#[Gedmo\TreeLevel]
	#[ORM\Column(name: 'lvl', type: 'integer')]
	private $lvl;

	#[Gedmo\TreeRight]
	#[ORM\Column(name: 'rgt', type: 'integer')]
	private $rgt;

	#[Gedmo\TreeRoot]
	#[ORM\ManyToOne(targetEntity: Department::class)]
	#[ORM\JoinColumn(name: 'tree_root', referencedColumnName: 'id', onDelete: 'CASCADE')]
	private $root;

	#[ORM\OneToMany(targetEntity: Department::class, mappedBy: 'parent')]
	#[ORM\OrderBy(['lft' => 'ASC'])]
	private $children;

	/** ERP部门编码 */
	#[ORM\Column(type: 'string', length: 255, nullable: true, unique: true)]
	private $eRPBuMenBianMa;

	/** 部门经理 */
	#[ORM\Column(type: 'string', length: 255, nullable: true, unique: true)]
	private $buMenJingLi;

	/** 部门总监 */
	#[ORM\Column(type: 'string', length: 255, nullable: true, unique: true)]
	private $buMenZongJian;


	public function __toString()
	{
		return $this->alias;
	}


	/**
	 * Get the value of id
	 */
	public function getId()
	{
		return $this->id;
	}


	/**
	 * Set the value of id
	 *
	 * @return  self
	 */
	public function setId($id)
	{
		$this->id = $id;

		return $this;
	}


	/**
	 * Get 部门全称
	 */
	public function getName()
	{
		return $this->name;
	}


	/**
	 * Set 部门全称
	 *
	 * @return  self
	 */
	public function setName($name)
	{
		$this->name = $name;

		return $this;
	}


	/**
	 * Get 部门简称
	 */
	public function getAlias()
	{
		return $this->alias;
	}


	/**
	 * Set 部门简称
	 *
	 * @return  self
	 */
	public function setAlias($alias)
	{
		$this->alias = $alias;

		return $this;
	}


	/**
	 * Get 所属公司
	 */
	public function getCompany()
	{
		return $this->company;
	}


	/**
	 * Set 所属公司
	 *
	 * @return  self
	 */
	public function setCompany($company)
	{
		$this->company = $company;

		return $this;
	}


	/**
	 * Get 在树状中的类型
	 */
	public function getType()
	{
		return $this->type;
	}


	/**
	 * Set 在树状中的类型
	 *
	 * @return  self
	 */
	public function setType($type)
	{
		$this->type = $type;

		return $this;
	}


	/**
	 * Get 排序号
	 */
	public function getOrderNum()
	{
		return $this->orderNum;
	}


	/**
	 * Set 排序号
	 *
	 * @return  self
	 */
	public function setOrderNum($orderNum)
	{
		$this->orderNum = $orderNum;

		return $this;
	}


	/**
	 * Get 编码
	 */
	public function getCode()
	{
		return $this->code;
	}


	/**
	 * Set 编码
	 *
	 * @return  self
	 */
	public function setCode($code)
	{
		$this->code = $code;

		return $this;
	}


	/**
	 * Get 状态: 启用、停用
	 */
	public function getState()
	{
		return $this->state;
	}


	/**
	 * Set 状态: 启用、停用
	 *
	 * @return  self
	 */
	public function setState($state)
	{
		$this->state = $state;

		return $this;
	}


	/**
	 * Get 上级部门(树状)
	 */
	public function getParent()
	{
		return $this->parent;
	}


	/**
	 * Set 上级部门(树状)
	 *
	 * @return  self
	 */
	public function setParent($parent)
	{
		$this->parent = $parent;

		return $this;
	}


	/**
	 * Get 部门显示名称
	 */
	public function getDisplayName()
	{
		return $this->displayName;
	}


	/**
	 * Set 部门显示名称
	 *
	 * @return  self
	 */
	public function setDisplayName($displayName)
	{
		$this->displayName = $displayName;

		return $this;
	}


	/**
	 * Get 部门负责人
	 */
	public function getManager()
	{
		return $this->manager;
	}


	/**
	 * Set 部门负责人
	 *
	 * @return  self
	 */
	public function setManager($manager)
	{
		$this->manager = $manager;

		return $this;
	}


	/**
	 * ERP部门编码 Setter
	 * @return self
	 */
	public function setERPBuMenBianMa($eRPBuMenBianMa): Department
	{
		$this->eRPBuMenBianMa = $eRPBuMenBianMa;
		return $this;
	}


	/**
	 * ERP部门编码 Getter
	 */
	public function getERPBuMenBianMa(): string
	{
		return $this->eRPBuMenBianMa;
	}


	/**
	 * 部门经理 Setter
	 * @return self
	 */
	public function setBuMenJingLi($buMenJingLi): Department
	{
		$this->buMenJingLi = $buMenJingLi;
		return $this;
	}


	/**
	 * 部门经理 Getter
	 */
	public function getBuMenJingLi(): string
	{
		return $this->buMenJingLi;
	}


	/**
	 * 部门总监 Setter
	 * @return self
	 */
	public function setBuMenZongJian($buMenZongJian): Department
	{
		$this->buMenZongJian = $buMenZongJian;
		return $this;
	}


	/**
	 * 部门总监 Getter
	 */
	public function getBuMenZongJian(): string
	{
		return $this->buMenZongJian;
	}
}
