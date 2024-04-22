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
use PhpOffice\PhpSpreadsheet\Shared\Date;
use losthost\DB\DB;

class DBUpdater {

    public function update(string $file_path, string $worksheet_name='База текущая', int $header_line=5, int $first_data_line=6) {
    
        if (!file_exists($file_path)) {
            throw new \Exception('File not found: '. $file_path);
        }
        $spreadsheet = IOFactory::load($file_path, IReader::READ_DATA_ONLY, [IOFactory::READER_XLS, IOFactory::READER_XLSX]);
        $sheet = $spreadsheet->getSheetByName($worksheet_name);
        $this->updateDB($sheet);

}
    
    protected function updateDB($sheet) {
        
        $this->truncateDB();
        $this->loadDB($sheet);
        
    }
    
    protected function truncateDB() {
        
        $sql = 'TRUNCATE TABLE [x_object]; TRUNCATE TABLE [x_year_data]';
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
            
            $object = new x_object();
            $object->uin = $uin;
            $object->omsu_id = $omsu->id;
            $object->status = $cells[3];
            $object->status2 = $cells[4];
            $object->work_type = $cells[5];
            
            $object->full_name = $cells[7];
            $object->short_name = $cells[8];
            $object->name = $cells[9];
            
            // TODO
            $category = new x_category(['name' => $cells[10]], true);
            if ($category->isNew()) {
                $category->write();
            }
            $object->category_id = $category->id;
            
            $object->gasu_code = $cells[12];
            $object->report_status1 = $cells[14];
            $object->report_status2 = $cells[15];
            $object->gasu_date = $cells[16];
            $object->ready_percent = $cells[17];
            $object->object_char = $cells[18];
            $object->type = $cells[19];
            $object->period = $cells[20];
            $object->open_date_planned = $cells[21] ? Date::excelToDateTimeObject($cells[21]) : null;
            $object->object_count = $cells[23];
            
            $object->rg_date = $cells[52] ? Date::excelToDateTimeObject($cells[52]) : null;;
            $object->nmck_date = $cells[71] ? Date::excelToDateTimeObject($cells[71]) : null;;
            $object->nmck_opz_date = $cells[74] ? Date::excelToDateTimeObject($cells[74]) : null;;
            $object->nmck_numsign = $cells[72];
            $object->ikz = $cells[73];
            $object->contract_winner = $cells[93];
            $object->contract_inn = $cells[94];
            $object->contract_number = $cells[96];
            $object->contract_date = $cells[95] ? Date::excelToDateTimeObject($cells[95]) : null;;
            $object->href = $cells[135];
            
            $object->write();
            
            $year = 2024;
            
            $year_data = new x_year_data(['year' => $year, 'x_object_id' => $object->id, 'type' => x_year_data::TYPE_SMR], true);
            $year_data->value = $cells[24]; $year_data->write();
            $year_data = new x_year_data(['year' => $year, 'x_object_id' => $object->id, 'type' => x_year_data::TYPE_PIR], true);
            $year_data->value = $cells[25]; $year_data->write();
            $year_data = new x_year_data(['year' => $year, 'x_object_id' => $object->id, 'type' => x_year_data::TYPE_LIMIT_FB], true);
            $year_data->value = $cells[27]; $year_data->write();
            $year_data = new x_year_data(['year' => $year, 'x_object_id' => $object->id, 'type' => x_year_data::TYPE_LIMIT_BM], true);
            $year_data->value = $cells[28]; $year_data->write();
            $year_data = new x_year_data(['year' => $year, 'x_object_id' => $object->id, 'type' => x_year_data::TYPE_LIMIT_BMO], true);
            $year_data->value = $cells[29]; $year_data->write();
            $year_data = new x_year_data(['year' => $year, 'x_object_id' => $object->id, 'type' => x_year_data::TYPE_LIMIT_OMSU], true);
            $year_data->value = $cells[30]; $year_data->write();

            $year_data = new x_year_data(['year' => $year+1, 'x_object_id' => $object->id, 'type' => x_year_data::TYPE_SMR], true);
            $year_data->value = $cells[31]; $year_data->write();
            $year_data = new x_year_data(['year' => $year+1, 'x_object_id' => $object->id, 'type' => x_year_data::TYPE_PIR], true);
            $year_data->value = $cells[32]; $year_data->write();
            $year_data = new x_year_data(['year' => $year+1, 'x_object_id' => $object->id, 'type' => x_year_data::TYPE_LIMIT_FB], true);
            $year_data->value = $cells[34]; $year_data->write();
            $year_data = new x_year_data(['year' => $year+1, 'x_object_id' => $object->id, 'type' => x_year_data::TYPE_LIMIT_BM], true);
            $year_data->value = $cells[35]; $year_data->write();
            $year_data = new x_year_data(['year' => $year+1, 'x_object_id' => $object->id, 'type' => x_year_data::TYPE_LIMIT_BMO], true);
            $year_data->value = $cells[36]; $year_data->write();
            $year_data = new x_year_data(['year' => $year+1, 'x_object_id' => $object->id, 'type' => x_year_data::TYPE_LIMIT_OMSU], true);
            $year_data->value = $cells[37]; $year_data->write();

            $year_data = new x_year_data(['year' => $year+2, 'x_object_id' => $object->id, 'type' => x_year_data::TYPE_SMR], true);
            $year_data->value = $cells[38]; $year_data->write();
            $year_data = new x_year_data(['year' => $year+2, 'x_object_id' => $object->id, 'type' => x_year_data::TYPE_PIR], true);
            $year_data->value = $cells[39]; $year_data->write();
            $year_data = new x_year_data(['year' => $year+2, 'x_object_id' => $object->id, 'type' => x_year_data::TYPE_LIMIT_FB], true);
            $year_data->value = $cells[41]; $year_data->write();
            $year_data = new x_year_data(['year' => $year+2, 'x_object_id' => $object->id, 'type' => x_year_data::TYPE_LIMIT_BM], true);
            $year_data->value = $cells[42]; $year_data->write();
            $year_data = new x_year_data(['year' => $year+2, 'x_object_id' => $object->id, 'type' => x_year_data::TYPE_LIMIT_BMO], true);
            $year_data->value = $cells[43]; $year_data->write();
            $year_data = new x_year_data(['year' => $year+2, 'x_object_id' => $object->id, 'type' => x_year_data::TYPE_LIMIT_OMSU], true);
            $year_data->value = $cells[44]; $year_data->write();

            $year_data = new x_year_data(['year' => $year+3, 'x_object_id' => $object->id, 'type' => x_year_data::TYPE_SMR], true);
            $year_data->value = $cells[45]; $year_data->write();
            $year_data = new x_year_data(['year' => $year+3, 'x_object_id' => $object->id, 'type' => x_year_data::TYPE_PIR], true);
            $year_data->value = $cells[46]; $year_data->write();
            $year_data = new x_year_data(['year' => $year+3, 'x_object_id' => $object->id, 'type' => x_year_data::TYPE_LIMIT_FB], true);
            $year_data->value = $cells[48]; $year_data->write();
            $year_data = new x_year_data(['year' => $year+3, 'x_object_id' => $object->id, 'type' => x_year_data::TYPE_LIMIT_BM], true);
            $year_data->value = $cells[49]; $year_data->write();
            $year_data = new x_year_data(['year' => $year+3, 'x_object_id' => $object->id, 'type' => x_year_data::TYPE_LIMIT_BMO], true);
            $year_data->value = $cells[50]; $year_data->write();
            $year_data = new x_year_data(['year' => $year+3, 'x_object_id' => $object->id, 'type' => x_year_data::TYPE_LIMIT_OMSU], true);
            $year_data->value = $cells[51]; $year_data->write();

//
//
//
//
//
//            $year_data = new x_year_data(['year' => $year, 'x_object_id' => $object->id, 'type' => x_year_data::TYPE_SMR], true);
//            $year_data->value = $cells[24]; $year_data->write();
//            $year_data = new x_year_data(['year' => $year, 'x_object_id' => $object->id, 'type' => x_year_data::TYPE_SMR], true);
//            $year_data->value = $cells[24]; $year_data->write();
//            $year_data = new x_year_data(['year' => $year, 'x_object_id' => $object->id, 'type' => x_year_data::TYPE_SMR], true);
//            $year_data->value = $cells[24]; $year_data->write();
//            $year_data = new x_year_data(['year' => $year, 'x_object_id' => $object->id, 'type' => x_year_data::TYPE_SMR], true);
//            $year_data->value = $cells[24]; $year_data->write();
//            $year_data = new x_year_data(['year' => $year, 'x_object_id' => $object->id, 'type' => x_year_data::TYPE_SMR], true);
//            $year_data->value = $cells[24]; $year_data->write();
//            $year_data = new x_year_data(['year' => $year, 'x_object_id' => $object->id, 'type' => x_year_data::TYPE_SMR], true);
//            $year_data->value = $cells[24]; $year_data->write();
//            $year_data = new x_year_data(['year' => $year, 'x_object_id' => $object->id, 'type' => x_year_data::TYPE_SMR], true);
//            $year_data->value = $cells[24]; $year_data->write();
//            $year_data = new x_year_data(['year' => $year, 'x_object_id' => $object->id, 'type' => x_year_data::TYPE_SMR], true);
//            $year_data->value = $cells[24]; $year_data->write();
//            $year_data = new x_year_data(['year' => $year, 'x_object_id' => $object->id, 'type' => x_year_data::TYPE_SMR], true);
//            $year_data->value = $cells[24]; $year_data->write();
//            $year_data = new x_year_data(['year' => $year, 'x_object_id' => $object->id, 'type' => x_year_data::TYPE_SMR], true);
//            $year_data->value = $cells[24]; $year_data->write();
//            $year_data = new x_year_data(['year' => $year, 'x_object_id' => $object->id, 'type' => x_year_data::TYPE_SMR], true);
//            $year_data->value = $cells[24]; $year_data->write();
//            $year_data = new x_year_data(['year' => $year, 'x_object_id' => $object->id, 'type' => x_year_data::TYPE_SMR], true);
//            $year_data->value = $cells[24]; $year_data->write();
//            $year_data = new x_year_data(['year' => $year, 'x_object_id' => $object->id, 'type' => x_year_data::TYPE_SMR], true);
//            $year_data->value = $cells[24]; $year_data->write();
//            $year_data = new x_year_data(['year' => $year, 'x_object_id' => $object->id, 'type' => x_year_data::TYPE_SMR], true);
//            $year_data->value = $cells[24]; $year_data->write();

            error_log("$omsu_name, $status, $status2, $work_type, $uin");
        }

    }
    
}
