<?php

namespace losthost\BlagoBot;

use losthost\BlagoBot\data\user;
use losthost\telle\Bot;
use losthost\passg\Pass;

function initBUser() {
    global $b_user;
    $b_user = new user(['tg_user' => Bot::$user->id], true);
    
    if ($b_user->isNew()) {
        $b_user->is_admin = $b_user->tg_user == Bot::param('superadmin', null) ? true : false;
        $b_user->is_authorized = $b_user->is_admin ? true : false;
        $b_user->auth_code = Pass::generate(5, Pass::CLEAN_DIGITS);
        $b_user->write();
    }
}

function addBUser($tg_id, $as_admin = false) {
    $user = new user(['tg_user' => $tg_id], true);
    $user->is_admin = $as_admin;
    $user->is_authorized = true;
    if ($user->isModified()) {
        $user->write();
    }
}
