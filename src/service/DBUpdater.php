<?php

namespace losthost\BlagoBot\service;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReader;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use losthost\BlagoBot\data\x_omsu;
use losthost\BlagoBot\data\x_object;
use losthost\BlagoBot\data\x_category;
use losthost\BlagoBot\data\x_year_data;
use losthost\BlagoBot\data\x_activity;
use losthost\BlagoBot\data\x_contract;
use losthost\BlagoBot\data\x_contract_data;
use losthost\BlagoBot\data\x_contragent;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use losthost\DB\DB;
use losthost\telle\model\DBBotParam;
use Exception;

use function \losthost\BlagoBot\__;

class DBUpdater {

    const WORKSHEET_NAME = 'База текущая';
    const GPSHEET_NAME = 'Госпрограмма';
    
    private $year_cells = [
        24 => ['year' => 0, 'type' => x_year_data::TYPE_SMR],
        25 => ['year' => 0, 'type' => x_year_data::TYPE_PIR],
        27 => ['year' => 0, 'type' => x_year_data::TYPE_LIMIT_FB],
        28 => ['year' => 0, 'type' => x_year_data::TYPE_LIMIT_BM],
        29 => ['year' => 0, 'type' => x_year_data::TYPE_LIMIT_BMO],
        30 => ['year' => 0, 'type' => x_year_data::TYPE_LIMIT_OMSU],

        31 => ['year' => 1, 'type' => x_year_data::TYPE_SMR],
        32 => ['year' => 1, 'type' => x_year_data::TYPE_PIR],
        34 => ['year' => 1, 'type' => x_year_data::TYPE_LIMIT_FB],
        35 => ['year' => 1, 'type' => x_year_data::TYPE_LIMIT_BM],
        36 => ['year' => 1, 'type' => x_year_data::TYPE_LIMIT_BMO],
        37 => ['year' => 1, 'type' => x_year_data::TYPE_LIMIT_OMSU],

        38 => ['year' => 2, 'type' => x_year_data::TYPE_SMR],
        39 => ['year' => 2, 'type' => x_year_data::TYPE_PIR],
        41 => ['year' => 2, 'type' => x_year_data::TYPE_LIMIT_FB],
        42 => ['year' => 2, 'type' => x_year_data::TYPE_LIMIT_BM],
        43 => ['year' => 2, 'type' => x_year_data::TYPE_LIMIT_BMO],
        44 => ['year' => 2, 'type' => x_year_data::TYPE_LIMIT_OMSU],

        45 => ['year' => 3, 'type' => x_year_data::TYPE_SMR],
        46 => ['year' => 3, 'type' => x_year_data::TYPE_PIR],
        48 => ['year' => 3, 'type' => x_year_data::TYPE_LIMIT_FB],
        49 => ['year' => 3, 'type' => x_year_data::TYPE_LIMIT_BM],
        50 => ['year' => 3, 'type' => x_year_data::TYPE_LIMIT_BMO],
        51 => ['year' => 3, 'type' => x_year_data::TYPE_LIMIT_OMSU],
    ];
    
