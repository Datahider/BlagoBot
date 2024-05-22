<?php

namespace losthost\BlagoBot\service;

use losthost\BlagoBot\data\user;
use losthost\telle\Bot;

class AccessChecker {
    
    protected array $roles;
    
    public function __construct(array|string $roles) {
        if (is_string($roles)) {
            $this->roles = [$roles];
        } else {
            $this->roles = $roles;
        }
    }
    
    public function isAllowed() {
        $user = new user(['tg_user' => Bot::$user->id], true);
        if ($user->isNew()) {
            return false;
        } elseif (array_search($user->access_level, $this->roles) !== false) {
            return true;
        }
    }
    
    public function isDenied() {
        return !$this->isAllowed();
    }
}
