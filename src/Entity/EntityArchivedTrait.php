<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

trait EntityArchivedTrait{
	/**
	 * @ORM\Column(type="boolean")
	 */
	private $isArchived;

	public function getIsArchived(): ?bool
	{
		return $this->isArchived;
	}

	public function setIsArchived(bool $isArchived): self
	{
		$this->isArchived = $isArchived;

		return $this;
	}
}