<?php

class processFile {
	
	private $arrFileData;
	private $arrFinalData;
	private $totalAmount;
	private $arrAveragePerPaymentMethod;
	private $arrPaymentMethods;
	
	public function __construct($file) {
		
		$filename = $file["file"]["name"];
		$filepath = $file["file"]["tmp_name"];
		
		$this->arrPaymentMethods = ["00" => "Efectivo", "90" => "Tarjeta Debito", "99" => "Tarjeta Credito"]; //Medios de pago para el archivo 888ENTES*
		
		if(mime_content_type($filepath) === "text/plain") { //Que sea archivo de texto
		
			$filePrefix = substr($filename, 0, 8);
			$this->process($filepath, $filePrefix);
		
		}

	}
	
	private function parseFile($filepath) {
		
		$this->arrFileData = file($filepath);
		
	}
	
	private function process888ENTES($arrFileData) { //Para archivo 888ENTES***

		$this->arrAveragePerPaymentMethod = array();
		
		foreach($this->arrFileData as $index => $line) {

			if(substr($line, 0, 5) === "DATOS" && strlen($line) >= 5) {

				$transactionNum = substr($line, 40, 8); //Transacción  N8
				$amount = number_format(substr($line, 77, 11) / 100, 2, '.', ''); //Importe N11,2
				$identifier = substr($line, 58, 19); //Cod. Servicio / Identificacion C19
				$paymentDate = substr($line, 224, 6); //Fecha pago AAMMDD
				$paymentMethod = $this->arrPaymentMethods[substr($line, 247, 2)]; //Forma pago N2

				$this->arrFinalData["data"][] = [
					"transactionNum" => $transactionNum,
					"amount" => $amount,
					"identifier" => $identifier,
					"paymentDate" => $paymentDate,
					"paymentMethod" => $paymentMethod,
				];
				
				$this->arrAveragePerPaymentMethod[$paymentMethod][] = $amount;

			}
		
		}
		
		$footer = $this->arrFileData[array_key_last($this->arrFileData)];
		$this->arrFinalData["totals"]["count"] = (int)substr($footer, 7, 8) - 2; //Resto header y footer
		$this->totalAmount = number_format((int)(substr($footer, 15, 13)) / 100, 2, '.', '');
		
	}
	
	private function processRENDCOBREV($arrFileData) { //Para archivos REND.COB*** y REND.REV***

		$this->arrAveragePerPaymentMethod = array();
		$header = $this->arrFileData[array_key_first($this->arrFileData)];
		
		foreach($this->arrFileData as $index => $line) {

			if(substr($line, 0, 4) !== "0000" && substr($line, 0, 4) !== "9999" && strlen($line) >= 5) {

				$transactionNum = substr($header, 4, 4); //Nro. de Prestacion
				$amount = number_format((int)(substr($line, 75, 14)) / 100, 2, '.', ''); //Importe 1er vencimiento o Imp. Original
				$identifier = substr($line, 17, 1); //Identificacion de Archivo
				$paymentDate = substr($line, 67, 8); //Fecha 1er vencimiento o Venc. Original
				$paymentMethod = "CBU"; //Parece ser siempre CBU para estos 2 archivos

				$this->arrFinalData["data"][] = [
					"transactionNum" => $transactionNum,
					"amount" => $amount,
					"identifier" => $identifier,
					"paymentDate" => $paymentDate,
					"paymentMethod" => $paymentMethod,
				];
				
				$this->arrAveragePerPaymentMethod[$paymentMethod][] = $amount;

			}
		
		}
		
		$footer = $this->arrFileData[array_key_last($this->arrFileData)];
		$this->arrFinalData["totals"]["count"] = (int)substr($footer, 39, 7);
		$this->totalAmount = number_format((int)(substr($footer, 25, 14)) / 100, 2, '.', '');
		
	}
	
	private function process($filepath, $filePrefix) {
		
		$this->parseFile($filepath);
		
		if(strlen($this->arrFileData[array_key_last($this->arrFileData)]) < 5) { //Ultima fila vacia se elimina
			array_pop($this->arrFileData);
		}
		
		$this->arrFinalData = array();
		$this->arrFinalData["data"] = array();
		$this->arrFinalData["totals"] = array();
		
		if($filePrefix === "888ENTES") {
			
			$this->process888ENTES($this->arrFileData);
			
		} elseif($filePrefix === "REND.COB" || $filePrefix === "REND.REV") {
			
			$this->processRENDCOBREV($this->arrFileData);
			
		}
		
		$this->arrFinalData["totals"]["totalAmount"] = $this->totalAmount;
		
		foreach($this->arrAveragePerPaymentMethod as $paymentMethod => $amounts) {
			
			$this->arrFinalData["totals"]["perPaymentMethod"][$paymentMethod] = number_format(array_sum($amounts) / count($amounts), 2, '.', '');
			
		}
		
	}
	
	public function returnData() {
		
		return $this->arrFinalData;
		
	}
	
}