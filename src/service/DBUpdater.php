<?php

namespace losthost\BlagoBot\service;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReader;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use losthost\BlagoBot\data\x_omsu;
use losthost\BlagoBot\data\x_object2;
use losthost\BlagoBot\data\x_category;
use losthost\BlagoBot\data\x_year_data2;
use losthost\BlagoBot\data\x_activity;
use losthost\BlagoBot\data\x_contract2;
use losthost\BlagoBot\data\x_contract_data2;
use losthost\BlagoBot\data\x_contragent;
use losthost\BlagoBot\data\x_prev2;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use losthost\DB\DB;
use losthost\telle\model\DBBotParam;
use Exception;

use function \losthost\BlagoBot\__;

class DBUpdater {

    const WORKSHEET_NAME = 'База текущая';
    const GPSHEET_NAME = 'Госпрограмма';
    const PREVDATA_NAME = 'База прошлых лет';
    
    protected string $last_cell_checked;
    
    private $year_cells = [
        26 => ['year' => 0, 'type' => x_year_data2::TYPE_SMR],
        27 => ['year' => 0, 'type' => x_year_data2::TYPE_PIR],
        29 => ['year' => 0, 'type' => x_year_data2::TYPE_LIMIT_FB],
        30 => ['year' => 0, 'type' => x_year_data2::TYPE_LIMIT_BM],
        31 => ['year' => 0, 'type' => x_year_data2::TYPE_LIMIT_BMO],
        32 => ['year' => 0, 'type' => x_year_data2::TYPE_LIMIT_OMSU],

        33 => ['year' => 1, 'type' => x_year_data2::TYPE_SMR],
        34 => ['year' => 1, 'type' => x_year_data2::TYPE_PIR],
        36 => ['year' => 1, 'type' => x_year_data2::TYPE_LIMIT_FB],
        37 => ['year' => 1, 'type' => x_year_data2::TYPE_LIMIT_BM],
        38 => ['year' => 1, 'type' => x_year_data2::TYPE_LIMIT_BMO],
        39 => ['year' => 1, 'type' => x_year_data2::TYPE_LIMIT_OMSU],

        40 => ['year' => 2, 'type' => x_year_data2::TYPE_SMR],
        41 => ['year' => 2, 'type' => x_year_data2::TYPE_PIR],
        43 => ['year' => 2, 'type' => x_year_data2::TYPE_LIMIT_FB],
        44 => ['year' => 2, 'type' => x_year_data2::TYPE_LIMIT_BM],
        45 => ['year' => 2, 'type' => x_year_data2::TYPE_LIMIT_BMO],
        46 => ['year' => 2, 'type' => x_year_data2::TYPE_LIMIT_OMSU],

        47 => ['year' => 3, 'type' => x_year_data2::TYPE_SMR],
        48 => ['year' => 3, 'type' => x_year_data2::TYPE_PIR],
        50 => ['year' => 3, 'type' => x_year_data2::TYPE_LIMIT_FB],
        51 => ['year' => 3, 'type' => x_year_data2::TYPE_LIMIT_BM],
        52 => ['year' => 3, 'type' => x_year_data2::TYPE_LIMIT_BMO],
        53 => ['year' => 3, 'type' => x_year_data2::TYPE_LIMIT_OMSU],
    ];
    
