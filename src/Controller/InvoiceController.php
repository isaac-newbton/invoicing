<?php
namespace App\Controller;

use App\Entity\Invoice;
use App\Entity\InvoiceItem;
use App\Form\InvoiceItemType;
use App\Repository\ClientRepository;
use App\Repository\InvoiceItemRepository;
use App\Repository\InvoiceRepository;
use App\Service\InvoiceFileService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Mime\FileinfoMimeTypeGuesser;
use Symfony\Component\Routing\Annotation\Route;

class InvoiceController extends AbstractController{
	/**
	 * @Route("/admin/invoice/client/{clientUuid}", name="invoices_for_client")
	 */
	public function invoicesForClient(string $clientUuid, ClientRepository $clientRepository){
		$client = $clientRepository->findOneBy(['uuid'=>$clientUuid]);
		if($client){
			return $this->render('admin/invoice/client.html.twig', ['client'=>$client]);
		}
		return $this->redirectToRoute('admin_dashboard', [], 404);
	}

	/**
	 * @Route("/admin/invoice/create/{clientUuid}", name="create_invoice", methods={"GET","POST"})
	 */
	public function createInvoice(string $clientUuid, ClientRepository $clientRepository, Request $request){
		$client = $clientRepository->findOneBy(['uuid'=>$clientUuid]);
		if($client){
			if($request->isMethod('GET')){
				return $this->render('admin/invoice/create.html.twig', ['client'=>$client]);
			}else if($request->isMethod('POST')){
				$name = $request->request->get('name');
				$description = $request->request->get('description');
				$date = $request->request->get('date');
				$periodStart = $request->request->get('periodStart');
				$periodEnd = $request->request->get('periodEnd');
				if($name && $periodEnd && $periodStart){
					$manager = $this->getDoctrine()->getManager();
					$invoice = new Invoice();
					$invoice->setName($name);
					$invoice->setPeriodStart(new \DateTime($periodStart));
					$invoice->setPeriodEnd(new \DateTime($periodEnd));
					if($description) $invoice->setDescription($description);
					if($date) $invoice->setDate(new \DateTime($date));
					$client->addInvoice($invoice);
					$manager->persist($invoice);
					$manager->flush();
					return $this->redirectToRoute('edit_invoice', [
						'uuid'=>$invoice->getUuid()
					], 201);
				}
				return $this->redirectToRoute($request->request->get('_redirect'));
			}
		}
		return $this->redirectToRoute('admin_dashboard', [], 404);
	}

	/**
	 * @Route("/admin/invoice/{uuid}", name="edit_invoice")
	 */
	public function editInvoice(string $uuid, InvoiceRepository $invoiceRepository){
		$invoice = $invoiceRepository->findOneBy(['uuid'=>$uuid]);
		if($invoice){
			return $this->render('admin/invoice/edit.html.twig', ['invoice'=>$invoice]);
		}
		return $this->redirectToRoute('admin_dashboard', [], 404);
	}

	/**
	 * @Route("/admin/invoice/{uuid}/cancel/{cancel}", name="cancel_invoice")
	 */
	public function cancelInvoice(string $uuid, InvoiceRepository $invoiceRepository, int $cancel = 1){
		$invoice = $invoiceRepository->findOneBy(['uuid'=>$uuid]);
		if($invoice){
			$manager = $this->getDoctrine()->getManager();
			$invoice->setIsCanceled((bool)$cancel);
			$manager->persist($invoice);
			$manager->flush();
			return $this->redirectToRoute('edit_invoice', ['uuid'=>$uuid]);
		}
		return $this->redirectToRoute('admin_dashboard', [], 404);
	}

	/**
	 * @Route("/admin/invoice/{uuid}/complete/{complete}", name="complete_invoice")
	 */
	public function completeInvoice(string $uuid, InvoiceRepository $invoiceRepository, int $complete = 1){
		$invoice = $invoiceRepository->findOneBy(['uuid'=>$uuid]);
		if($invoice){
			$manager = $this->getDoctrine()->getManager();
			$invoice->setIsCompleted((bool)$complete);
			$manager->persist($invoice);
			$manager->flush();
			return $this->redirectToRoute('edit_invoice', ['uuid'=>$uuid]);
		}
		return $this->redirectToRoute('admin_dashboard', [], 404);
	}

	/**
	 * @Route("/admin/invoice/create_item/{uuid}", name="create_invoice_item", methods={"POST"})
	 */
	public function createItem(string $uuid, InvoiceRepository $invoiceRepository, Request $request){
		$invoice = $invoiceRepository->findOneBy(['uuid'=>$uuid]);
		if($invoice){
			$name = $request->request->get('name');
			$description = $request->request->get('description');
			$quantity = $request->request->get('quantity');
			$unitPrice = $request->request->get('unitPrice');
			$url = $request->request->get('url');
			if($name){
				$item = new InvoiceItem();
				$manager = $this->getDoctrine()->getManager();
				$item->setName($name);
				if($description) $item->setDescription(nl2br($description));
				if($url) $item->setUrl($url);
				$item->setQuantity(number_format($quantity, 2, '.', ''));
				$item->setUnitPrice(number_format($unitPrice, 2, '.', ''));
				$invoice->addItem($item);
				$manager->persist($item);
				$manager->flush();
			}
		}
		return $this->redirectToRoute('edit_invoice', ['uuid'=>$uuid]);
	}

