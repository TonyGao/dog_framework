<?php

namespace App\Entity\Platform;

use App\Entity\CommonTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use App\Repository\Platform\OptionValueRepository;

#[ORM\Entity(repositoryClass: OptionValueRepository::class)]
#[ORM\Table(name: 'platform_optionvalue')]
class OptionValue
{
    use CommonTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    /**
     * 编码
     */
    #[ORM\Column(type: 'string', length: 80)]
    private $code;

    /**
     * 字符串型的值
     */
    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private $stringValue;

    /**
     * 整型的值
     */
    #[ORM\Column(type: 'integer', nullable: true)]
    private $integerValue;

    /**
     * 布尔值类型的值
     */
    #[ORM\Column(type: 'boolean', nullable: true)]
    private $booleanValue;

    /**
     * 小数型的值
     */
    #[ORM\Column(type: 'decimal', nullable: true)]
    private $decimalValue;

    /**
     * 日期时间类型的值
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private $datetime;

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
     * Get 字符串型的值
     */
    public function getStringValue()
    {
        return $this->stringValue;
    }

    /**
     * Set 字符串型的值
     *
     * @return  self
     */
    public function setStringValue($stringValue)
    {
        $this->stringValue = $stringValue;

        return $this;
    }

    /**
     * Get 整型的值
     */
    public function getIntegerValue()
    {
        return $this->integerValue;
    }

    /**
     * Set 整型的值
     *
     * @return  self
     */
    public function setIntegerValue($integerValue)
    {
        $this->integerValue = $integerValue;

        return $this;
    }

    /**
     * Get 布尔值类型的值
     */
    public function getBooleanValue()
    {
        return $this->booleanValue;
    }

    /**
     * Set 布尔值类型的值
     *
     * @return  self
     */
    public function setBooleanValue($booleanValue)
    {
        $this->booleanValue = $booleanValue;

        return $this;
    }

    /**
     * Get 小数型的值
     */
    public function getDecimalValue()
    {
        return $this->decimalValue;
    }

    /**
     * Set 小数型的值
     *
     * @return  self
     */
    public function setDecimalValue($decimalValue)
    {
        $this->decimalValue = $decimalValue;

        return $this;
    }

    /**
     * Get 日期时间类型的值
     */
    public function getDatetime()
    {
        return $this->datetime;
    }

    /**
     * Set 日期时间类型的值
     *
     * @return  self
     */
    public function setDatetime($datetime)
    {
        $this->datetime = $datetime;

        return $this;
    }
}
