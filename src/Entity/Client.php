<?php

namespace App\Entity;

use App\Repository\ClientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\Entity(repositoryClass=ClientRepository::class)
 */
class Client
{
    use EntityIdTrait;
    use EntityDeletedTrait;
    use EntityArchivedTrait;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity=ClientContact::class, mappedBy="client", orphanRemoval=true)
     */
    private $contacts;

    /**
     * @ORM\OneToMany(targetEntity=ClientAccessKey::class, mappedBy="client", orphanRemoval=true)
     */
    private $accessKeys;

    /**
     * @ORM\OneToMany(targetEntity=Invoice::class, mappedBy="client", orphanRemoval=true)
     * @ORM\OrderBy({"date" = "DESC"})
     */
    private $invoices;

    public function __construct()
    {
        $this->contacts = new ArrayCollection();
        $this->accessKeys = new ArrayCollection();
        $this->invoices = new ArrayCollection();
        $this->uuid = Uuid::uuid4();
        $this->isDeleted = false;
        $this->isArchived = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection|ClientContact[]
     */
    public function getContacts(): Collection
    {
        return $this->contacts;
    }

    public function addContact(ClientContact $contact): self
    {
        if (!$this->contacts->contains($contact)) {
            $this->contacts[] = $contact;
            $contact->setClient($this);
        }

        return $this;
    }

    public function removeContact(ClientContact $contact): self
    {
        if ($this->contacts->contains($contact)) {
            $this->contacts->removeElement($contact);
            // set the owning side to null (unless already changed)
            if ($contact->getClient() === $this) {
                $contact->setClient(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ClientAccessKey[]
     */
    public function getAccessKeys(): Collection
    {
        return $this->accessKeys;
    }

    public function addAccessKey(ClientAccessKey $accessKey): self
    {
        if (!$this->accessKeys->contains($accessKey)) {
            $this->accessKeys[] = $accessKey;
            $accessKey->setClient($this);
        }

        return $this;
    }

    public function removeAccessKey(ClientAccessKey $accessKey): self
    {
        if ($this->accessKeys->contains($accessKey)) {
            $this->accessKeys->removeElement($accessKey);
            // set the owning side to null (unless already changed)
            if ($accessKey->getClient() === $this) {
                $accessKey->setClient(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Invoice[]
     */
    public function getInvoices(): Collection
    {
        return $this->invoices;
    }

    public function addInvoice(Invoice $invoice): self
    {
        if (!$this->invoices->contains($invoice)) {
            $this->invoices[] = $invoice;
            $invoice->setClient($this);
        }

        return $this;
    }

    public function removeInvoice(Invoice $invoice): self
    {
        if ($this->invoices->contains($invoice)) {
            $this->invoices->removeElement($invoice);
            // set the owning side to null (unless already changed)
            if ($invoice->getClient() === $this) {
                $invoice->setClient(null);
            }
        }

        return $this;
    }
}
