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

    public function sendError($text, $title_error = 'JIRA-PROJECTOR INTEGRATION ALERT')
    {
//        $text = json_encode($text);
//        $rand = 48;
        foreach (self::find()->all() as $key_email => $email) {
//            $dateTime =  "'" . date('Y-m-d H:i:s') . "'" ;
            Yii::$app->db->createCommand("INSERT INTO `c_send_error` "
                    . "(`email`, `text`, `title`, `created`,`send_id`) VALUES ('" . $email->email . "', ".$text.",'".$title_error."', $dateTime, $rand)")->execute();
            Yii::$app->mailer->compose()
                ->setTo($email->email)
                ->setSubject($title_error)
                ->setTextBody($text)
                ->setHtmlBody("<p>$text</p>")
                ->send();
//            sleep(2);
        }
    }
}
