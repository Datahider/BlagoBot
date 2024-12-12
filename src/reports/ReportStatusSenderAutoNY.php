<?php

namespace losthost\BlagoBot\reports;

class ReportStatusSenderAutoNY extends ReportStatusSenderAuto {
    protected function getCurrentYear() {
        return date('Y') + 1;
    }
}
