<?php
/**
 * Created by PhpStorm.
 * User: Thiago
 * Date: 17/01/2018
 * Time: 01:30
 */

namespace ThiagoSV\GoogleCalendar;

use ThiagoSV\ControllerPDO;

class CalendarBD{

    private $trigger;

    public function __construct(){

    }

    public function createEvent($event){
        $params = [
            'appointment_title' => $event->getSummary(),
            'appointment_description' => $event->getDescription(),
            'appointment_location' => $event->getLocation(),
            'appointment_event_id' => $event->getId(),
            'appointment_start' => date('Y-m-d H:i:s', strtotime($event->getStart()->getDateTime())),
            'appointment_end' => date('Y-m-d H:i:s', strtotime($event->getEnd()->getDateTime()))
        ];

        $create = new ControllerPDO\Create;
        $create->create("appointment", $params);

        if(empty($create->getResult())){
            $this->trigger = ['error', 'Erro ao cadastrar evento, se persistir entre em contato com o administrador.'];
        }else{
            if(!empty($event->getAttendees)){
                $this->createAttendees($event->getAttendees(), $create->getResult());
            }
            if(empty($this->trigger)){
                $this->trigger = ['success', 'Evento cadastrado com sucesso!'];
            }
        }
    }

    public function deleteEvent($id){
        if(empty($this->getEvent($id))){
            $this->trigger = ['error', 'Evento não encontrado para delete.'];
        }else{
            $delete = new ControllerPDO\Delete;
            $delete->delete("appointment", 'WHERE appointmente_id = :id', "id={$id}");
            if(empty($delete->getRowCount())){
                $this->trigger = ['error', 'Erro ao deletar evento, se persistir entre em contato com o administrador.'];
            }else{
                $this->trigger = ['success', 'Evento deletado com sucesso.'];
            }
        }
    }

    public function getTrigger(){
        return $this->trigger;
    }

    public function getEvent($id = NULL){
        $read = new ControllerPDO\Read;
        if(empty($id)){
            $read->read(self::$table);
        }else{
            $read->read(self::$table, 'WHERE appointment_id = :id', "id={$id}");
        }
        return $read->getResult();
    }

    private function createAttendees($attendeesList, $appointmentId){
        foreach($attendeesList as $attendees){
            $params = [
                'attendees_appointment_id' => $appointmentId,
                'attendees_name' => $attendees->getDisplayName(),
                'attendees_email' => $attendees->getEmail()
            ];

            $create = new ControllerPDO\Create;
            $create->create("attendees", $params);

            if(empty($create->getResult())){
                $this->trigger = ['error', 'Erro ao cadastrar participantes do evento no banco de dados.'];
            }
        }
    }
}