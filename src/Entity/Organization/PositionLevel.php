<?php

namespace App\Entity\Organization;

use App\Entity\Traits\CommonTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Uid\Uuid;

/**
 * 职务级别
 */
#[ORM\Entity(repositoryClass: 'App\Repository\HelloWorldRepository')]
#[ORM\Table(name: 'app_entity_organization_hello_world')]
#[ORM\HasLifecycleCallbacks]
class PositionLevel
{
	use CommonTrait;

	/** ID字段 */
	#[ORM\Id]
	#[ORM\Column(type: 'uuid', unique: 'true')]
	private $id;

	/** 职级名称 */
	#[ORM\Column(type: 'string', length: 255, nullable: false, unique: true)]
	private $zhiJiMingCheng;


	public function __construct()
	{
		$this->id = Uuid::v4();
	}


	/**
	 * 获取ID
	 */
	public function getId(): string
	{
		return $this->id;
	}


	/**
	 * 设置ID
	 */
	public function setId($id): self
	{
		return $this;
	}


	/**
	 * 职级名称 Setter
	 * @return self
	 */
	public function setZhiJiMingCheng($zhiJiMingCheng): PositionLevel
	{
		$this->zhiJiMingCheng = $zhiJiMingCheng;
		return $this;
	}


	/**
	 * 职级名称 Getter
	 */
	public function getZhiJiMingCheng(): string
	{
		return $this->zhiJiMingCheng;
	}
}
