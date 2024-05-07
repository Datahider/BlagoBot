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
            $exporter = new Exporter();
            $spreadsheet = $exporter->export($this->result);
            $writer = IOFactory::createWriter($spreadsheet, IOFactory::WRITER_XLSX);
            $writer = new Mpdf($spreadsheet);
            
            $writer->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
            
            $tmp_dir = tempnam('/tmp', 'Rpt');
            unlink($tmp_dir);
            mkdir($tmp_dir);
            $report_file = "$tmp_dir/report.xlsx";
            $report_file = "$tmp_dir/report.pdf";
            $writer->save($report_file);
            
            $file_to_send = new \CURLFile($report_file);
            Bot::$api->sendDocument(Bot::$chat->id, $file_to_send, 'Результат отчета');
            $file_to_send = null;
            unlink($report_file);
        }
        
        Bot::$session->set('command', null);
    }
}
