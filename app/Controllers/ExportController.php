<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Services\FormService;
use App\Services\ResponseService;

class ExportController
{
    public function csv(Request $request, array $params): void
    {
        $formService = new FormService();
        $responseService = new ResponseService();

        $form = $formService->getById($params['id']);
        if (!$form) Response::notFound('Form not found');

        $schema = json_decode($form['schema'] ?? '{}', true);
        $questions = $schema['questions'] ?? [];
        $responses = $responseService->getAllForExport($params['id']);

        // Build CSV
        $headers = ['#', 'Timestamp'];
        foreach ($questions as $q) {
            if (($q['type'] ?? '') !== 'section') {
                $headers[] = $q['title'] ?? 'Question';
            }
        }

        $output = fopen('php://temp', 'r+');
        fputcsv($output, $headers);

        $row = 1;
        foreach ($responses as $resp) {
            $answers = json_decode($resp['answers'] ?? '{}', true);
            $line = [$row++, $resp['submitted_at'] ?? $resp['created_at'] ?? ''];

            foreach ($questions as $q) {
                if (($q['type'] ?? '') === 'section') continue;
                $val = $answers[$q['id'] ?? ''] ?? '';
                $line[] = is_array($val) ? implode(', ', $val) : $val;
            }

            fputcsv($output, $line);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        $filename = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $form['title'] ?? 'responses') . '_' . date('Y-m-d') . '.csv';
        Response::download($csv, $filename, 'text/csv');
    }

    public function excel(Request $request, array $params): void
    {
        $formService = new FormService();
        $responseService = new ResponseService();

        $form = $formService->getById($params['id']);
        if (!$form) Response::notFound('Form not found');

        $schema = json_decode($form['schema'] ?? '{}', true);
        $questions = $schema['questions'] ?? [];
        $responses = $responseService->getAllForExport($params['id']);

        try {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Responses');

            // Headers
            $col = 1;
            $sheet->setCellValueByColumnAndRow($col++, 1, '#');
            $sheet->setCellValueByColumnAndRow($col++, 1, 'Timestamp');
            foreach ($questions as $q) {
                if (($q['type'] ?? '') !== 'section') {
                    $sheet->setCellValueByColumnAndRow($col++, 1, $q['title'] ?? 'Question');
                }
            }

            // Style header row
            $headerRange = 'A1:' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col - 1) . '1';
            $sheet->getStyle($headerRange)->getFont()->setBold(true);
            $sheet->getStyle($headerRange)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF6366F1');
            $sheet->getStyle($headerRange)->getFont()->getColor()->setARGB('FFFFFFFF');

            // Data rows
            $rowNum = 2;
            $idx = 1;
            foreach ($responses as $resp) {
                $answers = json_decode($resp['answers'] ?? '{}', true);
                $col = 1;
                $sheet->setCellValueByColumnAndRow($col++, $rowNum, $idx++);
                $sheet->setCellValueByColumnAndRow($col++, $rowNum, $resp['submitted_at'] ?? $resp['created_at'] ?? '');

                foreach ($questions as $q) {
                    if (($q['type'] ?? '') === 'section') continue;
                    $val = $answers[$q['id'] ?? ''] ?? '';
                    $sheet->setCellValueByColumnAndRow($col++, $rowNum, is_array($val) ? implode(', ', $val) : $val);
                }
                $rowNum++;
            }

            // Auto-size columns
            foreach (range(1, $col - 1) as $c) {
                $sheet->getColumnDimensionByColumn($c)->setAutoSize(true);
            }

            // Output
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $filename = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $form['title'] ?? 'responses') . '_' . date('Y-m-d') . '.xlsx';

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
            exit;

        } catch (\Exception $e) {
            // Fallback to CSV if PhpSpreadsheet fails
            $this->csv($request, $params);
        }
    }
}
