<?php


namespace app\models;


use yii\db\ActiveRecord;

class JiraProjects extends ActiveRecord
{

    public static function tableName()
    {
        return 'jira_projects';
    }

    public function rules() {

        return [
            [['id','id_project'], 'integer'],
            [['name'], 'string' , 'max' => 255],
            [['key'], 'string', 'max' => 45],
            [['created_at', 'updated_at'], 'safe']
        ];
    }

    public function updateProject($id, $name, $key, $id_project = null) {
        $this->id = $id;
        $this->id_project = $id_project;
        $this->name = $name;
        $this->key = $key;
        $this->updated = date('Y-m-d H-i-s');
        if ($this->validate()){
            return $this->save();
        }
        return false;
    }

    public function newProject($id, $name, $key, $id_project = null) {
        $this->id = $id;
        $this->id_project = $id_project;
        $this->name = $name;
        $this->key = $key;
        if ($this->validate()){
            return $this->save();
        }
        return false;
    }

}