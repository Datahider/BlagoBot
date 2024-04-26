<?php

namespace losthost\BlagoBot\service;

use PHPUnit\Framework\TestCase;

class DBUpdater2Test extends TestCase {
    
    public function testLoadDB() {
        $file_path = 'c:/forLoad2.xlsx';
        $updater = new DBUpdater2();
        
        $updater->update($file_path);
    }
}
