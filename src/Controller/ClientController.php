<?php
namespace App\Controller;

use App\Entity\Client;
use App\Repository\ClientRepository;
use App\Service\ClientAccessKeyService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ClientController extends AbstractController{
	/**
	 * @Route("/admin/client", name="create_client", methods={"POST"})
	 */
	public function create(Request $request, ClientAccessKeyService $keyService){
		$name = $request->request->get('name');
		if($name){
			$manager = $this->getDoctrine()->getManager();
			$client = new Client();
			$client->setName($name);
			$manager->persist($client);
			$keyService->addKeyToClient($client, $manager);
		}
		return $this->redirectToRoute($request->request->get('_redirect'));
	}

	/**
	 * @Route("/admin/client/delete/{uuid}/{redirect}", name="delete_client", methods={"GET"})
	 */
	public function delete(string $uuid, string $redirect, ClientRepository $clientRepository){
		$client = $clientRepository->findOneBy(['uuid'=>$uuid]);
		if($client){
			$manager = $this->getDoctrine()->getManager();
			$client->setIsDeleted(true);
			$manager->persist($client);
			$manager->flush();
		}
		return $this->redirectToRoute($redirect);
	}
}