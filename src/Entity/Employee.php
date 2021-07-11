<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\EmployeeRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use JsonSerializable;

/**
 * @ORM\Entity(repositoryClass=EmployeeRepository::class)
 */
class Employee implements JsonSerializable
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(length=255)
     */
    protected $firstname;

    /**
     * @ORM\Column(length=255)
     */
    private $lastname;

    /**
     * @ORM\Column(length=255)
     */
    private $email;

    /**
     * @ORM\Column(length=255, nullable=true)
     */
    private $phone;

    /**
     * @ORM\Column(length=255)
     */
    private $password;

    /**
     * @ORM\ManyToOne(targetEntity=Company::class, inversedBy="employees")
     * @ORM\JoinColumn(nullable=false)
     */
    private $company;

    /**
     * @ORM\ManyToMany(targetEntity=Role::class, inversedBy="employees")
     */
    private $roles;

    public function __construct()
    {
        $this->roles = new ArrayCollection();

    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

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

    public function getRoles(): Collection
    {
        return $this->roles;
    }

    public function addRole(Role $role): self
    {
        if (!$this->roles->contains($role)) {
            $this->roles[] = $role;
            $role->addEmployee($this);
        }

        return $this;
    }

    public function removeRole(Role $role): self
    {
        if ($this->roles->contains($role)) {
            $this->roles->removeElement($role);
            $role->removeEmployee($this);
        }

        return $this;
    }

    public function clearRoles(): self 
    {
        $this->roles->clear();

        return $this;
    }

    public function jsonSerialize()
    {
        return [
            "id" => $this->id,
            "firstname" => $this->firstname,
            "lastname" => $this->lastname,
            "company" => $this->company,
            "roles" => $this->roles,
            "email" => $this->email,
            "phone" => $this->phone,
        ];
    }
}
