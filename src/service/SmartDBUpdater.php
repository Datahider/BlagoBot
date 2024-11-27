<?php

namespace losthost\BlagoBot\service;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReader;

/**
 * Description of SmartDBUpdater
 * 
 * Открывает переданный файл, смотрит по названиям вкладок, что это за файл и 
 * вызывает соответствующих апдейтер. 
 * 
 * @author web
 */
class SmartDBUpdater {
    
    const WORKSHEET_NAME_1 = 'База текущая';
    const WORKSHEET_NAME_2 = 'База';
    const WORKSHEET_NAME_3 = 'Отчет1';
    
    const INCORRECT_FORMAT_EXCEPTION = 'Файл не соответствует ни одному из трех возможных форматов';

    public function update(string $file_path) {
    
        if (!file_exists($file_path)) {
            throw new \Exception('File not found: '. $file_path);
        }
    
        $spreadsheet = IOFactory::load($file_path, IReader::READ_DATA_ONLY, [IOFactory::READER_XLS, IOFactory::READER_XLSX]);

        $updater = new DBUpdater();
        try {
            $updater->update($spreadsheet);
            return;
        } catch (\Exception $ex) {
            if ($ex->getMessage() == DBUpdater::NOT_MY_FILE_EXCEPTION) {
                $updater = new DBUpdater2();
            } else {
                throw $ex;
            }
        }

        try {
            $updater->update($spreadsheet);
            return;
        } catch (\Exception $ex) {
            if ($ex->getMessage() == DBUpdater::NOT_MY_FILE_EXCEPTION) {
                $updater = new DBUpdater3();
            } else {
                throw $ex;
            }
        }

        try {
            $updater->update($spreadsheet);
            return;
        } catch (\Exception $ex) {
            if ($ex->getMessage() == DBUpdater::NOT_MY_FILE_EXCEPTION) {
                throw new \Exception(static::INCORRECT_FORMAT_EXCEPTION);
            } else {
                throw $ex;
            }
        }
        
    }
}
