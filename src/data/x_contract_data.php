<?php

namespace losthost\BlagoBot\data;

use losthost\DB\DBObject;

class x_contract_data extends DBObject {
    
    const TYPE_RG_FB = "РГ ФБ";
    const TYPE_RG_BM = "РГ БМ";
    const TYPE_RG_BMO = "РГ БМО";
    const TYPE_RG_OMSU = "РГ ОМСУ";
    const TYPE_RG_OMSU2 = "РГ ОМСУ2";
    const TYPE_NMCK_FB = "Нмцк ФБ";
    const TYPE_NMCK_BM = "Нмцк БМ";
    const TYPE_NMCK_BMO = "Нмцк БМО";
    const TYPE_NMCK_OMSU = "Нмцк ОМСУ";
    const TYPE_NMCK_OMSU2 = "Нмцк ОМСУ2";
    const TYPE_CONTRACT_FB = "Контракт ФБ";
    const TYPE_CONTRACT_BM = "Контракт БМ";
    const TYPE_CONTRACT_BMO = "Контракт БМО";
    const TYPE_CONTRACT_OMSU = "Контракт ОМСУ";
    const TYPE_CONTRACT_OMSU2 = "Контракт ОМСУ2";
    const TYPE_ORDER_FB = "Заявка ФБ";
    const TYPE_ORDER_BM = "Заявка БМ";
    const TYPE_ORDER_BMO = "Заявка БМО";
    const TYPE_ORDER_OMSU = "Заявка ОМСУ";
    const TYPE_ORDER_OMSU2 = "Заявка ОМСУ2";
    const TYPE_PAYMENT_FB = "Оплата ФБ";
    const TYPE_PAYMENT_BM = "Оплата БМ";
    const TYPE_PAYMENT_BMO = "Оплата БМО";
    const TYPE_PAYMENT_OMSU = "Оплата ОМСУ";
    const TYPE_PAYMENT_OMSU2 = "Оплата ОМСУ2";
    const TYPE_CRITERIA = "Критерий";
    const TYPE_REDUCTION = "Снятие";
    
    const METADATA = [
        'id' => 'BIGINT(20) NOT NULL AUTO_INCREMENT',
        'year' => 'INT(11) NOT NULL', 
        'x_contract_id' => 'BIGINT(20) NOT NULL',
        'type' => 'ENUM("РГ ФБ","РГ БМ","РГ БМО","РГ ОМСУ","РГ ОМСУ2","Нмцк ФБ","Нмцк БМ","Нмцк БМО","Нмцк ОМСУ","Нмцк ОМСУ2","Контракт ФБ","Контракт БМ","Контракт БМО","Контракт ОМСУ","Контракт ОМСУ2","Заявка ФБ","Заявка БМ","Заявка БМО","Заявка ОМСУ","Заявка ОМСУ2","Оплата ФБ","Оплата БМ","Оплата БМО","Оплата ОМСУ","Оплата ОМСУ2","Критерий","Снятие") NOT NULL',
        'value' => 'DECIMAL',
        'note' => 'VARCHAR(1024)',
        'PRIMARY KEY' => 'id',
        'UNIQUE INDEX YOT' => ['year', 'x_contract_id', 'type']
    ];
}
