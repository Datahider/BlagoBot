<?php

namespace losthost\BlagoBot\view;

use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class ReportAdminPdf extends ReportAdminView {
    
    protected function getWriter($spreadsheet) {
        $writer = new Mpdf($spreadsheet);
        $writer->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
        return $writer;
    }
    
    protected function getReportMimeType() : string {
        return 'application/pdf';
    }
    
    protected function getReportFileName() : string {
        return 'report.pdf';
    }
    
}
