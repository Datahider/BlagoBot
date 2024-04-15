<?php

use losthost\telle\Bot;
use losthost\BotView\BotView;

use losthost\BlagoBot\handlers\PreMessage;
use losthost\BlagoBot\handlers\MessageAuth;
use losthost\BlagoBot\handlers\CommandStart;

use losthost\BlagoBot\handlers\PreCallback;
use losthost\BlagoBot\handlers\CallbackAddUser;
use losthost\BlagoBot\handlers\CallbackInlineButton;
use losthost\BlagoBot\handlers\CallbackMakeReport;

use losthost\BlagoBot\data\user;
use losthost\BlagoBot\data\menu;
use losthost\BlagoBot\data\report;
use losthost\BlagoBot\data\report_param;
use losthost\BlagoBot\data\report_param_value;

require 'vendor/autoload.php';
require 'src/functions.php';

BotView::setTemplateDir(__DIR__. '/templates');

Bot::setup();

menu::initDataStructure();
user::initDataStructure();
report::initDataStructure();
report_param::initDataStructure();
report_param_value::initDataStructure();

Bot::param('superadmin', 203645978);
Bot::param('topmenu_id', 1);

Bot::addHandler(PreMessage::class);
Bot::addHandler(MessageAuth::class);
Bot::addHandler(CommandStart::class);


Bot::addHandler(PreCallback::class);
Bot::addHandler(CallbackAddUser::class);
Bot::addHandler(CallbackInlineButton::class);
Bot::addHandler(CallbackMakeReport::class);

Bot::run();