    private $contract_cells = [
        54 => ['year' => 0, 'type' => x_contract_data::TYPE_RG_FB],
        55 => ['year' => 0, 'type' => x_contract_data::TYPE_RG_BM],
        56 => ['year' => 0, 'type' => x_contract_data::TYPE_RG_BMO],
        57 => ['year' => 0, 'type' => x_contract_data::TYPE_RG_OMSU],
        58 => ['year' => 0, 'type' => x_contract_data::TYPE_RG_OMSU2],
        
        60 => ['year' => 1, 'type' => x_contract_data::TYPE_RG_FB],
        61 => ['year' => 1, 'type' => x_contract_data::TYPE_RG_BM],
        62 => ['year' => 1, 'type' => x_contract_data::TYPE_RG_BMO],
        63 => ['year' => 1, 'type' => x_contract_data::TYPE_RG_OMSU],
        64 => ['year' => 1, 'type' => x_contract_data::TYPE_RG_OMSU2],
        
        66 => ['year' => 2, 'type' => x_contract_data::TYPE_RG_FB],
        67 => ['year' => 2, 'type' => x_contract_data::TYPE_RG_BM],
        68 => ['year' => 2, 'type' => x_contract_data::TYPE_RG_BMO],
        69 => ['year' => 2, 'type' => x_contract_data::TYPE_RG_OMSU],
        70 => ['year' => 2, 'type' => x_contract_data::TYPE_RG_OMSU2],
        
        76 => ['year' => 0, 'type' => x_contract_data::TYPE_NMCK_FB],
        77 => ['year' => 0, 'type' => x_contract_data::TYPE_NMCK_BM],
        78 => ['year' => 0, 'type' => x_contract_data::TYPE_NMCK_BMO],
        79 => ['year' => 0, 'type' => x_contract_data::TYPE_NMCK_OMSU],
        80 => ['year' => 0, 'type' => x_contract_data::TYPE_NMCK_OMSU2],
        
        82 => ['year' => 1, 'type' => x_contract_data::TYPE_NMCK_FB],
        83 => ['year' => 1, 'type' => x_contract_data::TYPE_NMCK_BM],
        84 => ['year' => 1, 'type' => x_contract_data::TYPE_NMCK_BMO],
        85 => ['year' => 1, 'type' => x_contract_data::TYPE_NMCK_OMSU],
        86 => ['year' => 1, 'type' => x_contract_data::TYPE_NMCK_OMSU2],
        
        88 => ['year' => 2, 'type' => x_contract_data::TYPE_NMCK_FB],
        89 => ['year' => 2, 'type' => x_contract_data::TYPE_NMCK_BM],
        90 => ['year' => 2, 'type' => x_contract_data::TYPE_NMCK_BMO],
        91 => ['year' => 2, 'type' => x_contract_data::TYPE_NMCK_OMSU],
        92 => ['year' => 2, 'type' => x_contract_data::TYPE_NMCK_OMSU2],
        
        98 => ['year' => 0, 'type' => x_contract_data::TYPE_CONTRACT_FB],
        99 => ['year' => 0, 'type' => x_contract_data::TYPE_CONTRACT_BM],
        100 => ['year' => 0, 'type' => x_contract_data::TYPE_CONTRACT_BMO],
        101 => ['year' => 0, 'type' => x_contract_data::TYPE_CONTRACT_OMSU],
        102 => ['year' => 0, 'type' => x_contract_data::TYPE_CONTRACT_OMSU2],
        
        104 => ['year' => 1, 'type' => x_contract_data::TYPE_CONTRACT_FB],
        105 => ['year' => 1, 'type' => x_contract_data::TYPE_CONTRACT_BM],
        106 => ['year' => 1, 'type' => x_contract_data::TYPE_CONTRACT_BMO],
        107 => ['year' => 1, 'type' => x_contract_data::TYPE_CONTRACT_OMSU],
        108 => ['year' => 1, 'type' => x_contract_data::TYPE_CONTRACT_OMSU2],
        
        110 => ['year' => 2, 'type' => x_contract_data::TYPE_CONTRACT_FB],
        111 => ['year' => 2, 'type' => x_contract_data::TYPE_CONTRACT_BM],
        112 => ['year' => 2, 'type' => x_contract_data::TYPE_CONTRACT_BMO],
        113 => ['year' => 2, 'type' => x_contract_data::TYPE_CONTRACT_OMSU],
        114 => ['year' => 2, 'type' => x_contract_data::TYPE_CONTRACT_OMSU2],
        
        116 => ['year' => 0, 'type' => x_contract_data::TYPE_ORDER_FB],
        117 => ['year' => 0, 'type' => x_contract_data::TYPE_ORDER_BM],
        118 => ['year' => 0, 'type' => x_contract_data::TYPE_ORDER_BMO],
        119 => ['year' => 0, 'type' => x_contract_data::TYPE_ORDER_OMSU],
        120 => ['year' => 0, 'type' => x_contract_data::TYPE_ORDER_OMSU2],
        122 => ['year' => 0, 'type' => x_contract_data::TYPE_PAYMENT_FB],
        123 => ['year' => 0, 'type' => x_contract_data::TYPE_PAYMENT_BM],
        124 => ['year' => 0, 'type' => x_contract_data::TYPE_PAYMENT_BMO],
        125 => ['year' => 0, 'type' => x_contract_data::TYPE_PAYMENT_OMSU],
        126 => ['year' => 0, 'type' => x_contract_data::TYPE_PAYMENT_OMSU2],
        
    ];


    public function update(string $file_path) {
    
        if (!file_exists($file_path)) {
            throw new \Exception('File not found: '. $file_path);
        }
        $spreadsheet = IOFactory::load($file_path, IReader::READ_DATA_ONLY, [IOFactory::READER_XLS, IOFactory::READER_XLSX]);
        $sheet = $spreadsheet->getSheetByName(static::WORKSHEET_NAME);
        $this->updateDB($sheet);
        
        $sheet_gp = $spreadsheet->getSheetByName(static::GPSHEET_NAME);
        $this->updateGP($sheet_gp);
    }
    
