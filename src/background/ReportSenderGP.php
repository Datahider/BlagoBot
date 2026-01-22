<?php

namespace losthost\BlagoBot\background;

class ReportSenderGP extends AbstractReportSender {
    
    #[\Override]
    protected function reportParamSetup(): array {
        return [
            'year' => ["2026"],
            'sources' => [],
        ];
    }
}
