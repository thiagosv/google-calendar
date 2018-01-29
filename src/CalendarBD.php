<?php
/**
 * Created by PhpStorm.
 * User: Thiago
 * Date: 17/01/2018
 * Time: 01:30
 */

namespace ThiagoSV\GoogleCalendar;

use ThiagoSV\ControllerPDO;

class CalendarBD
{

    private $trigger;
    private $query;
    private $appointment_usuario_id;

    public function __construct($appointment_user_id = NULL)
    {
        if(!empty($appointment_user_id)){
            $this->appointment_user_id = appointment_user_id;
        }
    }

    public function createEvent($event, $background = NULL)
    {
        $params = [
            'appointment_title' => $event->getSummary(),
            'appointment_description' => $event->getDescription(),
            'appointment_location' => $event->getLocation(),
            'appointment_event_id' => $event->getId(),
            'appointment_start' => date('Y-m-d H:i:s', strtotime($event->getStart()->getDateTime())),
            'appointment_end' => date('Y-m-d H:i:s', strtotime($event->getEnd()->getDateTime()))
        ];
        if (!empty($background) && is_array($background)) {
            $params += [
                'appointment_background' => $background[0],
                'appointment_foreground' => $background[1]
            ];
        }
        if (!empty($this->appointment_user_id)) {
            $params += [
                'appointment_user_id' => $this->appointment_user_id
            ];
        }
        $create = new ControllerPDO\Create;
        $create->create("appointment", $params);

        if (empty($create->getResult())) {
            $this->trigger = ['error', 'Oops!', 'Erro ao cadastrar evento, se persistir entre em contato com o administrador.'];
        } else {
            if (!empty($event->getAttendees())) {
                $this->createAttendees($event->getAttendees(), $create->getResult());
            }
            if (empty($this->trigger)) {
                $this->trigger = ['success', 'Sucesso!', 'Evento cadastrado com sucesso!'];
            }
        }
    }

    public function deleteEvent($id)
    {
        if (empty($this->getEvent($id))) {
            $this->trigger = ['error', 'Oops!', 'Evento não encontrado para delete.'];
        } else {
            $delete = new ControllerPDO\Delete;
            $delete->delete("appointment", 'WHERE appointment_id = :id', "id={$id}");
            if (empty($delete->getRowCount())) {
                $this->trigger = ['error', 'Oops!', 'Erro ao deletar evento, se persistir entre em contato com o administrador.'];
            } else {
                $this->trigger = ['success', 'Sucesso!', 'Evento deletado com sucesso.'];
            }
        }
    }

    public function getTrigger()
    {
        return $this->trigger;
    }

    public function getEvent($appointment_id = NULL, $appointment_event_id = NULL, $limit = NULL)
    {
        $read = new ControllerPDO\Read;
        $this->query = new ControllerPDO\QueryCreator();
        $this->query->from("appointment");
        $this->query->select("*");

        $dados = [];
        $i = 0;
        if (!empty($appointment_id)) {
            $this->query->where("appointment_id = :appointment_id");
            $this->query->places("appointment_id={$appointment_id}");
        }
        if (!empty($appointment_event_id)) {
            $this->query->where("appointment_event_id = :appointment_event_id");
            $this->query->places("appointment_event_id={$appointment_event_id}");
        }
        if (!empty($this->appointment_usuario_id)) {
            $this->query->where("appointment_usuario_id = :appointment_usuario_id");
            $this->query->places("appointment_usuario_id={$this->appointment_usuario_id}");
        }
        $this->query->order(["appointment_start" => "ASC"]);
        if (!empty($limit)) {
            $this->query->limit($limit);
        }
        $read->readFull($this->query->getQuery(), $this->query->getPlaces());
        if ($read->getResult()) {
            $dados = $read->getResult();
            foreach ($read->getResult() as $result) {
                $dados[$i] = $result;
                $read->readFull("SELECT * FROM attendees WHERE attendees_appointment_id = :attendees_appointment_id", "attendees_appointment_id={$result['appointment_id']}");
                if ($read->getResult()) {
                    $dados[$i]['attendees'] = $read->getResult();
                }
                $i++;
            }
        }

        return $dados;
    }

    private function createAttendees($attendeesList, $appointmentId)
    {
        foreach ($attendeesList as $attendees) {
            $params = [
                'attendees_appointment_id' => $appointmentId,
                'attendees_name' => $attendees->getDisplayName(),
                'attendees_email' => $attendees->getEmail()
            ];

            $create = new ControllerPDO\Create;
            $create->create("attendees", $params);

            if (empty($create->getResult())) {
                $this->trigger = ['error', 'Oops!', 'Erro ao cadastrar participantes do evento no banco de dados.'];
            }
        }
    }
}