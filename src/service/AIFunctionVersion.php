<?php

namespace losthost\BlagoBot\service;

class AIFunctionVersion extends AIFunction {
    
    #[\Override]
    public function getResult(array $params): mixed {
        $version = $this->getLatestReleaseTag("Datahider/BlagoBot");
        
        switch ($version) {
            case null:
                return '0.0.0';
            case false:
                return 'Не удалось получить версию с сервера.';
            default:
                return $version;
        }
    }
    
    protected function getLatestReleaseTag(string $userRepo, string $token = null) {
        $url = "https://api.github.com/repos/$userRepo/releases/latest";

        $headers = "User-Agent: php\r\n";
        if ($token) {
            $headers .= "Authorization: token $token\r\n";
        }

        $opts = [
            "http" => [
                "method" => "GET",
                "header" => $headers
            ]
        ];

        $context = stream_context_create($opts);
        $response = @file_get_contents($url, false, $context);

        if ($response === false) {
            return false; // GitHub обиделся или интернета нет
        }

        $data = json_decode($response, true);

        // Если в ответе есть сообщение "Not Found", значит у репы релизов нет
        if (isset($data['message']) && $data['message'] === "Not Found") {
            return null; // релизов ещё нет
        }

        return $data['tag_name'] ?? null;
    }

}
