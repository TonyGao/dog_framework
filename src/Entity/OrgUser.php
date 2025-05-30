<?php

namespace App\Entity;

use App\Annotation\Ef;
use App\Entity\Traits\CommonTrait;
use App\Repository\Organization\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\AssociationOverride;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Uid\Uuid;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * 用户
 * isBusinessEntity
 */
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`org_user`')]
#[UniqueEntity(fields: ['username'], message: 'There is already an account with this username')]
#[ORM\HasLifecycleCallbacks]
class OrgUser implements UserInterface, PasswordAuthenticatedUserInterface
{

	use CommonTrait;

	#[ORM\Id]
	#[ORM\Column(type: "uuid", unique: true)]
	private $id;

	/**
	 * 用户名
	 * @Ef(
	 *     group="user_base_info",
	 *     isBF=true
	 * )
	 */
	#[ORM\Column(type: 'string', length: 180, unique: true)]
	private $username;

	/**
	 * 姓名、显示名
	 * @Ef(
	 *     group="user_base_info",
	 *     isBF=true
	 * )
	 */
	#[ORM\Column(type: 'string', length: 180, unique: true, nullable: true)]
	private $displayName;

	/**
	 * 邮箱
	 * @Ef(
	 *     group="user_base_info",
	 *     isBF=true
	 * )
	 */
	#[ORM\Column(type: 'string', unique: true, nullable: true)]
	#[Assert\NotBlank]
	#[Assert\Email]
	private $email;

	/**
	 * 手机号
	 * @Ef(
	 *     group="user_base_info",
	 *     isBF=true
	 * )
	 */
	#[ORM\Column(type: 'string', unique: true, nullable: true)]
	private $phone;

	/**
	 * 角色
	 * @Ef(
	 *     group="user_base_info",
	 *     isBF=true
	 * )
	 */
	#[ORM\Column(type: 'json')]
	private $roles = [];

	#[ORM\ManyToMany(targetEntity: 'App\Entity\Organization\Department', inversedBy: 'manager')]
	#[ORM\JoinTable(name: 'org_department_managers')]
	#[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
	#[ORM\InverseJoinColumn(name: 'department_id', referencedColumnName: 'id')]
	private $managedDepartments;

	#[Assert\NotBlank]
	#[Assert\Length(max: 4096)]
	private $plainPassword;

	/** @var string The hashed password */
	#[ORM\Column(type: 'string')]
	private $password;

	#[ORM\Column(type: 'boolean', nullable: true)]
	private $isVerified = false;

	public function __construct()
	{
			// 自动生成 UUID
			$this->id = Uuid::v4();
	}

	public function __toString(): string
	{
		return $this->getDisplayName(); // 或根据需要返回其他字段
	}


	public function getId(): ?int
	{
		return $this->id;
	}


	/**
	 * @deprecated since Symfony 5.3, use getUserIdentifier instead
	 */
	public function getUsername(): string
	{
		return (string) $this->username;
	}


	public function setUsername(string $username): self
	{
		$this->username = $username;

		return $this;
	}


	/**
	 * A visual identifier that represents this user.
	 *
	 * @see UserInterface
	 */
	public function getUserIdentifier(): string
	{
		return (string) $this->username;
	}


	/**
	 * @see UserInterface
	 */
	public function getRoles(): array
	{
		$roles = $this->roles;
		// guarantee every user at least has ROLE_USER
		$roles[] = 'ROLE_USER';

		return array_unique($roles);
	}


	public function setRoles(array $roles): self
	{
		$this->roles = $roles;

		return $this;
	}


	/**
	 * @see PasswordAuthenticatedUserInterface
	 */
	public function getPassword(): string
	{
		return $this->password;
	}


	public function setPassword(string $password): self
	{
		$this->password = $password;

		return $this;
	}


	/**
	 * Returning a salt is only needed, if you are not using a modern
	 * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
	 *
	 * @see UserInterface
	 */
	public function getSalt(): ?string
	{
		return null;
	}

	/**
	 * @see UserInterface
	 */
	public function eraseCredentials()
	{
		// If you store any temporary, sensitive data on the user, clear it here
		// $this->plainPassword = null;
	}


	/**
	 * Get the value of displayName
	 */
	public function getDisplayName(): string
	{
		return $this->displayName;
	}


	/**
	 * Set the value of displayName
	 *
	 * @return  self
	 */
	public function setDisplayName($displayName): self
	{
		$this->displayName = $displayName;

		return $this;
	}


	/**
	 * Get the value of email
	 *
	 * @return  string
	 */
	public function getEmail(): ?string
	{
		return $this->email;
	}


	/**
	 * Set the value of email
	 *
	 * @param  string  $email
	 *
	 * @return  self
	 */
	public function setEmail(string $email): self
	{
		$this->email = $email;

		return $this;
	}


	/**
	 * Get the value of phone
	 *
	 * @return  string
	 */
	public function getPhone(): ?string
	{
		return $this->phone;
	}


	/**
	 * Set the value of phone
	 *
	 * @param  string  $phone
	 *
	 * @return  self
	 */
	public function setPhone(string $phone): self
	{
		$this->phone = $phone;

		return $this;
	}


	public function isVerified(): bool
	{
		return $this->isVerified;
	}


	public function setIsVerified(bool $isVerified): self
	{
		$this->isVerified = $isVerified;

		return $this;
	}


	/**
	 * Get the value of plainPassword
	 */
	public function getPlainPassword(): ?string
	{
		return $this->plainPassword;
	}


	/**
	 * Set the value of plainPassword
	 *
	 * @return  self
	 */
	public function setPlainPassword($plainPassword): self
	{
		$this->plainPassword = $plainPassword;

		return $this;
	}


	/**
	 * Get joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
	 */
	public function getManagedDepartments()
	{
		return $this->managedDepartments;
	}


	/**
	 * Set joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
	 *
	 * @return  self
	 */
	public function setManagedDepartments($managedDepartments)
	{
		$this->managedDepartments = $managedDepartments;

		return $this;
	}
}
