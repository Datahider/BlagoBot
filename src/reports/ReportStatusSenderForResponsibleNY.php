<?php

namespace losthost\BlagoBot\reports;

class ReportStatusSenderForResponsibleNY extends ReportStatusSenderForResponsible {
    protected function getCurrentYear() {
        return date('Y') + 1;
    }
}
