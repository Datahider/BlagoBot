<?php

namespace losthost\BlagoBot\data;

use losthost\DB\DBObject;

class log_report extends DBObject {
    
    protected int $started;
    
    const METADATA = [
        'id' => 'BIGINT NOT NULL AUTO_INCREMENT',
        'user_id' => 'BIGINT NOT NULL',
        'report_class' => 'VARCHAR(1024) NOT NULL',
        'time_start' => 'DATETIME NOT NULL',
        'processing_time' => 'BIGINT',
        'PRIMARY KEY' => 'id'
    ];
    
    static public function log_start(int $user_id, string $report_class) {
        
        $me = new log_report();
        $me->started = hrtime(true);
        
        $me->user_id = $user_id;
        $me->report_class = $report_class;
        $me->time_start = new \DateTimeImmutable();
        $me->write();
        
        return $me;
    }
    
    public function log_stop() {
        $this->processing_time = hrtime(true) - $this->started;
        $this->write();
    }
    
}
