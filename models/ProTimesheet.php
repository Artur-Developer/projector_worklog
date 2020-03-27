<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "pro_timesheet".
 *
 * @property int $id_timesheet
 * @property int $id_project
 * @property int|null $id_task
 * @property int|null $id_calculate
 * @property int $id_user
 * @property int $rate_for_time
 * @property int|null $spend_time
 * @property int|null $report_time
 * @property int|null $id_rhour
 * @property int|null $unixtime_create
 * @property int $unixtime_update
 */
class ProTimesheet extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pro_timesheet';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_project','id_user'], 'required'],
            [['id_project', 'id_task', 'id_calculate', 'id_user', 'rate_for_time', 'spend_time', 'report_time', 'id_rhour', 'unixtime_create', 'unixtime_update'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id_timesheet' => 'Id Timesheet',
            'id_project' => 'Id Project',
            'id_task' => 'Id Task',
            'id_calculate' => 'Id Calculate',
            'id_user' => 'Id User',
            'rate_for_time' => 'Rate For Time',
            'spend_time' => 'Spend Time',
            'report_time' => 'Report Time',
            'id_rhour' => 'Id Rhour',
            'unixtime_create' => 'Unixtime Create',
            'unixtime_update' => 'Unixtime Update',
        ];
    }

    public function saveProTimeSheet($id_project, $id_task, $id_user, $spend_time, $report_time) {
        if (empty($id_project) || intval($id_project) == 0) {
            return false;
        }
        $this->id_project = $id_project;
        $this->id_task = $id_task;
        $this->id_user = empty($id_user) ? 0 : $id_user;
        $this->spend_time = intval($spend_time) / 60;
        $this->report_time = $report_time;
        if ($this->isNewRecord){
            $this->unixtime_create = time();
        }
        $this->unixtime_update = time();
        if ($this->validate()){
            return $this->save();
        }
        return false;
    }
}
