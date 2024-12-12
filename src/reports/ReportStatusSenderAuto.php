<?php

namespace losthost\BlagoBot\reports;

class ReportStatusSenderAuto extends ReportStatusSender {
    protected function initParams() {
        parent::initParams();
        array_splice($this->params, 2, 1);
    }
}
