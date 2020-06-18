<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "jira_worklogs".
 *
 * @property int $id
 * @property int|null $id_timesheet
 * @property int $issueId
 * @property string $emailAdress
 * @property int $timeSpentSeconds
 * @property string|null $started
 * @property string|null $created
 * @property string|null $updated
 *
 * @property JiraTasks $issue
 */
class JiraWorklogs extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'jira_worklogs';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id','id_timesheet', 'issueId', 'timeSpentSeconds'], 'integer'],
            [['id','issueId', 'emailAdress', 'timeSpentSeconds'], 'required'],
            [['started', 'created', 'updated'], 'safe'],
            [['emailAdress'], 'string', 'max' => 45],
            [['issueId'], 'exist', 'skipOnError' => true, 'targetClass' => JiraTasks::className(), 'targetAttribute' => ['issueId' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'id_timesheet' => 'Id Timesheet',
            'issueId' => 'Issue ID',
            'emailAdress' => 'Email Adress',
            'timeSpentSeconds' => 'Time Spent Seconds',
            'started' => 'Started',
            'created' => 'Created',
            'updated' => 'Updated',
        ];
    }

    public function updateWorkLog($id, $issue_id, $email_adress, $time_spent_seconds, $started, $created,$updated) {
        $this->id = $id;
        $this->issueId = $issue_id;
        $this->emailAdress = $email_adress;
        $this->timeSpentSeconds = $time_spent_seconds;
        $this->started = $started;
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
    public function getIssue()
    {
        return $this->hasOne(JiraTasks::className(), ['id' => 'issueId']);
    }
}
