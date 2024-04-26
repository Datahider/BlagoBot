<?php

namespace losthost\BlagoBot\service;

use PhpOffice\PhpSpreadsheet\Spreadsheet;

class Exporter {
    
    public function export($result) {
    
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->fromArray([$result->columns]);
        $sheet->fromArray($result->data, NULL, 'A2');
        
        return $spreadsheet;
    }
}
