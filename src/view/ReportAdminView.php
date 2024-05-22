<?php

namespace losthost\BlagoBot\view;

use PhpOffice\PhpSpreadsheet\IOFactory;
use losthost\BlagoBot\service\ExporterAdmin;

class ReportAdminView extends ReportResultView {
    
    protected function getWriter($spreadsheet) {
        return IOFactory::createWriter($spreadsheet, IOFactory::WRITER_XLSX);
    }
    
    protected function getExporter() {
        return new ExporterAdmin();
    }
    
    protected function getReportMimeType(): string {
        return 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
    }
    
    protected function getReportFileName(): string {
        return 'report.xlsx';
    }
}
