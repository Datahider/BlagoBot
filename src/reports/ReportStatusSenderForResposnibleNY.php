<?php

namespace losthost\BlagoBot\reports;

class ReportStatusSenderForResposnibleNY extends ReportStatusSenderForResponsible {
    protected function getCurrentYear() {
        return date('Y') + 1;
    }
}
