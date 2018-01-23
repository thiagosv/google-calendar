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

    public function createEvent($event, $background = NULL){
        $params = [
            'appointment_title' => $event->getSummary(),
            'appointment_description' => $event->getDescription(),
            'appointment_location' => $event->getLocation(),
            'appointment_event_id' => $event->getId(),
            'appointment_background' => $background,
            'appointment_start' => date('Y-m-d H:i:s', strtotime($event->getStart()->getDateTime())),
            'appointment_end' => date('Y-m-d H:i:s', strtotime($event->getEnd()->getDateTime()))
        ];

        $create = new ControllerPDO\Create;
        $create->create("appointment", $params);

        if(empty($create->getResult())){
            $this->trigger = ['error', 'Oops!', 'Erro ao cadastrar evento, se persistir entre em contato com o administrador.'];
        }else{
            if(!empty($event->getAttendees())){
                $this->createAttendees($event->getAttendees(), $create->getResult());
            }
            if(empty($this->trigger)){
                $this->trigger = ['success', 'Sucesso!', 'Evento cadastrado com sucesso!'];
            }
        }
    }

    public function deleteEvent($id){
        if(empty($this->getEvent($id))){
            $this->trigger = ['error', 'Oops!', 'Evento não encontrado para delete.'];
        }else{
            $delete = new ControllerPDO\Delete;
            $delete->delete("appointment", 'WHERE appointment_id = :id', "id={$id}");
            if(empty($delete->getRowCount())){
                $this->trigger = ['error', 'Oops!', 'Erro ao deletar evento, se persistir entre em contato com o administrador.'];
            }else{
                $this->trigger = ['success', 'Sucesso!', 'Evento deletado com sucesso.'];
            }
        }
    }

    public function getTrigger(){
        return $this->trigger;
    }

    public function getEvent($id = NULL, $limit = NULL){
        $read = new ControllerPDO\Read;
        $query = new ControllerPDO\QueryCreator();
        $query->from("appointment");
        $query->select("*");

        if(empty($id)){
            $dados = [];
            $i = 0;
            $query->where("appointment_end > now()");
            $query->order(["appointment_start" => "ASC"]);
            if(!empty($limit)){
                $query->limit($limit);
            }
            $read->readFull($query->getQuery(), $query->getPlaces());
            if($read->getResult()){
                $dados = $read->getResult();
                foreach($read->getResult() as $result){
                    $dados[$i] = $result;
                    $read->readFull("SELECT * FROM attendees WHERE attendees_appointment_id = :id", "id={$result['appointment_id']}");
                    if($read->getResult()){
                        $dados[$i]['attendees'] = $read->getResult();
                    }
                    $i++;
                }
            }
        }else{
            $dados = [];
            $i = 0;
            $query->where("appointment_id = :id");
            $query->places("id={$id}");
            $read->readFull($query->getQuery(), $query->getPlaces());
            if($read->getResult()){
                $dados = $read->getResult();
                foreach($read->getResult() as $result){
                    $dados[$i] = $result;
                    $read->readFull("SELECT * FROM attendees WHERE attendees_appointment_id = :id", "id={$result['appointment_id']}");
                    if($read->getResult()){
                        $dados[$i]['attendees'] = $read->getResult();
                    }
                    $i++;
                }
            }
        }
        return $dados;
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
                $this->trigger = ['error', 'Oops!', 'Erro ao cadastrar participantes do evento no banco de dados.'];
            }
        }
    }
}