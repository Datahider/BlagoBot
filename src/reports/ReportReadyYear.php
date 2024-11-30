<?php

namespace losthost\BlagoBot\reports;

use function \losthost\BlagoBot\__;
use losthost\BlagoBot\service\ReportSummary;
use losthost\DB\DBView;
use losthost\BlagoBot\data\report_param_value;

class ReportReadyYear extends AbstractReport {
    
    const SQL_QUERY = <<<FIN
            SELECT
                YEAR(object.open_date_planned) AS year,
                SUM(CASE
                        WHEN open_date_fact IS NULL THEN 0
                        ELSE 1
                    END) AS open,
                COUNT(object.id) AS total
            FROM 
                [x_object] AS object
            WHERE 
                YEAR(object.open_date_planned) IN (%years%)
            GROUP BY
                year
            ORDER BY
                year
            FIN;
    protected function checkParamErrors($params): false|array {
        return false;
    }

    protected function reportColumns(): array {
        return [__('Год'), __('Всего объектов'), __('Открыто объектов'), __('Не открыто объектов')];
    }

    protected function reportData($params): array {
        
        $in = [];
        foreach ($params['period'] as $period_id) {
            $param = new report_param_value(['id' => $period_id]);
            $in[] = $param->value;
        }
               
        $sql = str_replace('%years%', implode(',', $in), static::SQL_QUERY);
        $view = new DBView($sql);
        
        $result = [];
        while ($view->next()) {
            $result[] = [
                "<b>$view->year</b>",
                "<b>$view->total</b>",
                "<b>$view->open</b>",
                "<b>". ($view->total - $view->open). "</b>"
            ];
        }
        
        return $result;
    }

    protected function reportSummary($params): ReportSummary {
        return new ReportSummary('Сводная информация по годам', date_create_immutable(), $params);
    }

    protected function resultType(): int|string {
        return AbstractReport::RESULT_TYPE_SHOW;
    }
}
