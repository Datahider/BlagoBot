<?php

namespace losthost\BlagoBot\background;

class ReportSenderOPZ extends AbstractReportSender {

    #[\Override]
    protected function reportParamSetup(): array {
        return [
            'filter' => ['>'],
        ];
    }
    
}
