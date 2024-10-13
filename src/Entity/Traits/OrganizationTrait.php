<?php

namespace App\Entity\Traits;

use App\Entity\OrgUser;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Organization\Company;
use App\Entity\Organization\Corporation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Doctrine\Persistence\Event\LifecycleEventArgs;

trait OrganizationTrait
{
    private ?Security $security = null;

    public function setSecurity(Security $security): void
    {
        $this->security = $security;
    }

    protected function getCurrentUser()
    {
        return $this->security ? $this->security->getUser() : null;
    }

    #[ORM\ManyToOne(targetEntity: Corporation::class)]
    #[ORM\JoinColumn(name: 'owner_corporation_id', referencedColumnName: 'id', nullable: true)]
    protected $ownerCorporation;

    #[ORM\ManyToOne(targetEntity: Company::class)]
    #[ORM\JoinColumn(name: 'owner_company_id', referencedColumnName: 'id', nullable: true)]
    protected $ownerCompany;


    #[ORM\PrePersist]
    public function setDefaultCorporation(LifecycleEventArgs $args)
    {
        $em = $args->getObjectManager();
        if ($this->ownerCorporation === null) {
            $this->ownerCorporation = $this->getDefaultCorporation($em);
        }
    }

    private function getDefaultCorporation(EntityManagerInterface $em): ?Corporation
    {
        // 获取默认的 Corporation 实例
            $corporation = $em->getRepository(Corporation::class)->findOneBy([]);
            return $corporation;
    }

    #[ORM\PrePersist]
    public function initializeCompany(): self
    {
        if ($this->ownerCompany === null) {
            $user = $this->getCurrentUser();
            
            if ($user instanceof OrgUser) {
                $this->ownerCompany = $user->getOwnerCompany();
            } else {
                $this->ownerCompany = null; // 处理未登录用户的情况
            }
        }
        
        return $this;
    }

    /**
     * Get the value of ownerCorporation
     */ 
    public function getOwnerCorporation()
    {
        return $this->ownerCorporation;
    }

    /**
     * Set the value of ownerCorporation
     *
     * @return  self
     */ 
    public function setOwnerCorporation($ownerCorporation)
    {
        $this->ownerCorporation = $ownerCorporation;

        return $this;
    }

    /**
     * Get the value of ownerCompany
     */ 
    public function getOwnerCompany()
    {
        return $this->ownerCompany;
    }

    /**
     * Set the value of ownerCompany
     *
     * @return  self
     */ 
    public function setOwnerCompany($ownerCompany)
    {
        $this->ownerCompany = $ownerCompany;

        return $this;
    }
}
