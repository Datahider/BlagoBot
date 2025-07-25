<?php

namespace losthost\BlagoBot;

use losthost\BlagoBot\data\user;
use losthost\telle\Bot;
use losthost\passg\Pass;
use losthost\templateHelper\Template;
use losthost\DB\DBView;

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

function addBUser($tg_id, $access_level) : user {
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
        case user::AL_UNKNOWN:
            $user->access_level = user::AL_UNKNOWN;
            break;
        default:
            throw new \Exception("Unknown access level $access_level");
    }

    if ($user->isModified()) {
        $user->write();
    }
    return $user;
}

function __(string $string) : string {
    return $string;
}

function checkAdmin(?int $message_id=null) {
    global $b_user;
    
    if ($b_user->access_level === user::AL_ADMIN) {
        return true;
    } else {
        showAdminsOnly($message_id);
        return false;
    }
}

function sendSplitMessage($chat_id, $text, $separator=null, $send_copy=false) : void {
    
    $messages = explode(
            is_null($separator) ? '<!-- SPLIT -->' : $separator, 
            $text);
    
    $copy = false;
    if ($send_copy) {
        $copy = new DBView('SELECT copy_user_id FROM [copy_message] WHERE user_id=?', [$chat_id]);
    }
    
    foreach ($messages as $message) {
        
        while (true) {
            if ($message) {
                sendMessageWithRetry($chat_id, $message, 'HTML');
                if ($copy) {
                    $copy->reset();
                    while ($copy->next()) {
                        sendMessageWithRetry($copy->copy_user_id, $message, 'HTML');
                    }
                }
            }
            break;
        }
    }
}

function sendMessageWithRetry($chat_id, $message, $parse_mode, $max_retries=3) {
    
    for ($i=0;$i<$max_retries;$i++) {
        try {
            Bot::$api->sendMessage($chat_id, $message, $parse_mode);
            break;
        } catch (\Exception $exc) {
            $m = [];
            if (preg_match("/^Too Many Requests: retry after (\d+)/", $exc->getMessage(), $m)) {
                sleep($m[1]);
            } else {
                throw $exc;
            }
        }
    }

}

function procedurePlanFact(string $title, ?\DateTimeImmutable $db_plan, ?\DateTimeImmutable $db_fact) : string {
    $now = date_create_immutable('midnight');    $ts_now = $now->getTimestamp();
    $plan = $db_plan ? $db_plan->modify("midnight") : null;
    $fact = $db_fact ? $db_fact->modify("midnight") : null;
    
    if (!$plan) {
        $icon = '◽️';
        $date = '(-)';
    } elseif (!$fact && $plan->getTimestamp() > $ts_now+259200) {
        $icon = '◽️';
        $date = $plan->format('d.m.Y');
    } elseif (!$fact && $plan->getTimestamp() == $ts_now+259200) {
        $icon = '⚠️';
        $date = '<b>Через 3 дня</b>('. $plan->format('d.m.Y'). ')';
    } elseif (!$fact && $plan->getTimestamp() == $ts_now+172800) {
        $icon = '⚠️';
        $date = '<b>Послезавтра</b> ('. $plan->format('d.m.Y'). ')';
    } elseif (!$fact && $plan->getTimestamp() == $ts_now+86400) {
        $icon = '⚠️';
        $date = '<b>Завтра</b> ('. $plan->format('d.m.Y'). ')';
    } elseif (!$fact && $plan->getTimestamp() == $ts_now) {
        $icon = '⚠️';
        $date = '<b>Сегодня!</b> ('. $plan->format('d.m.Y'). ')';
    } elseif (!$fact && $plan->getTimestamp() < $ts_now) {
        $icon = '❗️';
        $date = '<b>Просрочено</b> ('. $plan->format('d.m.Y'). ')';
    } elseif ($fact) {
        $icon = '✅';
        $date = '<b>Исполнено</b>';
    }
    
    return "$icon $title - $date";
}