    protected function updateGP(?Worksheet $sheet) {
        
        $column = 1;
        $row = 6;
        $gp_date = new DBBotParam('gp_date');
        
        if (!$sheet) {
            return;
        }
        
        while (true) {
            $cell = $sheet->getCell([$column, $row]);
            
            $next_value = $cell->getValue();
            
            if (!$next_value) {
                $gp_date->value = $value;
                return;
            }
            
            $value = $next_value;
            $row++;
        }
    }
    
    protected function updateDB(?Worksheet $sheet) {
        
        if (!$sheet) {
            throw new \Exception('Не найден лист '. self::WORKSHEET_NAME);
        }
        $this->truncateDB();
        $this->loadDB($sheet);
        
    }
    
    protected function truncateDB() {
        
        $sql = 'TRUNCATE TABLE [x_object]; TRUNCATE TABLE [x_year_data]; TRUNCATE TABLE [x_contract]; TRUNCATE TABLE [x_contract_data];';
        DB::exec($sql);
        
    }
    
    protected function loadDB(Worksheet $sheet) {
    
        
        //$data =  $sheet->toArray('');
        $row_iterator = $sheet->getRowIterator(6);
        
        foreach ($row_iterator as $row) {
            $cell_iterator = $row->getCellIterator();
            $cells = [];
            foreach ($cell_iterator as $cell) {
                $cells[] = $cell->getValue();
            }
            
            $uin = $cells[6];
            
            if (!preg_match("/^\d+\.\d+\s*$/", $uin)) {
                error_log("Skipped as uin=$uin");
                continue;
            }
            
            ///
            $omsu = new x_omsu(['name' => $cells[0]], true);
            if ($omsu->isNew()) {
                error_log("Skipped as unknown OMSU name: $cells[0]");
                continue;
            }
            
            $object = new x_object(['uin' => $uin], true);
            
            if ($object->isNew()) {
                $object->uin = $uin;
                $object->omsu_id = $omsu->id;

                $object->full_name = $cells[7];
                $object->short_name = $cells[8];
                $object->name = $cells[9];

                $category = new x_category(['name' => $cells[10]], true);
                if ($category->isNew()) {
                    $category->write();
                }
                $object->x_category_id = $category->id;

                $gasu = new x_activity(['gasu_code' => $cells[12]], true);
                if ($gasu->isNew()) {
                    $gasu->name = $cells[13];
                    $gasu->write();
                }
                $object->x_activity_id = $gasu->id;

                $object->gasu_date = $cells[16];
                $object->ready_percent = $cells[17];
                $object->object_char = $cells[18];
                $object->type = $cells[19];
                $object->period = $cells[20];
                $object->open_date_planned = $cells[21] ? Date::excelToDateTimeObject($cells[21]) : null;

                $object->write();
            }
            
            $current_year = (int)date('Y');
            
            foreach ($this->year_cells as $key => $data) {
                $value = $cells[$key];
                if (!empty($value)) {
                    $year = $current_year + $data['year'];
                    $year_data = new x_year_data(['year' => $year, 'x_object_id' => $object->id, 'type' => $data['type']], true);
                    if ($year_data->isNew()) {
                        $year_data->value = $value;
                        $year_data->write();
                    } else {
                        throw new Exception(__("$data[type] $year для $object->uin уже был задан."));
                    }
                }
            }
            
            // Contract
            $contragent = new x_contragent(['inn' => $cells[94]], true);
            $contragent->name = $cells[93];
            if ($contragent->isNew() && 'x' != $contragent->inn && '' != $contragent->inn) {
                $contragent->write();
            } elseif ($contragent->isModified()) {
                $contragent->write();
            }
            
            $contract = new x_contract();
            $contract->x_contragent_id = $contragent->id;
            $contract->x_object_id = $object->id;
            $contract->status = $cells[3];
            $contract->status2 = $cells[4];
            $contract->number = ($cells[96] == 'x' || empty($cells[96])) ? null : $cells[96];
            $contract->date = $cells[95] ? Date::excelToDateTimeObject($cells[21]) : null;
            $contract->has_pir = strpos($cells[5], 'ПИР') === false ? false : true;
            $contract->has_smr = strpos($cells[5], 'СМР') === false ? false : true;
            $contract->write();
            
            foreach ($this->contract_cells as $key => $data) {
                $value = $cells[$key];
                if (!empty($value)) {
                    $year = $current_year + $data['year'];
                    $contract_data = new x_contract_data(['year' => $year, 'x_contract_id' => $contract->id, 'type' => $data['type']], true);
                    if ($contract_data->isNew()) {
                        $contract_data->value = $value;
                        $contract_data->write();
                    } else {
                        throw new Exception(__("Лимит $data[type] $year для $object->uin уже был задан."));
                    }
                }
            }
            
        }

    }
    
}
