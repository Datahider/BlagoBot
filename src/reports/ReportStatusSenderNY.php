<?php

namespace losthost\BlagoBot\reports;

/**
 * Description of ReportStatusSenderNY
 *
 * @author web
 */
class ReportStatusSenderNY extends ReportStatusSender {
    protected function getCurrentYear() {
        return date('Y') + 1;
    }
}