    private $contract_cells = [
        56 => ['year' => 0, 'type' => x_contract_data2::TYPE_RG_FB],
        57 => ['year' => 0, 'type' => x_contract_data2::TYPE_RG_BM],
        58 => ['year' => 0, 'type' => x_contract_data2::TYPE_RG_BMO],
        59 => ['year' => 0, 'type' => x_contract_data2::TYPE_RG_OMSU],
        60 => ['year' => 0, 'type' => x_contract_data2::TYPE_RG_OMSU2],
        
        62 => ['year' => 1, 'type' => x_contract_data2::TYPE_RG_FB],
        63 => ['year' => 1, 'type' => x_contract_data2::TYPE_RG_BM],
        64 => ['year' => 1, 'type' => x_contract_data2::TYPE_RG_BMO],
        65 => ['year' => 1, 'type' => x_contract_data2::TYPE_RG_OMSU],
        66 => ['year' => 1, 'type' => x_contract_data2::TYPE_RG_OMSU2],
        
        68 => ['year' => 2, 'type' => x_contract_data2::TYPE_RG_FB],
        69 => ['year' => 2, 'type' => x_contract_data2::TYPE_RG_BM],
        70 => ['year' => 2, 'type' => x_contract_data2::TYPE_RG_BMO],
        71 => ['year' => 2, 'type' => x_contract_data2::TYPE_RG_OMSU],
        72 => ['year' => 2, 'type' => x_contract_data2::TYPE_RG_OMSU2],
        
        78 => ['year' => 0, 'type' => x_contract_data2::TYPE_NMCK_FB],
        79 => ['year' => 0, 'type' => x_contract_data2::TYPE_NMCK_BM],
        80 => ['year' => 0, 'type' => x_contract_data2::TYPE_NMCK_BMO],
        81 => ['year' => 0, 'type' => x_contract_data2::TYPE_NMCK_OMSU],
        82 => ['year' => 0, 'type' => x_contract_data2::TYPE_NMCK_OMSU2],
        
        84 => ['year' => 1, 'type' => x_contract_data2::TYPE_NMCK_FB],
        85 => ['year' => 1, 'type' => x_contract_data2::TYPE_NMCK_BM],
        86 => ['year' => 1, 'type' => x_contract_data2::TYPE_NMCK_BMO],
        87 => ['year' => 1, 'type' => x_contract_data2::TYPE_NMCK_OMSU],
        88 => ['year' => 1, 'type' => x_contract_data2::TYPE_NMCK_OMSU2],
        
        90 => ['year' => 2, 'type' => x_contract_data2::TYPE_NMCK_FB],
        91 => ['year' => 2, 'type' => x_contract_data2::TYPE_NMCK_BM],
        92 => ['year' => 2, 'type' => x_contract_data2::TYPE_NMCK_BMO],
        93 => ['year' => 2, 'type' => x_contract_data2::TYPE_NMCK_OMSU],
        94 => ['year' => 2, 'type' => x_contract_data2::TYPE_NMCK_OMSU2],
        
        100 => ['year' => 0, 'type' => x_contract_data2::TYPE_CONTRACT_FB],
        101 => ['year' => 0, 'type' => x_contract_data2::TYPE_CONTRACT_BM],
        102 => ['year' => 0, 'type' => x_contract_data2::TYPE_CONTRACT_BMO],
        103 => ['year' => 0, 'type' => x_contract_data2::TYPE_CONTRACT_OMSU],
        104 => ['year' => 0, 'type' => x_contract_data2::TYPE_CONTRACT_OMSU2],
        
        106 => ['year' => 1, 'type' => x_contract_data2::TYPE_CONTRACT_FB],
        107 => ['year' => 1, 'type' => x_contract_data2::TYPE_CONTRACT_BM],
        108 => ['year' => 1, 'type' => x_contract_data2::TYPE_CONTRACT_BMO],
        109 => ['year' => 1, 'type' => x_contract_data2::TYPE_CONTRACT_OMSU],
        110 => ['year' => 1, 'type' => x_contract_data2::TYPE_CONTRACT_OMSU2],
        
        112 => ['year' => 2, 'type' => x_contract_data2::TYPE_CONTRACT_FB],
        113 => ['year' => 2, 'type' => x_contract_data2::TYPE_CONTRACT_BM],
        114 => ['year' => 2, 'type' => x_contract_data2::TYPE_CONTRACT_BMO],
        115 => ['year' => 2, 'type' => x_contract_data2::TYPE_CONTRACT_OMSU],
        116 => ['year' => 2, 'type' => x_contract_data2::TYPE_CONTRACT_OMSU2],
        
        118 => ['year' => 0, 'type' => x_contract_data2::TYPE_ORDER_FB],
        119 => ['year' => 0, 'type' => x_contract_data2::TYPE_ORDER_BM],
        120 => ['year' => 0, 'type' => x_contract_data2::TYPE_ORDER_BMO],
        121 => ['year' => 0, 'type' => x_contract_data2::TYPE_ORDER_OMSU],
        122 => ['year' => 0, 'type' => x_contract_data2::TYPE_ORDER_OMSU2],
        124 => ['year' => 0, 'type' => x_contract_data2::TYPE_PAYMENT_FB],
        125 => ['year' => 0, 'type' => x_contract_data2::TYPE_PAYMENT_BM],
        126 => ['year' => 0, 'type' => x_contract_data2::TYPE_PAYMENT_BMO],
        127 => ['year' => 0, 'type' => x_contract_data2::TYPE_PAYMENT_OMSU],
        128 => ['year' => 0, 'type' => x_contract_data2::TYPE_PAYMENT_OMSU2],
        
    ];


