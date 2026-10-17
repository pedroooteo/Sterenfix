<?php

namespace App\Entity;

use App\Repository\EmpleadoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EmpleadoRepository::class)]
class Empleado
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $nombres = null;

    #[ORM\Column(length: 100)]
    private ?string $apellidos = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $fechaNacimiento = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fotografia = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2)]
    private ?string $salario = null;

    #[ORM\ManyToOne(inversedBy: 'empleados')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Puesto $puesto = null;

    #[ORM\ManyToOne(inversedBy: 'empleados')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Tienda $tienda = null;

    /**
     * @var Collection<int, RegistroEmpleado>
     */
    #[ORM\OneToMany(targetEntity: RegistroEmpleado::class, mappedBy: 'empleado')]
    private Collection $registroEmpleados;

    public function __construct()
    {
        $this->registroEmpleados = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombres(): ?string
    {
        return $this->nombres;
    }

    public function setNombres(string $nombres): static
    {
        $this->nombres = $nombres;

        return $this;
    }

    public function getApellidos(): ?string
    {
        return $this->apellidos;
    }

    public function setApellidos(string $apellidos): static
    {
        $this->apellidos = $apellidos;

        return $this;
    }

    public function getFechaNacimiento(): ?\DateTimeImmutable
    {
        return $this->fechaNacimiento;
    }

    public function setFechaNacimiento(\DateTimeImmutable $fechaNacimiento): static
    {
        $this->fechaNacimiento = $fechaNacimiento;

        return $this;
    }

    public function getFotografia(): ?string
    {
        return $this->fotografia;
    }

    public function setFotografia(?string $fotografia): static
    {
        $this->fotografia = $fotografia;

        return $this;
    }

    public function getSalario(): ?string
    {
        return $this->salario;
    }

    public function setSalario(string $salario): static
    {
        $this->salario = $salario;

        return $this;
    }

    public function getPuesto(): ?Puesto
    {
        return $this->puesto;
    }

    public function setPuesto(?Puesto $puesto): static
    {
        $this->puesto = $puesto;

        return $this;
    }

    public function getTienda(): ?Tienda
    {
        return $this->tienda;
    }

    public function setTienda(?Tienda $tienda): static
    {
        $this->tienda = $tienda;

        return $this;
    }

    /**
     * @return Collection<int, RegistroEmpleado>
     */
    public function getRegistroEmpleados(): Collection
    {
        return $this->registroEmpleados;
    }

    public function addRegistroEmpleado(RegistroEmpleado $registroEmpleado): static
    {
        if (!$this->registroEmpleados->contains($registroEmpleado)) {
            $this->registroEmpleados->add($registroEmpleado);
            $registroEmpleado->setEmpleado($this);
        }

        return $this;
    }

    public function removeRegistroEmpleado(RegistroEmpleado $registroEmpleado): static
    {
        if ($this->registroEmpleados->removeElement($registroEmpleado)) {
            // set the owning side to null (unless already changed)
            if ($registroEmpleado->getEmpleado() === $this) {
                $registroEmpleado->setEmpleado(null);
            }
        }

        return $this;
    }
}
