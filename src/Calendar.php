<?php
/**
 * Created by PhpStorm.
 * User: Thiago
 * Date: 16/01/2018
 * Time: 17:38
 */

namespace ThiagoSV\GoogleCalendar;

class Calendar {
    private $client;
    private $trigger;
    private $event;
    private $params;
    private $service;
    private $colors;
    
    /**
     * Calendar constructor.
     * Método responsável por inicializar a comunicação com a API do Google
     * @throws \Google_Exception
     */
    public function __construct($ajax = NULL, $path_user = NULL) {
        
        if ($path_user) {
            $path_user = '/' . $path_user;
        }
        if ($ajax) {
            $ajax = '../';
        }
        
        if (!defined('APPLICATION_NAME')) {
            define('APPLICATION_NAME', 'MLBIDDING');
        }
        if (!defined('CREDENTIALS_PATH')) {
            define('CREDENTIALS_PATH', "~/.credentials{$path_user}/google-calendar.json");
        }
        if (!defined('CLIENT_SECRET_PATH')) {
            if ($ajax) {
                define('CLIENT_SECRET_PATH', '../client_secret.json');
            } else {
                define('CLIENT_SECRET_PATH', 'client_secret.json');
            }
        }
        if (!defined('SCOPES')) {
            define('SCOPES', implode(' ', [\Google_Service_Calendar::CALENDAR]));
        }
        
        $this->client = new \Google_Client();
        $this->client->setApplicationName(APPLICATION_NAME);
        $this->client->setScopes(SCOPES);
        $this->client->setAuthConfig(CLIENT_SECRET_PATH);
        $this->client->setAccessType('offline');
    }
    
    /**
     * <b>getTrigger:</b> Método responsável por retornar mensagem de erro
     * @return mixed
     */
    public function getTrigger() {
        return $this->trigger;
    }
    
    /**
     * <b>createClient:</b> Método responsável por gerar o link de autenticação da API
     * @return string
     */
    public function createClient() {
        $authUrl = $this->client->createAuthUrl();
        return $authUrl;
    }
    
    /**
     * <b>setAccessToken:</b> Método responsável por criar o credenciamento da API,
     * salvar o arquivo .json no diretório especificado da define
     * @param $authCode
     * @return bool
     */
    public function setAccessToken($authCode) {
        $accessToken = $this->client->fetchAccessTokenWithAuthCode($authCode);
        // Retorna o caminho absoluto
        $credentialsPath = $this->expandHomeDirectory(CREDENTIALS_PATH);
        // Cria o diretório de forma recursiva para armazenar o .json
        if (!file_exists(dirname($credentialsPath))) {
            mkdir(dirname($credentialsPath), 0700, TRUE);
        }
        // Salva o credenciamento dentro da pasta
        file_put_contents($credentialsPath, json_encode($accessToken));
        return TRUE;
    }
    
    /**
     * <b>getClient:</b> Método responsável por obter o client do Google
     * @return bool|\Google_Client
     */
    public function getClient() {
        // Load previously authorized credentials from a file.
        $credentialsPath = $this->expandHomeDirectory(CREDENTIALS_PATH);
        
        if (!file_exists($credentialsPath)) {
            $this->trigger = 'Não há credenciais definidas!';
            return FALSE;
        } else {
            $accessToken = json_decode(file_get_contents($credentialsPath), TRUE);
            $this->client->setAccessToken($accessToken);
        }
        
        if ($this->client->isAccessTokenExpired()) {
            if (is_file($credentialsPath)) {
                unlink($credentialsPath);
                return FALSE;
            }
        }
        
        // Refresh the token if it's expired. /* nao esta vindo o refreshToken, entao essa parte do codigo ira ficar comentada*/
//        if ($this->client->isAccessTokenExpired()) {
//            $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
//            file_put_contents($credentialsPath, json_encode($this->client->getAccessToken()));
//        }
        return $this->client;
    }
    
