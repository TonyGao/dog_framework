<?php

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Persistence\Event\LifecycleEventArgs;

trait CommonTraitWithoutOrg
{
    use BasicTrait;

    #[ORM\Column(type: 'integer', nullable: true)]
    protected $orderNum;

    #[ORM\PrePersist]
    public function beforeSave(LifecycleEventArgs $args)
    {
        $em = $args->getObjectManager();
        $this->createdAt = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));

        // 设置 orderNum
        if ($this->orderNum === null) {
            $maxOrderNum = $em->createQueryBuilder()
                ->select('MAX(e.orderNum)')
                ->from(get_class($this), 'e')
                ->getQuery()
                ->getSingleScalarResult();

            $this->orderNum = $maxOrderNum !== null ? $maxOrderNum + 1 : 1;
        }
    }

    #[ORM\PreUpdate]
    public function beforeUpdate()
    {
        $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }
}
