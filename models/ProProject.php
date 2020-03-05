<?php


namespace app\models;


use yii\db\ActiveRecord;

class ProProject extends ActiveRecord
{

    public static function tableName()
    {
        return 'pro_project';
    }

    public function rules() {

        return [
            [['id_project', 'nom', 'client_dictionary_value', 'year_full',
                'year_small', 'name', 'id_status_buh', 'id_user_create',
                'id_user_pmanager_1', 'id_user_pmanager_2', 'nom_agreement',
                'date_agreement', 'nom_appendix', 'date_appendix', 'status_dictionary_value',
                'description', 'link_forum', 'unixtime_create', 'unixtime_update', 'temp'
            ], 'safe']
        ];
    }

//    public function attributeLabels()
//    {
//        return [
//            'id_project' => \Yii::t('common', 'Date of creation'),
//            'name' => \Yii::t('common', 'Country'),
//            'key' => \Yii::t('common', 'Account'),
//        ];
//    }

}