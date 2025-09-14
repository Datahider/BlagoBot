<?php

namespace losthost\BlagoBot\data;

use losthost\DB\DBObject;
use losthost\DB\DB;
use losthost\DB\DBView;
use losthost\templateHelper\Template;

class user extends DBObject {
    
    const AL_ADMIN = 'admin';
    const AL_OPERATOR = 'operator';
    const AL_USER = 'user';
    const AL_RESTRICTED = 'restricted';
    const AL_UNKNOWN = 'unknown';
    
    const METADATA = [
        'id' => 'BIGINT(20) NOT NULL AUTO_INCREMENT',
        'tg_user' => 'BIGINT NOT NULL',
        'access_level' => 'ENUM("admin", "operator", "user", "restricted", "unknown")',
        'surname' => 'VARCHAR(50)',
        'name' => 'VARCHAR(50)',
        'fathers_name' => 'VARCHAR(50)',
        'email' => 'VARCHAR(50)',
        'phone' => 'VARCHAR(14)',
        'phone2' => 'VARCHAR(14)',
        'notes' => 'VARCHAR(1024)',
        'ai_context_starts' => "DATETIME NOT NULL DEFAULT '2025-08-04 00:00:00'",
        'PRIMARY KEY' => 'id', 
        'UNIQUE INDEX TG_USER' => 'tg_user', 
    ];
    
    public function getBindings() : array {
        $sth = DB::prepare(<<<FIN
            SELECT
                    omsu.id AS id,
                    omsu.name AS omsu_name,
                    'head' AS role 
            FROM 
                    [x_omsu] AS omsu
            WHERE
                    omsu.head_id = ?

            UNION ALL

            SELECT
                    omsu.id,
                    omsu.name,
                    'vicehead' 
            FROM 
                    [x_omsu] AS omsu
            WHERE
                    omsu.vicehead_id = ?

            FIN);

        $sth->execute([$this->id, $this->id]);

        $result = [];
        while ($row = $sth->fetch(\PDO::FETCH_NUM)) {
            $result[] = $row;
        }

        return $result;
    }
    
    public function getFIO() : string|false {
        if ($this->surname === null) {
            return false;
        }
        
        return "$this->surname $this->name $this->fathers_name";
    }
    
    public function getName() : string {
        $result = $this->getFIO();
        
        if ($result === false) {
            $result = $this->getTelegramName();
        }
        
        return $result;
    }
    
    public function getTelegramName() {
        $tg_user = new DBView("SELECT * FROM [telle_users] WHERE id = ?", [$this->tg_user]);
        if (!$tg_user->next()) {
            return 'Пользователь не заходил в бота';
        }
        
        $result = $tg_user->first_name;
        
        if ($tg_user->last_name) {
            $result .= " $tg_user->last_name";
        }
        
        if ($tg_user->username) {
            $result .= " (@$tg_user->username)";
        }
        
        return $result;
    }
    
    public function aiContextAddMessage($message_data) {
        if (empty($message_data['role'])) {
            throw new \Exception('Не задано свойство "role"');
        }

        $this->aiCheckAddPrompt();
        
        $role = $message_data['role'];
        $text = $message_data['text'] ?? null;
        
        $ai_context = new static();
        $ai_context->date_added = date_create();
        $ai_context->user_id = $this->id;
        $ai_context->role = $role;
        $ai_context->text = $text;
        $ai_context->data = serialize($message_data);
        $ai_context->write();
        
        return $ai_context;
    }
    
    public function aiContextCountMessages() : int {

        $sql = $this->sqlContextCountMessages();
        
        $sth = DB::prepare($sql);
        $sth->execute([$this->id, $this->ai_context_starts->format(DB::DATE_FORMAT)]);
        
        $row = $sth->fetch(\PDO::FETCH_NUM);
        return $row[0];
        
    }
    
    protected function aiCheckAddPrompt() {
        if ($this->aiContextCountMessages() != 0) {
            return;
        }
        
        $this->aiAddPrompt();
    }

    protected function aiAddPrompt() {
        
        $prompt = [
            'role' => 'system',
            'text' => $this->getPromptText()
        ];
        
        $ai_context = new ai_context();
        $ai_context->date_added = date_create();
        $ai_context->user_id = $this->id;
        $ai_context->role = $prompt['role'];
        $ai_context->text = $prompt['text'];
        $ai_context->data = serialize($prompt);
        $ai_context->write();

    }

    protected function aiGetPromptText() {
        
        $prompt_template = new Template('prompt.php', Bot::$language_code);
        $prompt_template->setTemplateDir('src/templates');
        
        $vars = [
            'name' => $this->name,
            'fathers_name' => $this->fathers_name,
        ];
        
        foreach ($vars as $key => $value) {
            $prompt_template->assign($key, $value);
        }
        
        return $prompt_template->process();
    }
    
    protected function sqlAiContextCountMessages() {
        return <<<FIN
            SELECT
                COUNT(id)
            FROM
                [ai_context]
            WHERE
                user_id = ?
                AND date_added >= ?
            FIN;
    }
    
    
}