    /**
     * <b>createEvent:</b> Método responsável por criar o evento dentro do Google Calendar
     * @param STRING      $summary     = Título do Evento
     * @param STRING      $location    = Endereço completo do onde ocorrerá o evento
     * @param STRING      $description = Descrição do evento
     * @param DATETIME    $start       = Data e Hora no formato americano
     * @param DATETIME    $end         = Data e Hora no formato americano
     * @param null|STRING $attendees   = E-mail do Convidado
     * @return bool|\Google_Service_Calendar_Event
     */
    public function createEvent($summary, $location, $description, $start, $end, $attendees = NULL, $colorId = NULL) {
        if (date('Y-m-d H:i:s', strtotime($start)) < date('Y-m-d H:i:s')) {
            $this->trigger = "A data inicial é menor do que a data atual, por favor verifique e tente novamente!";
            return FALSE;
        }
        if (date('Y-m-d H:i:s', strtotime($end)) < date('Y-m-d H:i:s', strtotime($start))) {
            $this->trigger = "A data final é menor que a data de início, por favor verifique e tente novamente!";
            return FALSE;
        }
        $this->params = array('summary' => $summary, 'location' => $location, 'description' => $description, 'start' => array('dateTime' => date(DATE_ISO8601, strtotime($start)), 'timeZone' => 'America/Sao_Paulo',), 'end' => array('dateTime' => date(DATE_ISO8601, strtotime($end)), 'timeZone' => 'America/Sao_Paulo',), 'reminders' => array('useDefault' => FALSE, 'overrides' => array(array('method' => 'email', 'minutes' => 24 * 60), array('method' => 'popup', 'minutes' => 10),),),);
        if (!empty($colorId)) {
            $this->params += ['colorId' => $colorId];
        }
        if (!empty($attendees)) {
            $emails = [];
            foreach($attendees as $val) {
                if (filter_var($val, FILTER_VALIDATE_EMAIL)) {
                    $emails[] = ['email' => $val];
                }
            }
            if (!empty($emails)) {
                $this->params += ['attendees' => $emails];
            }
        }
        $this->event = new \Google_Service_Calendar_Event($this->params);
        $this->service = new \Google_Service_Calendar($this->client);
        $this->event = $this->service->events->insert('primary', $this->event, ['sendNotifications' => TRUE]);
        return $this->event;
    }
    
    /**
     * <b>deleteEvent:</b> Método responsável por deletar um evento do Google Calendar
     * @param STRING $eventId = ID do evento do Google
     */
    public function deleteEvent($eventId) {
        $this->service = new \Google_Service_Calendar($this->client);
        $this->service->events->delete('primary', $eventId, ['sendNotifications' => TRUE]);
    }
    
    /**
     * <b>getColors:</b> Método responsável por trazer as cores disponiveis no google calendar, para integracao com as cores.
     */
    public function getColors($color_id = NULL) {
        $this->service = new \Google_Service_Calendar($this->client);
        $this->colors = $this->service->colors->get();
        
        if (!empty($color_id)) {
            foreach($this->colors->getEvent() as $key => $color) {
                if ($key == $color_id) {
                    return $color;
                }
            }
        } else {
            return $this->colors->getEvent();
        }
        
    }
    
    public function getListEvents() {
        $this->service = new \Google_Service_Calendar($this->client);
        $events = $this->service->events->listEvents('primary');
        $eventos = [];
        while(TRUE) {
            foreach($events->getItems() as $event) {
                $eventos[] = $event;
            }
            $pageToken = $events->getNextPageToken();
            if ($pageToken) {
                $optParams = array('pageToken' => $pageToken);
                $events = $service->events->listEvents('primary', $optParams);
            } else {
                break;
            }
        }
        return $eventos;
    }
    
    /**
     * <b>expandHomeDirectory:</b> Método responsável por expandir o diretório e normalizar o caminho absoluto
     * @param STRING $path = Caminho que deseja ser verificado
     * @return mixed
     */
    private function expandHomeDirectory($path) {
        $homeDirectory = getenv('HOME');
        if (empty($homeDirectory)) {
            $homeDirectory = getenv('HOMEDRIVE') . getenv('HOMEPATH');
        }
        return str_replace('~', realpath($homeDirectory), $path);
    }
}