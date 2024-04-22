<?php

use losthost\DB\DB;
use losthost\BlagoBot\data\x_object;
use losthost\BlagoBot\data\x_omsu;
use losthost\BlagoBot\data\x_year_data;
use losthost\BlagoBot\data\x_category;

require '../vendor/autoload.php';
require '../etc/bot_config.php';

DB::connect($db_host, $db_user, $db_pass, $db_name, $db_prefix);

x_object::initDataStructure();
x_omsu::initDataStructure();
x_year_data::initDataStructure();
x_category::initDataStructure();