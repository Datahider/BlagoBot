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
    
    const NOT_MY_FILE_EXCEPTION = 'Not my file';
    
    protected string $last_cell_checked;
    
    private $year_cells = [
        22 => ['year' => 0, 'type' => x_year_data2::TYPE_SMR],
        23 => ['year' => 0, 'type' => x_year_data2::TYPE_PIR],
        25 => ['year' => 0, 'type' => x_year_data2::TYPE_LIMIT_FB],
        26 => ['year' => 0, 'type' => x_year_data2::TYPE_LIMIT_BM],
        27 => ['year' => 0, 'type' => x_year_data2::TYPE_LIMIT_BMO],
        28 => ['year' => 0, 'type' => x_year_data2::TYPE_LIMIT_OMSU],

        29 => ['year' => 1, 'type' => x_year_data2::TYPE_SMR],
        30 => ['year' => 1, 'type' => x_year_data2::TYPE_PIR],
        32 => ['year' => 1, 'type' => x_year_data2::TYPE_LIMIT_FB],
        33 => ['year' => 1, 'type' => x_year_data2::TYPE_LIMIT_BM],
        34 => ['year' => 1, 'type' => x_year_data2::TYPE_LIMIT_BMO],
        35 => ['year' => 1, 'type' => x_year_data2::TYPE_LIMIT_OMSU],

        36 => ['year' => 2, 'type' => x_year_data2::TYPE_SMR],
        37 => ['year' => 2, 'type' => x_year_data2::TYPE_PIR],
        39 => ['year' => 2, 'type' => x_year_data2::TYPE_LIMIT_FB],
        40 => ['year' => 2, 'type' => x_year_data2::TYPE_LIMIT_BM],
        41 => ['year' => 2, 'type' => x_year_data2::TYPE_LIMIT_BMO],
        42 => ['year' => 2, 'type' => x_year_data2::TYPE_LIMIT_OMSU],

        43 => ['year' => 3, 'type' => x_year_data2::TYPE_SMR],
        44 => ['year' => 3, 'type' => x_year_data2::TYPE_PIR],
        46 => ['year' => 3, 'type' => x_year_data2::TYPE_LIMIT_FB],
        47 => ['year' => 3, 'type' => x_year_data2::TYPE_LIMIT_BM],
        48 => ['year' => 3, 'type' => x_year_data2::TYPE_LIMIT_BMO],
        49 => ['year' => 3, 'type' => x_year_data2::TYPE_LIMIT_OMSU],
        
        53 => ['year' => 3, 'type' => x_year_data2::TYPE_LIMIT_FB],
        54 => ['year' => 3, 'type' => x_year_data2::TYPE_LIMIT_BM],
        55 => ['year' => 3, 'type' => x_year_data2::TYPE_LIMIT_BMO],
        56 => ['year' => 3, 'type' => x_year_data2::TYPE_LIMIT_OMSU],
        
    ];
    
    private $contract_cells = [
        60 => ['year' => 0, 'type' => x_contract_data2::TYPE_RG_FB],
        61 => ['year' => 0, 'type' => x_contract_data2::TYPE_RG_BM],
        62 => ['year' => 0, 'type' => x_contract_data2::TYPE_RG_BMO],
        63 => ['year' => 0, 'type' => x_contract_data2::TYPE_RG_OMSU],
        64 => ['year' => 0, 'type' => x_contract_data2::TYPE_RG_OMSU2],
        
        66 => ['year' => 1, 'type' => x_contract_data2::TYPE_RG_FB],
        67 => ['year' => 1, 'type' => x_contract_data2::TYPE_RG_BM],
        68 => ['year' => 1, 'type' => x_contract_data2::TYPE_RG_BMO],
        69 => ['year' => 1, 'type' => x_contract_data2::TYPE_RG_OMSU],
        70 => ['year' => 1, 'type' => x_contract_data2::TYPE_RG_OMSU2],
        
        72 => ['year' => 2, 'type' => x_contract_data2::TYPE_RG_FB],
        73 => ['year' => 2, 'type' => x_contract_data2::TYPE_RG_BM],
        73 => ['year' => 2, 'type' => x_contract_data2::TYPE_RG_BMO],
        75 => ['year' => 2, 'type' => x_contract_data2::TYPE_RG_OMSU],
        76 => ['year' => 2, 'type' => x_contract_data2::TYPE_RG_OMSU2],
        
        82 => ['year' => 0, 'type' => x_contract_data2::TYPE_NMCK_FB],
        83 => ['year' => 0, 'type' => x_contract_data2::TYPE_NMCK_BM],
        84 => ['year' => 0, 'type' => x_contract_data2::TYPE_NMCK_BMO],
        85 => ['year' => 0, 'type' => x_contract_data2::TYPE_NMCK_OMSU],
        86 => ['year' => 0, 'type' => x_contract_data2::TYPE_NMCK_OMSU2],
        
        88 => ['year' => 1, 'type' => x_contract_data2::TYPE_NMCK_FB],
        89 => ['year' => 1, 'type' => x_contract_data2::TYPE_NMCK_BM],
        90 => ['year' => 1, 'type' => x_contract_data2::TYPE_NMCK_BMO],
        91 => ['year' => 1, 'type' => x_contract_data2::TYPE_NMCK_OMSU],
        92 => ['year' => 1, 'type' => x_contract_data2::TYPE_NMCK_OMSU2],
        
        94 => ['year' => 2, 'type' => x_contract_data2::TYPE_NMCK_FB],
        95 => ['year' => 2, 'type' => x_contract_data2::TYPE_NMCK_BM],
        96 => ['year' => 2, 'type' => x_contract_data2::TYPE_NMCK_BMO],
        97 => ['year' => 2, 'type' => x_contract_data2::TYPE_NMCK_OMSU],
        98 => ['year' => 2, 'type' => x_contract_data2::TYPE_NMCK_OMSU2],
        
        104 => ['year' => 0, 'type' => x_contract_data2::TYPE_CONTRACT_FB],
        105 => ['year' => 0, 'type' => x_contract_data2::TYPE_CONTRACT_BM],
        106 => ['year' => 0, 'type' => x_contract_data2::TYPE_CONTRACT_BMO],
        107 => ['year' => 0, 'type' => x_contract_data2::TYPE_CONTRACT_OMSU],
        108 => ['year' => 0, 'type' => x_contract_data2::TYPE_CONTRACT_OMSU2],
        
        110 => ['year' => 1, 'type' => x_contract_data2::TYPE_CONTRACT_FB],
        111 => ['year' => 1, 'type' => x_contract_data2::TYPE_CONTRACT_BM],
        112 => ['year' => 1, 'type' => x_contract_data2::TYPE_CONTRACT_BMO],
        113 => ['year' => 1, 'type' => x_contract_data2::TYPE_CONTRACT_OMSU],
        114 => ['year' => 1, 'type' => x_contract_data2::TYPE_CONTRACT_OMSU2],
        
        116 => ['year' => 2, 'type' => x_contract_data2::TYPE_CONTRACT_FB],
        117 => ['year' => 2, 'type' => x_contract_data2::TYPE_CONTRACT_BM],
        118 => ['year' => 2, 'type' => x_contract_data2::TYPE_CONTRACT_BMO],
        119 => ['year' => 2, 'type' => x_contract_data2::TYPE_CONTRACT_OMSU],
        120 => ['year' => 2, 'type' => x_contract_data2::TYPE_CONTRACT_OMSU2],
        
        122 => ['year' => 0, 'type' => x_contract_data2::TYPE_ORDER_FB],
        123 => ['year' => 0, 'type' => x_contract_data2::TYPE_ORDER_BM],
        124 => ['year' => 0, 'type' => x_contract_data2::TYPE_ORDER_BMO],
        125 => ['year' => 0, 'type' => x_contract_data2::TYPE_ORDER_OMSU],
        126 => ['year' => 0, 'type' => x_contract_data2::TYPE_ORDER_OMSU2],
        128 => ['year' => 0, 'type' => x_contract_data2::TYPE_PAYMENT_FB],
        129 => ['year' => 0, 'type' => x_contract_data2::TYPE_PAYMENT_BM],
        130 => ['year' => 0, 'type' => x_contract_data2::TYPE_PAYMENT_BMO],
        131 => ['year' => 0, 'type' => x_contract_data2::TYPE_PAYMENT_OMSU],
        132 => ['year' => 0, 'type' => x_contract_data2::TYPE_PAYMENT_OMSU2],
        
    ];


    public function update(Spreadsheet &$spreadsheet) {
            
        $sheet = $spreadsheet->getSheetByName(static::WORKSHEET_NAME);
        if (!$sheet) {
            throw new \Exception(static::NOT_MY_FILE_EXCEPTION);
        }
    
        $this->truncateDB();
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
    
    protected function updateGP(?Worksheet &$sheet) {
        
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
    
    protected function updatePrev(?Worksheet &$sheet) {
        
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
                $prev->object_name = $this->checkCell('Объект3', $cells[11]);
                $prev->category2_name = $this->checkCell('Категория2', $cells[13]);
                $prev->object_count = $this->checkCell('Количество объектов', $cells[22]);
                $prev->contract_inn = $this->checkCell('Контракт ИНН', preg_replace("/^_/", '', $cells[55]));
                $prev->contract_winner = $this->checkCell('Контракт Победитель', $cells[54]);
                $prev->payment_total = $this->checkCell('Оплата ФБ', $cells[75])
                        + $this->checkCell('Оплата БМ', $cells[76])
                        + $this->checkCell('Оплата БМО', $cells[77])
                        + $this->checkCell('Оплата ОМСУ', $cells[78])
                        + $this->checkCell('Оплата ОМСУ2', $cells[79])
                        + $this->checkCell('Оплата ДФ', $cells[80])
                        + $this->checkCell('Оплата Внебюд.', $cells[81]);
                $prev->write();
                error_log("Written $cells[0] -- $row_num");
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(). " База прошлых лет. Строка $row_num");
        }
    }
    
    protected function updateDB(Worksheet &$sheet) {
        
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
    
    protected function loadDB(Worksheet &$sheet) {
    
        
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


                $uin = $this->checkCell("УИН", $cells[4]);

                if (!preg_match("/^\d+\.\d+\s*$/", $uin)) {
                    error_log("Skipped as uin=$uin");
                    continue;
                }

                ///
                $omsu = new x_omsu(['name' => $this->checkCell("Округ", $cells[0])], true);
                if ($omsu->isNew()) {
                    throw new \Exception("Не известный ОМСУ: $cells[0]");
                }
                if (preg_match("/[Пп][Рр][Оо][Чч][Ее][Ее]/", $cells[1])) {
                    error_log("Skipped as status is $cells[1]");
                    continue;
                }

                $object = new x_object2(['uin' => $uin], true);

                if ($object->isNew()) {
                    $object->uin = $uin;
                    $object->omsu_id = $omsu->id;

                    $object->full_name = $this->checkCell("Объект1", $cells[6]);
                    $object->short_name = $this->checkCell("Объект2", $cells[7]);
                    $object->name = $this->checkCell("Объект3", $cells[8]);

                    $category = new x_category(['name' => $this->checkCell("Категория", $cells[9])], true);
                    if ($category->isNew()) {
                        $category->write();
                    }
                    $object->x_category_id = $category->id;
                    $object->category2_name = $this->checkCell("Категория2", $cells[10]);

                    $gasu = new x_activity(['gasu_code' => $this->checkCell("Код Гасу", $cells[12])], true);
                    if ($gasu->isNew()) {
                        $gasu->name = $this->checkCell("Мероприятие кратко", $cells[13]);
                        $gasu->write();
                    }
                    $object->x_activity_id = $gasu->id;

                    $object->object_char = $this->checkCell("Характеристика объекта", $cells[19]);
                    $object->type = $this->checkCell("ТИП", $cells[16]);
                    $object->period = $this->checkCell("Срок", $cells[17]);
                    
                    $object->nmck_purchase_number = $this->checkCell("№ Закупки", $cells[77]);
                    
                    try {
                        $object->open_date_planned = $cells[18] ? Date::excelToDateTimeObject($this->checkCell("Плановая дата открытия объекта", $cells[18])) : null;
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
                    $contragent = new x_contragent(['inn' => $this->checkCell("Контракт ИНН", preg_replace("/^\'/", '', $cells[100]))], true);
                    $contragent->name = $this->checkCell("Контракт победитель", $cells[99]);
                    if ($contragent->isNew() && 'x' != $contragent->inn && '' != $contragent->inn && null != $contragent->name) {
                        $contragent->write();
                    } elseif ($contragent->isModified() && null != $contragent->name) {
                        $contragent->write();
                    }
                } catch (\Exception $e) {
                    throw new \Exception($e->getMessage(). " Строка файла: $row_num", $e->getCode(), $e);
                }
                $contract = new x_contract2();
                $contract->x_contragent_id = $contragent->id;
                $contract->x_object_id = $object->id;
                $contract->status = $this->checkCell("Статус", $cells[1]);
                $contract->status2 = $this->checkCell("Статус2", $cells[2]);
                $contract->number = ($cells[102] == 'x' || empty($cells[102])) ? null : $this->checkCell("Контракт номер", $cells[102]);

                try {
                    $contract->date = $cells[101] ? Date::excelToDateTimeObject($this->checkCell("Контракт дата", $cells[101])) : null;
                } catch (\Exception $e) {
                    throw new \Exception($e->getMessage(). "\n\nСтрока: $row_num, \nЗначение: Дата контракта");
                    } catch (\TypeError $e) {
                        throw new \Exception($e->getMessage(). "\n\nСтрока $row_num, \nЗначение: Дата контракта");
                }

                $contract->has_pir = strpos($this->checkCell("Вид работ по контракту", $cells[5]), 'ПИР') === false ? false : true;
                $contract->has_smr = strpos($cells[3], 'СМР') === false ? false : true;
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
