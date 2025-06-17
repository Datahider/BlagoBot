<?php

namespace losthost\BlagoBot\reports;

class ReportStatusSenderTotalNY extends ReportStatusSenderTotal {
    #[\Override]
    protected function getCurrentYear() {
        return date('Y') + 1;
    }
}
