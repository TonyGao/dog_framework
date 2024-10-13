<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Organization\Company;
use App\Entity\Organization\Corporation;
use Gedmo\Blameable\Traits\BlameableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;

/**
 * Trait SoftDeleteableTrait
 */
trait SoftDeleteableTrait
{
    use SoftDeleteableEntity;
}

/**
 * Trait TimestampableTrait
 */
trait TimestampableTrait
{
    use TimestampableEntity;
}

/**
 * Trait BlameableTrait
 */
trait BlameableTrait
{
    use BlameableEntity;
}

/**
 * Trait OrganizationTrait
 */
trait OrganizationTrait
{
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
}
