<?php

namespace losthost\BlagoBot\background;

use losthost\telle\abst\AbstractBackgroundProcess;
use losthost\BotView\BotView;
use losthost\telle\Bot;

class PrologSender extends AbstractBackgroundProcess {
    
    #[\Override]
    public function run() {
    
        $view = new BotView(Bot::$api, $this->param);
        $view->show('auto_report_prolog');
        
    }
}
