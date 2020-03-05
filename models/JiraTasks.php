<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "jira_tasks".
 *
 * @property int $id
 * @property int|null $id_task
 * @property int $project_id
 * @property string $key
 * @property string $project_key
 * @property string|null $summary
 * @property int|null $timespent
 * @property string|null $created
 * @property string|null $updated
 * @property string $created_at
 * @property string|null $updated_at
 *
 * @property JiraProjects $project
 * @property JiraWorklogs[] $jiraWorklogs
 */
class JiraTasks extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'jira_tasks';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'project_id', 'key', 'project_key'], 'required'],
            [['id', 'id_task', 'project_id', 'timespent'], 'integer'],
            [['created', 'updated', 'created_at', 'updated_at'], 'safe'],
            [['key', 'project_key'], 'string', 'max' => 45],
            [['summary'], 'string', 'max' => 255],
            [['id', 'project_id'], 'unique', 'targetAttribute' => ['id', 'project_id']],
            [['project_id'], 'exist', 'skipOnError' => true, 'targetClass' => JiraProjects::className(), 'targetAttribute' => ['project_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'id_task' => 'Id Task',
            'project_id' => 'Project ID',
            'key' => 'Key',
            'project_key' => 'Project Key',
            'summary' => 'Summary',
            'timespent' => 'Timespent',
            'created' => 'Created',
            'updated' => 'Updated',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
    public function updateTask($id, $task_key, $project_id, $project_key, $summary, $timespent, $created, $updated) {
        $this->id = $id;
        $this->key = $task_key;
        $this->project_id = $project_id;
        $this->project_key = $project_key;
        $this->summary = $summary;
        $this->timespent = $timespent;
        $this->created = $created;
        $this->updated = $updated;
        if ($this->validate()){
            return $this->save();
        }
        return false;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProject()
    {
        return $this->hasOne(JiraProjects::className(), ['id' => 'project_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getJiraWorklogs()
    {
        return $this->hasMany(JiraWorklogs::className(), ['issueId' => 'id']);
    }
}
