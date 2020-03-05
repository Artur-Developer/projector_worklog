<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "lib_tasks".
 *
 * @property int $id_task
 * @property int|null $id_work
 * @property string|null $name
 * @property int $id_prj
 */
class LibTasks extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'lib_tasks';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_work', 'id_prj'], 'integer'],
            [['name'], 'string'],
            [['id_prj'], 'required'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id_task' => 'Id Task',
            'id_work' => 'Id Work',
            'name' => 'Name',
            'id_prj' => 'Id Prj',
        ];
    }
}
