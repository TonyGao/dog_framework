<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Organization\Company;
use App\Entity\Organization\Corporation;
use Gedmo\Blameable\Traits\BlameableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;

trait CommonTrait
{
    /**
     * use soft delete trait
     * add deletedAt field
     */
    use SoftDeleteableEntity;

    /**
     * use timestamp trait to record time of entity
     * add createdAt, updateAt fields
     */
    use TimestampableEntity;

    /**
     * use blameable trait to record modifier etc
     * add createdBy, updatedBy fields
     */
    use BlameableEntity;

    #[ORM\ManyToOne(targetEntity: Corporation::class)]
    #[ORM\JoinColumn(name: 'corporation_id', referencedColumnName: 'id', nullable: true)]
    private $corporation;

    #[ORM\ManyToOne(targetEntity: Company::class)]
    #[ORM\JoinColumn(name: 'company_id', referencedColumnName: 'id', nullable: true)]
    private $company;

    public function getCorporation(): ?Corporation
    {
        return $this->corporation;
    }

    public function setCorporation(?Corporation $corporation): self
    {
        $this->corporation = $corporation;
        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): self
    {
        $this->company = $company;
        return $this;
    }

    /**
     * Pre persist event listener
     *
     * @ORM\PrePersist
     */
    public function beforeSave()
    {
        $this->createdAt = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * Pre update event listener
     *
     * @ORM\PreUpdate
     */
    public function beforeUpdate()
    {
        $this->updatedAt = new \DateTime('now', new \DatetimeZone('UTC'));
    }
}