function objectHasDelays(array $object_data) : bool {
    $status = objectCommonStatus($object_data);
    switch ($status) {
        case '✅ Объект открыт! Поздравляем!':
        case '🟩 Объект в графике.':
        case '🟡 <b>По объекту на проставлены плановые даты процедур!</b>':
            return false;
    }
    return true;
}

function objectCommonStatus(array $object_data) : string {
    
    $procedures = [
        'open_date_planned' => 'open_date_fact',
        'ksmr_plan' => 'ksmr_fact',
        'psmr_plan' => 'psmr_fact',
        'rgmin_in_plan' => 'rgmin_in_fact',
        'moge_out_plan' => 'moge_out_fact',
        'moge_in_plan' => 'moge_in_fact',
    ];

    if ($object_data['open_date_fact']) {
        return '✅ Объект открыт! Поздравляем!';
    } else {
        $now = date_create_immutable()->getTimestamp();
        $has_delay = null;
        foreach ($procedures as $db_plan => $db_fact) {
            $plan = $object_data[$db_plan] ? $object_data[$db_plan]->getTimestamp() : null;
            $fact = $object_data[$db_fact] ? $object_data[$db_fact]->getTimestamp() : null;
            
            if ($plan && !$fact && $plan > $now) {
                $has_delay = false;
                continue;
            } elseif ($plan && !$fact && $now-$plan >= 1209600) {
                return '‼️ <b>Риск срыва срока открытия!</b>';
            } elseif ($plan && !$fact && $now-$plan < 1209600) {
                return '⚠️ <b>Есть просрочки, необходимо ускориться.</b>';
            } elseif ($plan && $fact && $plan >= $fact) {
                return '🟩 Объект в графике.';
            } elseif ($plan && $fact && $fact-$plan >= 1209600) {
                return '‼️ <b>Риск срыва срока открытия!</b>';
            } elseif ($plan && $fact && $fact-$plan < 1209600) {
                return '⚠️ <b>Есть просрочки, необходимо ускориться.</b>';
            }
        }
        
        if ($has_delay === null) {
            return '🟡 <b>По объекту на проставлены плановые даты процедур!</b>';
        }
        
        return '🟩 Объект в графике.';
    }
}

