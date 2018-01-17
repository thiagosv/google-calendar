<?php
/**
 * Created by PhpStorm.
 * User: Thiago
 * Date: 16/01/2018
 * Time: 18:10
 */

$code = filter_input(INPUT_GET, 'code', FILTER_DEFAULT);

if(!empty($code)){
    require __DIR__ . '/../vendor/autoload.php';
    $calendar = new \ThiagoSV\GoogleCalendar\Calendar;

    $calendar->setAccessToken($code);
    header("Location: http://localhost/google-calendar/test");
}