<?php
namespace App\Service;

use App\Entity\Invoice;
use Symfony\Component\Filesystem\Filesystem;

class InvoiceFileService{
	public function generateCsv(Invoice $invoice){
		$path = "invoices/{$invoice->getDate()->format('Y-m-d')}_{$invoice->getUuid()}.csv";

		$cellSeparator = ',';
		$rowSeparator = PHP_EOL;
		$rows = [];

		$rows[] = "Invoice Date$cellSeparator" . $invoice->getDate()->format('Y-m-d');
		$rows[] = "Interval Start$cellSeparator" . $invoice->getPeriodStart()->format('Y-m-d');
		$rows[] = "Interval End$cellSeparator" . $invoice->getPeriodEnd()->format('Y-m-d');
		$rows[] = "";
		$rows[] = "Job{$cellSeparator}Description{$cellSeparator}Qty/Hrs{$cellSeparator}Rate{$cellSeparator}Amount";
		foreach($invoice->getItems() as $item){
			$rows[] = "\"{$item->getName()}\"" . $cellSeparator . "\"" . strip_tags($item->getDescription()) . "\"" . $cellSeparator . number_format($item->getQuantity(), 2, '.', '') . $cellSeparator . '$' . number_format($item->getUnitPrice(), 2, '.', '') . $cellSeparator . '$' . $item->getLineTotal();
		}
		$rows[] = "{$cellSeparator}{$cellSeparator}{$cellSeparator}{$cellSeparator}$" . $invoice->getSubtotal();

		$text = '';
		foreach($rows as $row){
			$text .= "{$row}{$rowSeparator}";
		}
		$text = trim($text);

		$filesystem = new Filesystem();
		$filesystem->dumpFile($path, $text);

		return $path;
	}
}