    public function update(string $file_path) {
    
        if (!file_exists($file_path)) {
            throw new \Exception('File not found: '. $file_path);
        }
    
        $this->truncateDB();
        
        $spreadsheet = IOFactory::load($file_path, IReader::READ_DATA_ONLY, [IOFactory::READER_XLS, IOFactory::READER_XLSX]);
        $sheet = $spreadsheet->getSheetByName(static::WORKSHEET_NAME);
        if (!$sheet) {
            throw new \Exception('Не найден лист '. static::WORKSHEET_NAME);
        }
        $this->updateDB($sheet);

        $sheet_prev = $spreadsheet->getSheetByName(static::PREVDATA_NAME);
        if (!$sheet_prev) {
            throw new \Exception('Не найден лист '. static::PREVDATA_NAME);
        }
        $this->updatePrev($sheet_prev);

        $sheet_gp = $spreadsheet->getSheetByName(static::GPSHEET_NAME);
        if (!$sheet_gp) {
            throw new \Exception('Не найден лист '. static::GPSHEET_NAME);
        }
        $this->updateGP($sheet_gp);
        
        $this->swapWorkingTables();

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
                if (is_string($value)) {
                    $gp_date->value = $value;
                } else {
                    $gp_date->value = Date::excelToDateTimeObject($value)->format('d.m.Y');
                }
                return;
            }
            
