<?php

use losthost\telle\Bot;
use losthost\BotView\BotView;

use losthost\BlagoBot\data\user;
use losthost\BlagoBot\data\menu;
use losthost\BlagoBot\data\report;
use losthost\BlagoBot\data\report_param;
use losthost\BlagoBot\data\report_param_value;
use losthost\BlagoBot\data\department;
use losthost\BlagoBot\data\user_dept_binding;

use losthost\BlagoBot\data\x_object;
use losthost\BlagoBot\data\x_omsu;
use losthost\BlagoBot\data\x_year_data;
use losthost\BlagoBot\data\x_contract;
use losthost\BlagoBot\data\x_contract_data;
use losthost\BlagoBot\data\x_contragent;
use losthost\BlagoBot\data\x_prev;
use losthost\BlagoBot\data\x_responsible;
use losthost\telle\model\DBPendingUpdate;

require 'vendor/autoload.php';
require 'src/functions.php';
require 'src/show.php';

BotView::setTemplateDir(__DIR__. '/templates');

Bot::setup();

menu::initDataStructure();
user::initDataStructure();
department::initDataStructure();
user_dept_binding::initDataStructure();
DBPendingUpdate::initDataStructure();

x_omsu::initDataStructure();
x_object::initDataStructure();
x_year_data::initDataStructure();
x_contract::initDataStructure();
x_contract_data::initDataStructure();
x_contragent::initDataStructure();
x_prev::initDataStructure();
x_responsible::initDataStructure();

report::initDataStructure();
report_param::initDataStructure();
report_param_value::initDataStructure();

Bot::param('superadmin', 203645978);
Bot::param('topmenu_id', 1);

Bot::addHandler(\losthost\BlagoBot\handlers\PreMessage::class);
Bot::addHandler(\losthost\BlagoBot\handlers\MessageAuth::class);
Bot::addHandler(losthost\BlagoBot\handlers\CommandStart::class);

Bot::addHandler(losthost\BlagoBot\handlers\MessageFiles::class);

Bot::addHandler(losthost\BlagoBot\handlers\CommandUsers::class);
Bot::addHandler(\losthost\BlagoBot\handlers\CommandXUsers::class);
Bot::addHandler(\losthost\BlagoBot\handlers\CommandDigits::class);
Bot::addHandler(\losthost\BlagoBot\handlers\CommandTest::class);
Bot::addHandler(losthost\BlagoBot\handlers\CommandHelp::class);
Bot::addHandler(\losthost\BlagoBot\handlers\CommandStat::class);

Bot::addHandler(\losthost\BlagoBot\handlers\PreCallback::class);
Bot::addHandler(\losthost\BlagoBot\handlers\CallbackAddUser::class);
Bot::addHandler(\losthost\BlagoBot\handlers\CallbackInlineButton::class);
Bot::addHandler(\losthost\BlagoBot\handlers\CallbackMakeReport::class);
Bot::addHandler(losthost\BlagoBot\handlers\CallbackEditUser::class);

Bot::run();

