<?php
date_default_timezone_set('America/Sao_Paulo');
require __DIR__ . '/../vendor/autoload.php';

//217412325991-uq0s46p42lh12c0mimmmu97trep387r5.apps.googleusercontent.com
//-9j9tLJ-N1IS3CJVLidxfpK0

$calendar = new ThiagoSV\GoogleCalendar\Calendar;
$client = $calendar->getClient();

if(!$client){
    echo "<b>{$calendar->getTrigger()}</b>";
    $linkAuth = $calendar->createClient();
    echo "<a href='{$linkAuth}'>Conceder permissao para gerenciar o Google Calendar</a>";
}else{
    echo "Suas credenciais sao validas!";
    //exemplo - criacao de evento
    $event = $calendar->createEvent('sumario', 'Rua Adao dos santos 96, Porto alegre, Rio grande do sul', 'teste de descricao', '2018-01-17 20:00:00', '2018-01-17 21:00:00', 'thiagosv97@gmail.com');
    var_dump($event);

}