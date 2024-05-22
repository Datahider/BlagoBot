<?php

namespace losthost\BlagoBot\handlers;

use losthost\telle\abst\AbstractHandlerCallback;
use losthost\telle\Bot;

abstract class _Callback extends AbstractHandlerCallback {

    protected function handle(\TelegramBot\Api\Types\CallbackQuery &$callback_query): bool {
        try { Bot::$api->answerCallbackQuery($callback_query->getId()); } catch (\Exception $e) {}
        return true;
    }
}
