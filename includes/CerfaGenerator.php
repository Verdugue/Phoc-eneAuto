<?php
require_once __DIR__ . '/../vendor/autoload.php';
use setasign\Fpdi\Fpdi;

class CerfaGenerator {
    private $pdf;
    private $template_path;

    public function __construct() {
        $this->pdf = new Fpdi();
        $this->template_path = __DIR__ . '/../templates/cerfa_15776-01.pdf';
    }

    public function generate($transaction, $seller, $buyer, $vehicle) {
        // Ajouter le template du cerfa
        $this->pdf->setSourceFile($this->template_path);
        $tplIdx = $this->pdf->importPage(1);
        $this->pdf->AddPage();
        $this->pdf->useTemplate($tplIdx);

        $this->pdf->SetFont('Helvetica', '', 10);

        // Informations du vendeur (A)
        $this->pdf->SetXY(20, 45);
        $this->pdf->Write(0, $seller['name']);
        $this->pdf->SetXY(20, 55);
        $this->pdf->Write(0, $seller['address']);
        
        // Informations de l'acheteur (B)
        $this->pdf->SetXY(20, 95);
        $this->pdf->Write(0, $buyer['first_name'] . ' ' . $buyer['last_name']);
        $this->pdf->SetXY(20, 105);
        $this->pdf->Write(0, $buyer['address']);

        // Informations du véhicule (C)
        $this->pdf->SetXY(20, 150);
        $this->pdf->Write(0, $vehicle['brand'] . ' ' . $vehicle['model']);
        $this->pdf->SetXY(20, 160);
        $this->pdf->Write(0, $vehicle['vin_number']);
        $this->pdf->SetXY(20, 170);
        $this->pdf->Write(0, $vehicle['registration_number']);

        // Date et signatures (D)
        $date = date('d/m/Y', strtotime($transaction['transaction_date']));
        $this->pdf->SetXY(20, 200);
        $this->pdf->Write(0, "Fait le " . $date);

        return $this->pdf;
    }

    public function output($filename) {
        $this->pdf->Output('D', $filename);
    }
} 