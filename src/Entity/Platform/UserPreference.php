<?php

namespace App\Entity\Platform;

use App\Entity\Organization\Employee;
use App\Entity\Traits\CommonTrait;
use App\Repository\Platform\UserPreferenceRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: UserPreferenceRepository::class)]
#[ORM\Table(name: 'platform_user_preference')]
#[ORM\HasLifecycleCallbacks]
class UserPreference
{
    use CommonTrait;

    #[ORM\Id]
    #[ORM\Column(type: "uuid", unique: true)]
    private $id;

    #[ORM\ManyToOne(targetEntity: Employee::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $user;

    #[ORM\Column(type: 'string', length: 100)]
    private $prefKey;

    #[ORM\Column(type: 'json')]
    private $prefValue = [];

    public function __construct()
    {
        $this->id = Uuid::v4();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getUser(): ?Employee
    {
        return $this->user;
    }

    public function setUser(?Employee $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getPrefKey(): ?string
    {
        return $this->prefKey;
    }

    public function setPrefKey(string $prefKey): self
    {
        $this->prefKey = $prefKey;
        return $this;
    }

    public function getPrefValue(): array
    {
        return $this->prefValue;
    }

    public function setPrefValue(array $prefValue): self
    {
        $this->prefValue = $prefValue;
        return $this;
    }
}