function isWoman(string $name) : bool {
    $women_names = [
        "Августа", "Августина", "Авдотья", "Авигея", "Аврора", "Автоноя", "Агапия", "Агата", "Агафья", "Аглая", "Агнес", 
        "Агнесса", "Агнета", "Агния", "Агриппина", "Агунда", "Ада", "Аделаида", "Аделина", "Аделия", "Адель", "Адельфина", 
        "Адиля", "Адриана", "Адриенна", "Аза", "Азалия", "Азиза", "Аида", "Айганым", "Айгерим", "Айгуль", "Айжан", "Айлин", 
        "Айнагуль", "Аксинья", "Акулина", "Алана", "Алдона", "Алевтина", "Александра", "Александрина", "Алёна", "Алеся", 
        "Алика", "Алико", "Алима", "Алина", "Алира", "Алиса", "Алия", "Алла", "Алсу", "Алтжин", "Альба", "Альберта", "Альбина", 
        "Альвина", "Альжбета", "Альфия", "Альфреда", "Аля", "Амалия", "Амаль", "Аманда", "Амелия", "Амина", "Амира", "Анаис", 
        "Анаит", "Анастасия", "Ангелина", "Андриана", "Анеля", "Анжела", "Анжелика", "Анжиолетта", "Анисия", "Анисья", "Анита", 
        "Анна", "Антонина", "Анфиса", "Анэля", "Аполлинария", "Аполлония", "Арабелла", "Ариадна", "Ариана", "Арина", "Артемида", 
        "Архелия", "Арьяна", "Асель", "Асида", "Асия", "Ассоль", "Астра", "Астрид", "Ася", "Аурелия", "Афанасия", "Аэлита", 
        "Аюна", "Бажена", "Беата", "Беатриса", "Белинда", "Белла", "Бенедикта", "Береслава", "Берта", "Биргит", "Бирута", 
        "Богдана", "Божена", "Борислава", "Бриллиант", "Бронислава", "Валентина", "Валерия", "Ванда", "Ванесса", "Варвара", 
        "Василина", "Василиса", "Васса", "Венди", "Венера", "Вера", "Верона", "Вероника", "Версавия", "Веселина", "Весна", 
        "Весняна", "Веста", "Вета", "Ветта", "Вида", "Видана", "Викторина", "Виктория", "Вилена", "Вилора", "Винетта", 
        "Виоланта", "Виолетта", "Виргиния", "Виринея", "Вита", "Виталина", "Влада", "Владислава", "Владлена", "Властилина", 
        "Габи", "Габриеле", "Габриэлла", "Галина", "Галия", "Гаянэ", "Гелана", "Гелена", "Гелианна", "Гелла", "Генриетта", 
        "Георгина", "Гера", "Герда", "Гертруда", "Глафира", "Гликерия", "Глория", "Гоар", "Говхар", "Горица", "Гортензия", 
        "Гражина", "Грета", "Гузель", "Гулия", "Гульмира", "Гульназ", "Гульнара", "Гульшат", "Гуннхильда", "Гюзель", 
        "Гюльджан", "Дайна", "Далия", "Дамира", "Дана", "Даниэла", "Дания", "Данна", "Данута", "Дара", "Дарерка", "Дарина", 
        "Дария", "Дарья", "Дарьяна", "Даша", "Даяна", "Дебора", "Джамиля", "Джанет", "Джема", "Джемма", "Дженифер", "Дженна", 
        "Дженнифер", "Джессика", "Джоан", "Джулия", "Джульетта", "Диана", "Дилара", "Дильназ", "Дильнара", "Диля", "Дина", 
        "Динара", "Динора", "Диодора", "Дионисия", "Дита", "Диша", "Долорес", "Доля", "Доминика", "Домна", "Дора", "Дэнна", 
        "Ева", "Евангелина", "Евгения", "Евдокия", "Евпраксия", "Евфимия", "Екатерина", "Елена", "Елизавета", "Ермиония", 
        "Есения", "Ефимия", "Жаклин", "Жанетт", "Жанна", "Жасмин", "Женевьева", "Жозефина", "Жюли", "Забава", "Заира", 
        "Залина", "Замира", "Зара", "Зарема", "Зарина", "Захария", "Земфира", "Зинаида", "Зита", "Злата", "Златослава", 
        "Зоряна", "Зоя", "Зульфия", "Зухра", "Иванна", "Ивета", "Иветта", "Ивона", "Ида", "Изабелла", "Изольда", "Илария", 
        "Илена", "Илзе", "Илиана", "Илона", "Ильзе", "Инара", "Инга", "Инге", "Ингеборга", "Индира", "Инесса", "Инна", 
        "Иоанна", "Иоланта", "Ираида", "Ирена", "Ирина", "Ирма", "Ирэн", "Ирэна", "Искра", "Иулия", "Июлия", "Ия", "Йенни", 
        "Кайли", "Калерия", "Камилла", "Камиля", "Капитолина", "Кара", "Карен", "Карима", "Карина", "Карла", "Кармелитта", 
        "Каролина", "Катарина", "Каторина", "Келен", "Кира", "Кирилла", "Клавдия", "Клара", "Клариса", "Кларисса", "Клементина", 
        "Климентина", "Констанция", "Консуэло", "Кора", "Корнелия", "Крис", "Кристина", "Ксения", "Лада", "Лайма", "Лали", 
        "Ламия", "Лана", "Ландыш", "Лаодика", "Лара", "Лариса", "Лаура", "Лейла", "Лейсан", "Леля", "Леокадия", "Леонида", 
        "Лера", "Леся", "Лея", "Лиана", "Лигия", "Лидия", "Лиза", "Лика", "Лили", "Лилиана", "Лилия", "Лилу", "Лина", "Линда", 
        "Линнея", "Лиора", "Лира", "Лия", "Лола", "Лолита", "Лора", "Луиза", "Лукерья", "Лукия", "Лунара", "Любава", "Любовь", 
        "Любомила", "Людмила", "Людовика", "Люция", "Ляля", "Магали", "Магда", "Магдалина", "Мадина", "Майя", "Малика", 
        "Мальвина", "Мальта", "Мара", "Маргарет", "Маргарита", "Марианна", "Марика", "Марина", "Мариса", "Марисоль", "Мариша", 
        "Мария", "Марлен", "Марселина", "Марта", "Мартина", "Маруся", "Марфа", "Марьям", "Марьяна", "Мастридия", "Матильда", 
        "Матрёна", "Мафтуха", "Мелания", "Мелиана", "Мелисса", "Мелитта", "Мериса", "Мика", "Мила", "Милада", "Милана", "Милда", 
        "Милена", "Милиса", "Милица", "Милолика", "Милослава", "Мира", "Мирдза", "Мирей", "Миропия", "Мирослава", "Мирра", 
        "Михайлина", "Михримах", "Мишель", "Мия", "Мод", "Моник", "Моника", "Муза", "Мэдисон", "Мэри", "Мю", "Надежда", 
        "Наджия", "Надия", "Надя", "Назгуль", "Назира", "Наиля", "Наима", "Нана", "Нания", "Наоми", "Наталия", "Наталья", 
        "Нателла", "Нева", "Нега", "Нелли", "Неолина", "Неонила", "Неонилла", "Ника", "Никки", "Николетта", "Николь", 
        "Нила", "Нилуфар", "Нина", "Нинель", "Нинна", "Ноа", "Номи", "Нонна", "Нора", "Нурия", "Нэнси", "Одетта", "Оксана", 
        "Октавия", "Октябрина", "Олеся", "Оливия", "Олимпиада", "Ольга", "Офелия", "Павла", "Павлина", "Памела", "Патрисия", 
        "Патриция", "Пелагея", "Перизат", "Полианна", "Полина", "Прасковья", "Рада", "Радмила", "Радосвета", "Радослава", 
        "Раиса", "Ралина", "Рамина", "Рая", "Ревекка", "Регина", "Рема", "Рената", "Риана", "Римма", "Рина", "Рита", "Роберта", 
        "Рогнеда", "Роза", "Розалина", "Розалия", "Роксалана", "Роксана", "Романа", "Ростислава", "Рузалия", "Рузанна", 
        "Рузиля", "Румия", "Русалина", "Руслана", "Руфина", "Сабина", "Сабрина", "Сажида", "Саида", "Саломея", "Самира", "Санда", 
        "Сандра", "Сания", "Санта", "Сара", "Сарра", "Сати", "Сафина", "Светлана", "Святослава", "Севара", "Северина", "Селена", 
        "Серафима", "Силика", "Сильва", "Сильвия", "Сима", "Симона", "Слава", "Снежана", "Созия", "Соня", "София", "Софья", 
        "Станислава", "Стелла", "Стефания", "Сусанна", "Таира", "Таисия", "Тала", "Тамара", "Тамила", "Тара", "Татьяна", 
        "Теодора", "Тереза", "Тина", "Томила", "Тора", "Триана", "Ульяна", "Урсула", "Устина", "Устинья", "Фаиза", "Фаина", 
        "Фания", "Фаня", "Фарида", "Фатима", "Фая", "Фекла", "Фелиция", "Феруза", "Физура", "Флора", "Франсуаза", "Француаза", 
        "Фредерика", "Фрида", "Харита", "Хилари", "Хильда", "Хлоя", "Христина", "Христя", "Цветана", "Цецилия", "Челси", "Чеслава", 
        "Чулпан", "Шакира", "Шарлотта", "Шейла", "Шелли", "Шерил", "Эвелина", "Эвита", "Эдда", "Эдита", "Элеонора", "Элиана", 
        "Элиза", "Элина", "Элла", "Эллада", "Элоиза", "Эльвина", "Эльвира", "Эльга", "Эльза", "Эльмира", "Эльнара", "Эля", "Эмили", 
        "Эмилия", "Эмма", "Эрика", "Эрнестина", "Эсмеральда", "Этель", "Этери", "Юзефа", "Юлия", "Юна", "Юния", "Юнона", "Юханна", 
        "Ядвига", "Яна", "Янина", "Янита", "Янка", "Ярина", "Ярослава", "Ясмина"        
    ];
    
    if (array_search($name, $women_names) !== false) {
        return true;
    }
    return false;
}

