<?php

namespace app\models;

use Yii;
use yii\base\Model;

class NewPass extends Model
{
    public $new_password;
    public $confirmation_password;

    public function rules()
    {
        return [
            [['new_password'], 'required'],
            ['confirmation_password', 'compare', 'compareAttribute' => 'new_password','message' => '{attribute} Password tidak sama'],
            // ['verifyCode', 'captcha'],
        ];
    }
}