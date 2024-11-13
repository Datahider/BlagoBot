<?php

use losthost\telle\Bot;
use losthost\BotView\BotView;

use losthost\BlagoBot\handlers\PreMessage;
use losthost\BlagoBot\handlers\MessageAuth;
use losthost\BlagoBot\handlers\CommandStart;
use losthost\BlagoBot\handlers\MessageFile2;
use losthost\BlagoBot\handlers\CommandUpdate;
use losthost\BlagoBot\handlers\CommandUsers;
use losthost\BlagoBot\handlers\CommandXUsers;
use losthost\BlagoBot\handlers\CommandDigits;
use losthost\BlagoBot\handlers\CommandTest;
use losthost\BlagoBot\handlers\CommandHelp;
use losthost\BlagoBot\handlers\CommandStat;

use losthost\BlagoBot\handlers\PreCallback;
use losthost\BlagoBot\handlers\CallbackAddUser;
use losthost\BlagoBot\handlers\CallbackInlineButton;
use losthost\BlagoBot\handlers\CallbackMakeReport;
use losthost\BlagoBot\handlers\CallbackEditUser;

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

require 'vendor/autoload.php';
require 'src/functions.php';
require 'src/show.php';

BotView::setTemplateDir(__DIR__. '/templates');

Bot::setup();

menu::initDataStructure();
user::initDataStructure();
department::initDataStructure();
user_dept_binding::initDataStructure();

x_omsu::initDataStructure();
x_object::initDataStructure();
x_year_data::initDataStructure();
x_contract::initDataStructure();
x_contract_data::initDataStructure();
x_contragent::initDataStructure();
x_prev::initDataStructure();

report::initDataStructure();
report_param::initDataStructure();
report_param_value::initDataStructure();

Bot::param('superadmin', 203645978);
Bot::param('topmenu_id', 1);

Bot::addHandler(PreMessage::class);
Bot::addHandler(MessageAuth::class);
Bot::addHandler(CommandStart::class);
Bot::addHandler(MessageFile2::class);
Bot::addHandler(CommandUpdate::class);
Bot::addHandler(CommandUsers::class);
Bot::addHandler(CommandXUsers::class);
Bot::addHandler(CommandDigits::class);
Bot::addHandler(CommandTest::class);
Bot::addHandler(CommandHelp::class);
Bot::addHandler(CommandStat::class);


Bot::addHandler(PreCallback::class);
Bot::addHandler(CallbackAddUser::class);
Bot::addHandler(CallbackInlineButton::class);
Bot::addHandler(CallbackMakeReport::class);
Bot::addHandler(CallbackEditUser::class);

Bot::run();

