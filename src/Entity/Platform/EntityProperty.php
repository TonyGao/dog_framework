<?php

namespace App\Entity\Platform;

use App\Entity\CommonTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use App\Repository\Platform\EntityPropertyRepository;

/**
 * 实体属性
 * @ORM\Entity(repositoryClass=EntityPropertyRepository::class)
 * @ORM\Table(name="platform_entity_property",
 *     indexes={@ORM\Index(name="entity_property_idx", columns={"token"})}
 * )
 */
class EntityProperty
{
    use CommonTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * 编码
     * @ORM\Column(type="string", length=80, nullable=true)
     */
    private $code = null;

    /**
     * 属性令牌
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    private $token = null;

    /**
     * 是自定义的
     * @ORM\Column(type="boolean")
     */
    private $isCustomized;

    /**
     * 业务字段，这个布尔值决定了增删改查时是否显示此字段
     * @ORM\Column(type="boolean")
     */
    private $businessField = false;

    /**
     * 属性名称
     * @ORM\Column(type="string", length=40)
     */
    private $propertyName;

    /**
     * 属性备注
     * @ORM\Column(type="string", length=500)
     */
    private $comment;

    /**
     * 属性类型
     * (string, integer, smallint, bigint, boolean,
     *  decimal, date, time, datetime, datetimez, text,
     *  object, array, simple_array, json_array, float,
     *  guid, blob, entity)
     * @ORM\Column(type="string", length=40)
     */
    private $type;

    /**
     * 当$type为entity时的目标实体
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $targetEntity = null;

    /**
     * 当属性类型为string时，定义的长度
     * @ORM\Column(type="integer", nullable=true)
     */
    private $length = null;

    /**
     * schema 数据中表中的字段名称
     * @ORM\Column(type="string", length=80)
     */
    private $fieldName;

    /**
     * 唯一性
     * @ORM\Column(type="boolean")
     */
    private $uniqueable = false;

    /**
     * 是否可以为空
     * @ORM\Column(type="boolean")
     */
    private $nullable = false;

    /**
     * 是否允许插入
     * @ORM\Column(type="boolean")
     */
    private $insertable = true;


    /**
     * 是否允许更新
     * @ORM\Column(type="boolean")
     */
    private $updatable = true;


    /**
     * 数字精度
     * @ORM\Column(type="integer")
     */
    private $decimalPrecision = 0;

    /**
     * 数字小数位数
     * @ORM\Column(type="integer")
     */
    private $decimalScale = 0;

    /**
     * 验证条件
     * @ORM\Column(type="json", nullable=true)
     */
    private $validation;

    /**
     * 属性所属的实体
     * @ORM\ManyToOne(
     *     targetEntity="Entity",
     *     inversedBy="properties",
     *     cascade={"persist"}
     * )
     * ORM\JoinColumn(name="entity_id", referencedColumnName="id", , onDelete="SET NULL")
     */
    private $entity = null;

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
     * Get 属性令牌
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set 属性令牌
     *
     * @return  self
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get 属性名称
     */
    public function getPropertyName()
    {
        return $this->propertyName;
    }

    /**
     * Set 属性名称
     *
     * @return  self
     */
    public function setPropertyName($propertyName)
    {
        $this->propertyName = $propertyName;

        return $this;
    }

    /**
     * Get 属性备注
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set 属性备注
     *
     * @return  self
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get 属性类型
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set 属性类型
     *
     * @return  self
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get 当$type为entity时的目标实体
     */
    public function getTargetEntity()
    {
        return $this->targetEntity;
    }

    /**
     * Set 当$type为entity时的目标实体
     *
     * @return  self
     */
    public function setTargetEntity($targetEntity)
    {
        $this->targetEntity = $targetEntity;

        return $this;
    }

    /**
     * Get 当属性类型为string时，定义的长度
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * Set 当属性类型为string时，定义的长度
     *
     * @return  self
     */
    public function setLength($length)
    {
        $this->length = $length;

        return $this;
    }

    /**
     * Get schema 数据中表中的字段名称
     */
    public function getFieldName()
    {
        return $this->fieldName;
    }

    /**
     * Set schema 数据中表中的字段名称
     *
     * @return  self
     */
    public function setFieldName($fieldName)
    {
        $this->fieldName = $fieldName;

        return $this;
    }

    /**
     * Get 唯一性
     */
    public function getUniqueable()
    {
        return $this->uniqueable;
    }

    /**
     * Set 唯一性
     *
     * @return  self
     */
    public function setUniqueable($uniqueable)
    {
        $this->uniqueable = $uniqueable;

        return $this;
    }

    /**
     * Get 是否可以为空
     */
    public function getNullable()
    {
        return $this->nullable;
    }

    /**
     * Set 是否可以为空
     *
     * @return  self
     */
    public function setNullable($nullable)
    {
        $this->nullable = $nullable;

        return $this;
    }

    /**
     * Get 是否允许插入
     */
    public function getInsertable()
    {
        return $this->insertable;
    }

    /**
     * Set 是否允许插入
     *
     * @return  self
     */
    public function setInsertable($insertable)
    {
        $this->insertable = $insertable;

        return $this;
    }

    /**
     * Get 是否允许更新
     */
    public function getUpdatable()
    {
        return $this->updatable;
    }

    /**
     * Set 是否允许更新
     *
     * @return  self
     */
    public function setUpdatable($updatable)
    {
        $this->updatable = $updatable;

        return $this;
    }

    /**
     * Get 数字精度
     */
    public function getDecimalPrecision()
    {
        return $this->decimalPrecision;
    }

    /**
     * Set 数字精度
     *
     * @return  self
     */
    public function setDecimalPrecision($decimalPrecision)
    {
        $this->decimalPrecision = $decimalPrecision;

        return $this;
    }

    /**
     * Get 数字小数位数
     */
    public function getDecimalScale()
    {
        return $this->decimalScale;
    }

    /**
     * Set 数字小数位数
     *
     * @return  self
     */
    public function setDecimalScale($decimalScale)
    {
        $this->decimalScale = $decimalScale;

        return $this;
    }

    /**
     * Get 属性所属的实体
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Set 属性所属的实体
     *
     * @return  self
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * Get 是自定义的
     */
    public function getIsCustomized()
    {
        return $this->isCustomized;
    }

    /**
     * Set 是自定义的
     *
     * @return  self
     */
    public function setIsCustomized($isCustomized)
    {
        $this->isCustomized = $isCustomized;

        return $this;
    }

    /**
     * Get 业务字段，这个布尔值决定了增删改查时是否显示此字段
     */
    public function getBusinessField()
    {
        return $this->businessField;
    }

    /**
     * Set 业务字段，这个布尔值决定了增删改查时是否显示此字段
     *
     * @return  self
     */
    public function setBusinessField($businessField)
    {
        $this->businessField = $businessField;

        return $this;
    }

    /**
     * Get 验证条件
     */
    public function getValidation()
    {
        return $this->validation;
    }

    /**
     * Set 验证条件
     *
     * @return  self
     */
    public function setValidation($validation)
    {
        $this->validation = $validation;

        return $this;
    }
}
