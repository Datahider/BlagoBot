<?php

namespace losthost\BlagoBot\service;

use DateTimeImmutable;

class ReportMetadata {
    
    protected string $title;
    protected DateTimeImmutable $date_generated;
    protected DateTimeImmutable $date_gp;
    protected DateTimeImmutable $date_update;
    
    protected array $column_groups;
    protected array $sub_totals;
}
