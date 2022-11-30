<?php

namespace App\Entity\Organization;

use App\Entity\CommonTrait;
use App\Annotation\Ef;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Tree\Node as GedmoNode;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * 公司, 分组: 公司基本信息, 公司关联信息, 公司管理信息, 公司说明信息
 * @Gedmo\Tree(type="nested")
 * @ORM\Table(name="organization_company")
 * @ORM\Entity(repositoryClass=CompanyPropertyRepository::class)
 * @ORM\Table(name="organization_company")
 * isBusinessEntity
 */
class Company implements GedmoNode
{
    use CommonTrait;

	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(type="integer")
	 */
	private $id;

	/**
	 * 公司名称
	 * @ORM\Column(type="string", length=180)
     * @Ef(
     *     group="company_base_info",
     *     isBF=true
     * )
	 */
	private $name;

	/**
	 * 简称
	 * @ORM\Column(type="string", length=80, nullable=true)
     * @Ef(
     *     group="company_base_info",
     *     isBF=true
     * )
	 */
	private $alias;

	/**
	 * 编码
	 * @ORM\Column(type="string", length=180, nullable=true)
     * @Ef(
     *    group="company_base_info",
     *    isBF=true
     * )
	 */
	private $code;

	/**
	 * 描述
	 * @ORM\Column(type="text")
     * @Ef(
     *     group="company_base_info",
     *     isBF=true
     * )
	 */
	private $remark;

    /**
     * 排序号
     * @ORM\Column(type="integer")
     * @Ef(
     *     group="company_base_info",
     *     isBF=true
     * )
     */
	private $orderNum;

    /**
     * 重复排序号处理: 插入、重复
     * @ORM\Column(type="string")
     * @Ef(
     *     group="company_base_info",
     *     isBF=true
     * )
     */
    private $repetitionNumHandling;

    /**
     * 状态: 启用、停用
     * @ORM\Column(type="boolean")
     * @Ef(
     *     group="company_base_info",
     *     isBF=true
     * )
     */
    private $state;

    /**
     * 独立登录页
     * @ORM\Column(type="boolean")
     * @Ef(
     *     group="company_base_info",
     *     isBF=true
     * )
     */
    private $loginIndependent;

    /**
     * 上级公司
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="Company", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     * @Ef(
     *     group="company_associated_info",
     *     isBF=true
     * )
     */
    private $parent;

    /**
     * 访问范围，一个公司有可见多个公司
     * @ORM\OneToMany(targetEntity="Company", mappedBy="accessSourcing")
     */
    private $accessScope;

    /**
     * 被什么公司访问
     * @ORM\ManyToOne(targetEntity="Company", inversedBy="accessScope")
     * @ORM\JoinColumn(name="access_sourcing_company_id", referencedColumnName="id")
     */
    private $accessSourcing = null;

    /**
     * @Gedmo\TreeLeft
     * @ORM\Column(name="lft", type="integer")
     */
    private $lft;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(name="lvl", type="integer")
     */
    private $lvl;

    /**
     * @Gedmo\TreeRight
     * @ORM\Column(name="rgt", type="integer")
     */
    private $rgt;

    /**
     * @Gedmo\TreeRoot
     * @ORM\ManyToOne(targetEntity="Company")
     * @ORM\JoinColumn(name="tree_root", referencedColumnName="id", onDelete="CASCADE")
     */
    private $root;

    /**
     * @ORM\OneToMany(targetEntity="Company", mappedBy="parent")
     * @ORM\OrderBy({"lft" = "ASC"})
     */
    private $children;

    public function __construct()
    {
        $this->accessScope = new ArrayCollection();
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
	 * Get 公司名称
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Set 公司名称
	 *
	 * @return  self
	 */
	public function setName($name)
	{
		$this->name = $name;

		return $this;
	}

	/**
	 * Get 简称
	 */
	public function getAlias()
	{
		return $this->alias;
	}

	/**
	 * Set 简称
	 *
	 * @return  self
	 */
	public function setAlias($alias)
	{
		$this->alias = $alias;

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
	 * Get 描述
	 */
	public function getRemark()
	{
		return $this->remark;
	}

	/**
	 * Set 描述
	 *
	 * @return  self
	 */
	public function setRemark($remark)
	{
		$this->remark = $remark;

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
     * Get 重复排序号处理: 插入、重复
     */
    public function getRepetitionNumHandling()
    {
        return $this->repetitionNumHandling;
    }

    /**
     * Set 重复排序号处理: 插入、重复
     *
     * @return  self
     */
    public function setRepetitionNumHandling($repetitionNumHandling)
    {
        $this->repetitionNumHandling = $repetitionNumHandling;

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
     * Get 独立登录页
     */
    public function getLoginIndependent()
    {
        return $this->loginIndependent;
    }

    /**
     * Set 独立登录页
     *
     * @return  self
     */
    public function setLoginIndependent($loginIndependent)
    {
        $this->loginIndependent = $loginIndependent;

        return $this;
    }
}
