<?php

namespace app\models;

use Yii;
use \yii\db\ActiveRecord;

class People extends ActiveRecord
{
    use \app\models\ModelExtentions;

    public static function tableName() {
        return 'people';
    }

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // name, email, subject and body are required
            [['ppl_itn', 'ppl_name', 'ppl_surname', 'ppl_middle_name', 'ppl_birth_date', 'ppl_nationality', 'ppl_place_address', 'ppl_residence_address'], 'required'],
            [['ppl_itn'], 'string', 'min' => 10, 'max' => 12],
            [['ppl_name', 'ppl_surname', 'ppl_middle_name', 'ppl_nationality', 'ppl_place_address', 'ppl_residence_address'], 'string'],
            [['ppl_birth_date'], 'date', 'format'=>'dd.mm.yyyy']
        ];
    }

    static function getFields($key = null) {
        $fields = [
            'ИИН' => 'ppl_itn',
            'Имя' => 'ppl_name',
            'Фамилия' => 'ppl_surname',
            'Отчество' => 'ppl_middle_name',
            'Дата рождения' => 'ppl_birth_date',
            'Национальность' => 'ppl_nationality',
            'Адрес прописки' => 'ppl_place_address',
            'Адрес проживания' => 'ppl_residence_address',
        ];

        return $key && isset($fields[$key]) ? $fields[$key] : $fields;
    }

    public function beforeSave($insert) {
        if (!is_numeric($this->ppl_birth_date)) {
            $this->ppl_birth_date = strtotime($this->ppl_birth_date);
        }

        return parent::beforeSave($insert);
    }

    public function validateItn($itn) {
        if (!is_numeric($itn) || strlen($itn) < 10 || strlen($itn) > 12) {
            $this->addError("Некорректный ИИН");
            return false;
        }

        return true;
    }
}