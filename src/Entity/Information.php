<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToOne;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity(repositoryClass="App\Repository\InformationRepository")
 * @Vich\Uploadable
 */
class Information
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $prenom;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $adresse;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $telephone;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $email;

    /**
     *
     * @Vich\UploadableField(mapping="photos", fileNameProperty="photoName", size="photoSize")
     *
     * @var File
     */
    private $photo;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @var string
     */
    private $photoName;

    /**
     * @ORM\Column(type="integer")
     *
     * @var integer
     */
    private $photoSize;

    /**
     * @ORM\Column(type="datetime")
     *
     * @var \DateTime
     */
    private $updatedAt;

    /**
     *
     * @OneToOne(targetEntity="App\Entity\About")
     *
     * @JoinColumn(name="about_id", referencedColumnName="id")
     */
    private $about;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Prestation", mappedBy="information", cascade={"persist"})
     */
    private $prestations;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Pathologie", mappedBy="information", cascade={"persist"})
     */
    private $pathologies;

    public function __construct()
    {
        $this->prestations = new ArrayCollection();
        $this->pathologies = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): self
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): self
    {
        $this->telephone = $telephone;

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

    public function getAbout(): ?About
    {
        return $this->about;
    }

    public function setAbout(?About $about): self
    {
        $this->about = $about;

        return $this;
    }

    public function setPhoto(?File $photo = null): void
    {
        $this->photo = $photo;

        if (null !== $photo) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getPhoto(): ?File
    {
        return $this->photo;
    }

    public function getPhotoName(): ?string
    {
        return $this->photoName;
    }

    public function setPhotoName(string $photoName): self
    {
        $this->photoName = $photoName;

        return $this;
    }

    public function getPhotoSize(): ?int
    {
        return $this->photoSize;
    }

    public function setPhotoSize(int $photoSize): self
    {
        $this->photoSize = $photoSize;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection|Prestation[]
     */
    public function getPrestations(): Collection
    {
        return $this->prestations;
    }

    public function addPrestation(Prestation $prestation): self
    {
        if (!$this->prestations->contains($prestation)) {
            $this->prestations[] = $prestation;
            $prestation->setInformation($this);
        }

        return $this;
    }

    public function removePrestation(Prestation $prestation): self
    {
        if ($this->prestations->contains($prestation)) {
            $this->prestations->removeElement($prestation);
            // set the owning side to null (unless already changed)
            if ($prestation->getInformation() === $this) {
                $prestation->setInformation(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Pathologie[]
     */
    public function getPathologies(): Collection
    {
        return $this->pathologies;
    }

    public function addPathology(Pathologie $pathology): self
    {
        if (!$this->pathologies->contains($pathology)) {
            $this->pathologies[] = $pathology;
            $pathology->setInformation($this);
        }

        return $this;
    }

    public function removePathology(Pathologie $pathology): self
    {
        if ($this->pathologies->contains($pathology)) {
            $this->pathologies->removeElement($pathology);
            // set the owning side to null (unless already changed)
            if ($pathology->getInformation() === $this) {
                $pathology->setInformation(null);
            }
        }

        return $this;
    }

}