function log_memory_usage() {
    $usage = round(memory_get_usage() / 1024 / 1024, 1);
    $real = round(memory_get_usage(true) / 1024 / 1024, 1);
    
    error_log("memory: $usage Mb; real: $real Mb;");
}

function getClassIndex($object_name_index) {
    
    $known = [
        params\ParamDescriptionAddressee::class,
        params\ParamDescriptionCategory::class,
        params\ParamDescriptionCategory2All::class,
        params\ParamDescriptionCompletion::class,
        params\ParamDescriptionDataIncluded::class,
        params\ParamDescriptionMessageType::class,
        params\ParamDescriptionOmsu::class,
        params\ParamDescriptionOmsuAll::class,
        params\ParamDescriptionOpenYear::class,
        params\ParamDescriptionPeriod::class,
        params\ParamDescriptionPeriodSingle::class,
        params\ParamDescriptionReadyGroupBy::class,
        params\ParamDescriptionReadySortBy::class,
        params\ParamDescriptionResponsible::class,
        params\ParamDescriptionResponsibleAll::class,
        params\ParamDescriptionRiskyOnly::class,
        params\ParamDescriptionSelfCopy::class,
        params\ParamDescriptionSources::class,
        params\ParamDescriptionWinners::class,
        params\ParamDescriptionYearFull::class,
        params\ParamDescriptionYearLast::class,
        
        
        reports\ReportReady::class,
        reports\ReportCertificate::class,
        reports\ReportCertificateForOmsu::class,
        reports\ReportGP::class,
        reports\ReportObjectsByActivity::class,
        reports\ReportObjectsByOmsu::class,
        reports\ReportObjectsForOmsu::class,
        reports\ReportParams::class,
        reports\ReportStat::class,
        reports\ReportStatusSender::class,
        reports\ReportStatusSenderForOmsu::class,
        reports\ReportStatusSenderForResponsible::class,
        reports\ReportStatusSenderTotal::class,
        reports\ReportUsers::class,
        reports\ReportStatusSenderAuto::class,
        reports\ReportStatusSenderForResponsible::class,
        reports\ReportStatusSenderNY::class,
        reports\ReportStatusSenderAutoNY::class,
        reports\ReportStatusSenderForResponsibleNY::class,
        reports\ReportObjectsForOmsu::class,
        reports\ReportCertificateForOmsu::class,
        reports\ReportStatusSenderForOmsu::class,
        reports\ReportWinners::class,
        reports\ReportOPZStatus::class,
        
    ];
    
    if (is_numeric($object_name_index)) {
        return isset($known[$object_name_index]) ? $known[$object_name_index] : null;
    } elseif (is_string($object_name_index)) {
        $result = array_search($object_name_index, $known);
        if ($result === false) {
            return null;
        }
        return $result;
    } else {
        $result = array_search(get_class($object_name_index), $known);
        if ($result === false) {
            return null;
        }
        return $result;
    }
}