<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "jira_alert".
 *
 * @property int $id
 * @property string $email
 */
class JiraAlert extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'jira_alert';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['email'], 'required'],
            [['email'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'email' => 'Email',
        ];
    }

    public static function sendMail($text, $title_error = 'JIRA-PROJECTOR INTEGRATION ALERT')
    {
        foreach (self::find()->all() as $key_email => $email) {
            Yii::$app->mailer->compose()
                ->setTo($email->email)
                ->setSubject($title_error)
                ->setTextBody($text)
                ->setHtmlBody("<p>$text</p>")
                ->send();
        }
    }
}