            $value = $next_value;
            $row++;
        }
    }
    
    protected function updatePrev(?Worksheet $sheet) {
        
        $row_iterator = $sheet->getRowIterator(7);
        $row_num = 6; // Для выдачи номера строки с ошибкой
        
        try {
            foreach ($row_iterator as $row) {
                $row_num++;
                $cell_iterator = $row->getCellIterator();
                $cells = [];
                foreach ($cell_iterator as $cell) {
                    $cells[] = $cell->getValue();
                }

                if (empty($cells[3]) || $cells[3] == 'х') {
                    error_log("Skipped $row_num");
                    continue;
                }
                $prev = new x_prev2();
                $prev->year = $this->checkCell('Год', $cells[1]);
                $prev->omsu_name = $this->checkCell('Округ', $cells[3]);
                $prev->object_name = $this->checkCell('Объект3', $cells[15]);
                $prev->category2_name = $this->checkCell('Категория2', $cells[19]);
                $prev->object_count = $this->checkCell('Количество объектов', $cells[30]);
                $prev->payment_total = $this->checkCell('Оплата ФБ', $cells[86])
                        + $this->checkCell('Оплата БМ', $cells[87])
                        + $this->checkCell('Оплата БМО', $cells[88])
                        + $this->checkCell('Оплата ОМСУ', $cells[89])
                        + $this->checkCell('Оплата ОМСУ2', $cells[90])
                        + $this->checkCell('Оплата ДФ', $cells[91])
                        + $this->checkCell('Оплата Внебюд.', $cells[92]);
                $prev->write();
                error_log("Written $cells[0] -- $row_num");
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(). " База прошлых лет. Строка $row_num");
        }
    }
    
    protected function updateDB(?Worksheet $sheet) {
        
        if (!$sheet) {
            throw new \Exception('Не найден лист '. self::WORKSHEET_NAME);
        }
        $this->loadDB($sheet);
        
    }
    
    protected function truncateDB() {
        
        $sql = 'DROP TABLE IF EXISTS [x_object2]; DROP TABLE IF EXISTS [x_year_data2]; DROP TABLE IF EXISTS [x_contract2]; DROP TABLE IF EXISTS [x_contract_data2]; DROP TABLE IF EXISTS [x_prev2]';
        DB::exec($sql);
        
        x_object2::initDataStructure(true);
        x_year_data2::initDataStructure(true);
        x_contract2::initDataStructure(true);
        x_contract_data2::initDataStructure(true);
        x_prev2::initDataStructure(true);
        
    }
    
    protected function swapWorkingTables() {
        $sql = <<<FIN
            RENAME TABLE
                [x_object] TO [x_object3], [x_object2] TO [x_object], [x_object3] TO [x_object2],
                [x_year_data] TO [x_year_data3], [x_year_data2] TO [x_year_data], [x_year_data3] TO [x_year_data2],
                [x_contract] TO [x_contract3], [x_contract2] TO [x_contract], [x_contract3] TO [x_contract2],
                [x_contract_data] TO [x_contract_data3], [x_contract_data2] TO [x_contract_data], [x_contract_data3] TO [x_contract_data2],
                [x_prev] TO [x_prev3], [x_prev2] TO [x_prev], [x_prev3] TO [x_prev2]
            FIN;
        DB::exec($sql);
    }
    
    protected function loadDB(Worksheet $sheet) {
    
        
        //$data =  $sheet->toArray('');
        $row_iterator = $sheet->getRowIterator(7);
        $row_num = 6;
        
        foreach ($row_iterator as $row) {
            $row_num++;
            try {
                $cell_iterator = $row->getCellIterator();
                $cells = [];
                foreach ($cell_iterator as $cell) {
                    $cells[] = $cell->getValue();
                }


                $uin = $this->checkCell("УИН", $cells[6]);

                if (!preg_match("/^\d+\.\d+\s*$/", $uin)) {
                    error_log("Skipped as uin=$uin");
                    continue;
                }

                ///
                $omsu = new x_omsu(['name' => $this->checkCell("Округ", $cells[0])], true);
                if ($omsu->isNew()) {
                    error_log("Skipped as unknown OMSU name: $cells[0]");
                    continue;
                }
                if (preg_match("/[Пп][Рр][Оо][Чч][Ее][Ее]/", $cells[3])) {
                    error_log("Skipped as status is $cells[3]");
                    continue;
                }

                $object = new x_object2(['uin' => $uin], true);

                if ($object->isNew()) {
                    $object->uin = $uin;
                    $object->omsu_id = $omsu->id;

                    $object->full_name = $this->checkCell("Объект1", $cells[7]);
                    $object->short_name = $this->checkCell("Объект2", $cells[8]);
                    $object->name = $this->checkCell("Объект3", $cells[9]);

                    $category = new x_category(['name' => $this->checkCell("Категория", $cells[10])], true);
                    if ($category->isNew()) {
                        $category->write();
                    }
                    $object->x_category_id = $category->id;
                    $object->category2_name = $this->checkCell("Категория2", $cells[11]);

                    $gasu = new x_activity(['gasu_code' => $this->checkCell("Код Гасу", $cells[13])], true);
                    if ($gasu->isNew()) {
                        $gasu->name = $this->checkCell("Мероприятие кратко", $cells[14]);
                        $gasu->write();
                    }
                    $object->x_activity_id = $gasu->id;

                    try {
                        $object->gasu_date = $cells[17] ? Date::excelToDateTimeObject($this->checkCell("Дата открытия ГАСУ", $cells[17])) : null;
                    } catch (\Exception $e) {
                        throw new \Exception($e->getMessage(). "\n\nСтрока: $row_num, \nЗначение: Дата ГАСУ");
                    } catch (\TypeError $e) {
                        throw new \Exception($e->getMessage(). "\n\nСтрока $row_num, \nЗначение: Дата ГАСУ");
                    }

                    $object->ready_percent = $this->checkCell("% выполнения работ", $cells[18]);
                    $object->object_char = $this->checkCell("Характеристика объекта", $cells[19]);
                    $object->type = $this->checkCell("ТИП", $cells[20]);
                    $object->period = $this->checkCell("Срок", $cells[21]);
                    try {
                        $object->open_date_planned = $cells[22] ? Date::excelToDateTimeObject($this->checkCell("Плановая дата открытия объекта", $cells[22])) : null;
                    } catch (\Exception $e) {
                        throw new \Exception($e->getMessage(). "\n\nСтрока: $row_num, \nЗначение: Запланированная дата открытия");
                    } catch (\TypeError $e) {
                        throw new \Exception($e->getMessage(). "\n\nСтрока $row_num, \nЗначение: Запланированная дата открытия");
                    }

                    $object->write();
                }

                $current_year = (int)date('Y');

                foreach ($this->year_cells as $key => $data) {
                    $year = $current_year + $data['year'];
                    $value = $this->checkCell($data['type']. " $year", $cells[$key]);
                    if (!empty($value)) {
                        $year_data = new x_year_data2(['year' => $year, 'x_object_id' => $object->id, 'type' => $data['type']], true);
                        if ($year_data->isNew()) {
                            $year_data->value = $value;
                            $year_data->write();
                        } else {
                            $year_data->value += $value;
                            $year_data->write();
                            //throw new Exception(__("$data[type] $year для $object->uin уже был задан. Строка $row_num"));
                            // TODO - если всё норм, то убрать комментарий и перенести запись во вне if
                        }
                    }
                }

                // Contract
                try {
                    $contragent = new x_contragent(['inn' => $this->checkCell("Контракт ИНН", $cells[96])], true);
                    $contragent->name = $this->checkCell("Контракт победитель", $cells[95]);
                    if ($contragent->isNew() && 'x' != $contragent->inn && '' != $contragent->inn) {
                        $contragent->write();
                    } elseif ($contragent->isModified()) {
                        $contragent->write();
                    }
                } catch (\Exception $e) {
                    throw new \Exception($e->getMessage(). " Строка файла: $row_num", $e->getCode(), $e);
                }
                $contract = new x_contract2();
                $contract->x_contragent_id = $contragent->id;
                $contract->x_object_id = $object->id;
                $contract->status = $this->checkCell("Статус", $cells[3]);
                $contract->status2 = $this->checkCell("Статус2", $cells[4]);
                $contract->number = ($cells[98] == 'x' || empty($cells[98])) ? null : $this->checkCell("Контракт номер", $cells[98]);

                try {
                    $contract->date = $cells[97] ? Date::excelToDateTimeObject($this->checkCell("Контракт дата", $cells[97])) : null;
                } catch (\Exception $e) {
                    throw new \Exception($e->getMessage(). "\n\nСтрока: $row_num, \nЗначение: Дата контракта");
                    } catch (\TypeError $e) {
                        throw new \Exception($e->getMessage(). "\n\nСтрока $row_num, \nЗначение: Дата контракта");
                }

                $contract->has_pir = strpos($this->checkCell("Вид работ по контракту", $cells[5]), 'ПИР') === false ? false : true;
                $contract->has_smr = strpos($cells[5], 'СМР') === false ? false : true;
                $contract->write();

                foreach ($this->contract_cells as $key => $data) {
                    $year = $current_year + $data['year'];
                    $value = $this->checkCell($data['type']. " $year", $cells[$key]);
                    if (!empty($value)) {
                        $contract_data = new x_contract_data2(['year' => $year, 'x_contract_id' => $contract->id, 'type' => $data['type']], true);
                        if ($contract_data->isNew()) {
                            $contract_data->value = $value;
                            $contract_data->write();
                        } else {
                            throw new Exception(__("Лимит $data[type] $year для $object->uin уже был задан."));
                        }
                    }
                }
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage(). "\n\nСтрока: $row_num");
            }
        }

    }
    
    protected function checkCell(string $text, mixed $value) : mixed {
        
        $this->last_cell_checked = $text;
                
        if (substr($value, 0, 1) == '=') {
            throw new \Exception('Ячейка содержит формулу. '. $this->last_cell_checked);
        }
        
        return $value;
    }
    
}
