<?php
namespace App\Controller;

use App\Repository\ClientRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController{
	/**
	 * @Route("/admin/dashboard", name="admin_dashboard")
	 */
	public function dashboard(ClientRepository $clientRepository){



		return $this->render('admin/dashboard.html.twig', [
			'clients'=>$clientRepository->findBy([
				'isDeleted'=>false,
				'isArchived'=>false
			], ['name'=>'ASC'])
		]);
	}
}