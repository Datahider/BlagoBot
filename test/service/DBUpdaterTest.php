<?php
namespace losthost\BlagoBot\service;

use PHPUnit\Framework\TestCase;

class DBUpdaterTest extends TestCase {
    
    public function testLoadDB() {
    
        $file_path = 'c:/forLoad1.xlsx';
        $updater = new DBUpdater();
        
        $updater->update($file_path);
    }
}
