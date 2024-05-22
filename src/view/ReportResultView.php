<?php

namespace losthost\BlagoBot\view;

use losthost\BlagoBot\reports\AbstractReport;
use losthost\telle\Bot;
use losthost\BotView\BotView;
use losthost\BlagoBot\service\Exporter;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use CURLFile;
use stdClass;

class ReportResultView {
    
    protected AbstractReport $report;
    protected stdClass $result;
    
    public function __construct(AbstractReport $report) {
        $this->report = $report;
        $this->result = $this->report->build();
    }
    
    public function show(?int $message_id=null) {
        $view = new BotView(Bot::$api, Bot::$chat->id, Bot::$language_code);
        
        if ($this->result->ok) {
            $view->show('tpl_report_view', null, ['result' => $this->result], $message_id);
        } else {
            $view->show('tpl_report_view', null, ['result' => $this->result], $message_id);
        }
        
        if ($this->result->result_type == AbstractReport::RESULT_TYPE_XLSX) {
            $exporter = $this->getExporter();
            $spreadsheet = $exporter->export($this->result);
            
            $writer = $this->getWriter($spreadsheet);
            
            $tmp_file = tempnam('/tmp', 'Rpt');
            $writer->save($tmp_file);
            
            $file_to_send = new \CURLFile($tmp_file, $this->getReportMimeType(), $this->getReportFileName());
            Bot::$api->sendDocument(Bot::$chat->id, $file_to_send, 'Результат отчета');
            $file_to_send = null;
            unlink($tmp_file);
        }
        
        Bot::$session->set('command', null);
    }
    
    protected function getExporter() {
        return new Exporter();
    }
    
    protected function getWriter($spreadsheet) {
        $writer = new Mpdf($spreadsheet);
        $writer->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
        return $writer;
    }
    
    protected function getReportMimeType() {
        return 'application/pdf';
    }
    
    protected function getReportFileName() {
        return 'report.pdf';
    }
}
