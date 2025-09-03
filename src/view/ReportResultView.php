<?php

namespace losthost\BlagoBot\view;

use losthost\BlagoBot\reports\AbstractReport;
use losthost\telle\Bot;
use losthost\BotView\BotView;
use losthost\BlagoBot\service\Exporter;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use losthost\BlagoBot\data\log_report;
use CURLFile;
use stdClass;

use function \losthost\BlagoBot\sendSplitMessage;

class ReportResultView {
    
    protected AbstractReport $report;
    protected stdClass $result;
    protected log_report $log;
    
    public function __construct(AbstractReport $report) {
        global $b_user;
        
        $this->log = log_report::log_start($b_user->id, get_class($report));
        $this->report = $report;
        $this->result = $this->report->build();
    }
    
    public function show(?int $message_id=null) {
        $view = new BotView(Bot::$api, Bot::$chat->id, Bot::$language_code);
        
        if ($this->result->ok) {
            $view->show('tpl_report_view', null, ['result' => $this->result], $message_id);

            if ($this->result->result_type == AbstractReport::RESULT_TYPE_XLSX) {
                $exporter = $this->getExporter();
                $spreadsheet = $exporter->export($this->result);

                $writer = $this->getWriter($spreadsheet);

                $tmp_file = tempnam('/tmp', 'Rpt');
                $writer->save($tmp_file);

                $file_to_send = new \CURLFile($tmp_file, $this->getReportMimeType(), $this->getReportFileName());
                $send_file_result = Bot::$api->sendDocument(Bot::$chat->id, $file_to_send, 'Результат отчета');
                $this->result->file_id = $send_file_result->getDocument()->getFileId();
                $file_to_send = null;
                unlink($tmp_file);
            } elseif (is_string($this->result->result_type)) {
                if (is_a($this->result->result_type, AbstractCustomView::class, true)) {
                    $view = new $this->result->result_type($this->result);
                    $view->show();
                } else {
                    sendSplitMessage(Bot::$chat->id, "Не удалось отобразить результат отчета. Обратитесь к разработчику.");
                }
            }
        } else {
            $view->show('tpl_report_view', null, ['result' => $this->result], $message_id);
        }
        
        $this->log->log_stop();
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
    
    public function getResult() {
        return $this->result;
    }
}
