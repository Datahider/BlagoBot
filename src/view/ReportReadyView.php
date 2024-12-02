<?php

namespace losthost\BlagoBot\view;

class ReportReadyView extends ReportResultView {
    protected function getExporter(): \losthost\BlagoBot\service\Exporter {
        return new \losthost\BlagoBot\service\ExporterReady();
    }
}
