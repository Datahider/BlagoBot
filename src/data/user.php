<?php

namespace losthost\BlagoBot\data;

use losthost\DB\DBObject;
use losthost\DB\DB;

class user extends DBObject {
    
    const AL_ADMIN = 'admin';
    const AL_USER = 'user';
    const AL_RESTRICTED = 'restricted';
    const AL_UNKNOWN = 'unknown';
    
    const METADATA = [
        'id' => 'BIGINT(20) NOT NULL AUTO_INCREMENT',
        'tg_user' => 'BIGINT NOT NULL',
        'access_level' => 'ENUM("admin", "user", "restricted", "unknown")',
        'surname' => 'VARCHAR(50)',
        'name' => 'VARCHAR(50)',
        'fathers_name' => 'VARCHAR(50)',
        'email' => 'VARCHAR(50)',
        'phone' => 'VARCHAR(14)',
        'phone2' => 'VARCHAR(14)',
        'notes' => 'VARCHAR(1024)',
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
    
}
