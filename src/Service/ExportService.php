<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Response;
use Dompdf\Dompdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExportService
{
    public function generateExport(array $entreprises, string $format): Response
    {
        if ($format === 'pdf') {
            return $this->generatePdf($entreprises);
        } elseif ($format === 'excel') {
            return $this->generateExcel($entreprises);
        }

        throw new \Exception('Format non supporté.');
    }

    private function generatePdf(array $entreprises): Response
    {
        $dompdf = new Dompdf();
        $html = "<h1>Listing des entreprises</h1><ul>";

        foreach ($entreprises as $entreprise) {
            $html .= "<li>{$entreprise->getRaisonSociale()} - {$entreprise->getSecteurActivite()}</li>";
        }
        $html .= "</ul>";

        $dompdf->loadHtml($html);
        $dompdf->render();

        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="entreprises.pdf"',
        ]);
    }

    private function generateExcel(array $entreprises): Response
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Nom de l\'entreprise');
        $sheet->setCellValue('B1', 'Secteur d\'activité');

        $row = 2;
        foreach ($entreprises as $entreprise) {
            $sheet->setCellValue('A' . $row, $entreprise->getRaisonSociale());
            $sheet->setCellValue('B' . $row, $entreprise->getSecteurActivite());
            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        $filePath = 'entreprises.xlsx';
        $writer->save($filePath);

        return new Response(file_get_contents($filePath), 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="entreprises.xlsx"',
        ]);
    }
}
