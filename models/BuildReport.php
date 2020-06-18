<?php


namespace app\models;

use yii\base\Exception;

class BuildReport
{
    public $dateTime;
    public $beginTime;
    public $endTime;

    public $update_prj;
    public $new_prj;

    public $new_task;
    public $update_task;
    public $new_lib_task;

    public $new_worklog;
    public $update_worklog;

    public $new_timesheet;
    public $update_timesheet;

    public $not_search_task_in_prj;
    public $mess;

    public function __construct()
    {
        $this->dateTime = new \DateTime();
        $this->beginTime = $this->dateTime->format('d-m-Y H:i:s');
        $this->update_prj = $this->new_prj = $this->new_task = $this->update_task
            = $this->new_worklog = $this->update_worklog = $this->new_timesheet
            = $this->update_timesheet = $this->not_search_task_in_prj = $this->new_lib_task = 0;
    }

    public function setEntity($entity){
        if (!property_exists(self::class, $entity)){
            new Exception('Не удалось вызвать сущность ' . $entity);
        }
        $this->$entity += 1;
    }

    public function build($end_time, $sep = "\n"){
        $this->endTime = $end_time;

        $time_diff = date_diff($this->dateTime, $end_time);
        $time_diff = $time_diff->format('%i минут %s секунд');

        return $this->mess = 'Запуск ' . $this->beginTime
            . $sep . '////////////////////////////////////'
            . $sep . 'Добавлено проектов ' . $this->new_prj
            . $sep . 'Обновлено проектов ' . $this->update_prj
            . $sep . '////////////////////////////////////'
            . $sep . 'Добавлено задач ' . $this->new_task
            . $sep . 'Обновлено задач ' . $this->update_task
            . $sep . 'Добавлено lib_task ' . $this->new_lib_task
            . $sep . '////////////////////////////////////'
            . $sep . 'Добавлено worklogs ' . $this->new_worklog
            . $sep . 'Обновлено worklogs ' . $this->update_worklog
            . $sep . '////////////////////////////////////'
            . $sep . 'Добавлено timesheets ' . $this->new_timesheet
            . $sep . 'Обновлено timesheets ' . $this->update_timesheet
            . $sep . '////////////////////////////////////'
            . $sep . 'Задач по поектам не нашлось ' . $this->not_search_task_in_prj
            . $sep . '////////////////////////////////////'
            . $sep . 'Завершение ' . $end_time->format('d-m-Y H:i:s')
            . $sep . 'Время работы ' . $time_diff;
    }

}