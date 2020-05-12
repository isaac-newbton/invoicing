<?php
namespace App\Service;

use App\Entity\Client;
use App\Entity\ClientAccessKey;
use Doctrine\ORM\EntityManagerInterface;

class ClientAccessKeyService{
	public function addKeyToClient(Client $client, EntityManagerInterface $manager){
		$accessKey = new ClientAccessKey();
		$accessKey->setName("New key for {$client->getName()}");
		$client->addAccessKey($accessKey);
		$manager->persist($accessKey);
		$manager->flush();
		return $accessKey;
	}
}