	/**
	 * @Route("/admin/invoice/delete_item/{uuid}/{redirect}", name="delete_invoice_item", methods={"GET"})
	 */
	public function deleteItem(string $uuid, string $redirect, InvoiceItemRepository $invoiceItemRepository){
		$item = $invoiceItemRepository->findOneBy(['uuid'=>$uuid]);
		if($item){
			$manager = $this->getDoctrine()->getManager();
			$item->setIsDeleted(true);
			$manager->persist($item);
			$manager->flush();
			return $this->redirectToRoute('edit_invoice', ['uuid'=>$item->getInvoice()->getUuid()]);
		}
		return $this->redirectToRoute($redirect);
	}

	/**
	 * @Route("/client/{uuid}/invoices", name="client_view_invoices", methods={"GET"})
	 */
	public function clientViewInvoices(string $uuid, ClientRepository $clientRepository){
		$client = $clientRepository->findOneBy(['uuid'=>$uuid]);
		if(!$client){
			return new Response("<html><body>That data is not available.</body></html>", 404);
		}
		if(0===count($client->getInvoices())){
			return new Response("<html><body>That client has no invoices.</body></html>", 409);
		}
		return $this->render('public/client_invoices.html.twig', ['client'=>$client]);
	}

	/**
	 * @Route("/client/{clientUuid}/invoice/{invoiceUuid}", name="client_view_invoice", methods={"GET"})
	 */
	public function clientViewInvoice(string $clientUuid, string $invoiceUuid, ClientRepository $clientRepository, InvoiceRepository $invoiceRepository){
		$client = $clientRepository->findOneBy(['uuid'=>$clientUuid]);
		$invoice = $invoiceRepository->findOneBy(['uuid'=>$invoiceUuid]);
		if(!$client || !$invoice){
			return new Response("<html><body>That invoice was not found.</body></html>", 404);
		}
		if($invoice->getClient()!=$client){
			return new Response("<html><body>Permission to view that invoice is blocked.</body></html>", 403);
		}
		if($invoice->getIsCanceled()){
			return new Response("<html><body>That invoice has been marked canceled.</body></html>", 410);
		}
		if(0===count($invoice->getItems())){
			return new Response("<html><body>That invoice is empty.</body></html>", 409);
		}
		return $this->render('public/invoice.html.twig', ['invoice'=>$invoice]);
	}

	/**
	 * @Route("/client/{clientUuid}/invoice/{invoiceUuid}/download", name="client_download_invoice", methods={"GET"})
	 */
	public function clientDownloadInvoice(string $clientUuid, string $invoiceUuid, ClientRepository $clientRepository, InvoiceRepository $invoiceRepository, InvoiceFileService $invoiceFileService){
		$client = $clientRepository->findOneBy(['uuid'=>$clientUuid]);
		$invoice = $invoiceRepository->findOneBy(['uuid'=>$invoiceUuid]);
		if(!$client || !$invoice){
			return new Response("<html><body>That invoice was not found.</body></html>", 404);
		}
		if($invoice->getClient()!=$client){
			return new Response("<html><body>Permission to view that invoice is blocked.</body></html>", 403);
		}
		if($invoice->getIsCanceled()){
			return new Response("<html><body>That invoice has been marked canceled.</body></html>", 410);
		}
		if(0===count($invoice->getItems())){
			return new Response("<html><body>That invoice is empty.</body></html>", 409);
		}

		try{
			$filePath = $invoiceFileService->generateCsv($invoice);
			$response = new BinaryFileResponse($filePath);
			$mime = new FileinfoMimeTypeGuesser();
			if($mime->isGuesserSupported()){
				$response->headers->set('Content-Type', $mime->guessMimeType($filePath));
			}else{
				$response->headers->set('Content-Type', 'text/plain');
			}
			$response->setContentDisposition(
				ResponseHeaderBag::DISPOSITION_ATTACHMENT,
				"invoice_{$invoice->getDate()->format('Y-m-d')}_{$invoice->getUuid()}.csv"
			);
			return $response;
		}catch(IOException $exception){
			return new Response("<html><body>IOException: {$exception->getMessage()}</body></html>");
		}
	}

	/**
	 * @Route("/admin/invoice/edit_item/{uuid}", name="edit_invoice_item")
	 */
	public function editItem(string $uuid, Request $request, InvoiceItemRepository $invoiceItemRepository){
		/**
		 * @var InvoiceItem|null
		 */
		$item = $invoiceItemRepository->findOneBy(['uuid'=>$uuid]);
		if(!$item){
			return $this->redirectToRoute('admin_dashboard');
		}

		$form = $this->createForm(InvoiceItemType::class, $item);
		$form->handleRequest($request);
		if($form->isSubmitted() && $form->isValid()){
			$item = $form->getData();
			$manager = $this->getDoctrine()->getManager();
			$manager->persist($item);
			$manager->flush();
			return $this->redirectToRoute('edit_invoice', ['uuid'=>$item->getInvoice()->getUuid()]);
		}

		return $this->render('admin/invoice/edit_item.html.twig', ['form'=>$form->createView()]);
	}
}