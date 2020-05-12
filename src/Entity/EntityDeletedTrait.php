<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

trait EntityDeletedTrait{
	/**
	 * @ORM\Column(type="boolean")
	 */
	private $isDeleted;

	public function getIsDeleted(): ?bool
	{
		return $this->isDeleted;
	}

	public function setIsDeleted(bool $isDeleted): self
	{
		$this->isDeleted = $isDeleted;

		return $this;
	}
}