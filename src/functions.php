<?php

namespace losthost\BlagoBot;

use losthost\BlagoBot\data\user;
use losthost\telle\Bot;
use losthost\passg\Pass;

function initBUser() {
    global $b_user;
    $b_user = new user(['tg_user' => Bot::$user->id], true);
    
    if ($b_user->isNew()) {
        if ($b_user->tg_user == Bot::param('superadmin', null)) {
            $b_user->access_level = 'admin';
        } else {
            $b_user->access_level = 'unknown';
        }
        $b_user->write();
    }
}

function addBUser($tg_id, $access_level) {
    $user = new user(['tg_user' => $tg_id], true);
    
    switch ($access_level) {
        case user::AL_ADMIN:
            $user->access_level = user::AL_ADMIN;
            break;
        case user::AL_USER:
            $user->access_level = user::AL_USER;
            break;
        case user::AL_RESTRICTED:
            $user->access_level = user::AL_RESTRICTED;
            break;
        default:
            throw new \Exception("Unknown access level $access_level");
    }

    if ($user->isModified()) {
        $user->write();
    }
}
