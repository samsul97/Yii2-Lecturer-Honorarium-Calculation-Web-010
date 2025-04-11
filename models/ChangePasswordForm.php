<?php
namespace app\models;
 
use Yii;
use yii\base\InvalidParamException;
use yii\base\Model;
use app\models\User;
 
/**
 * Change password form for current user only
 */
class ChangePasswordForm extends Model
{
    public $id;
    public $password;
    public $confirm_password;
    // public $verifyCode;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['password','confirm_password'], 'required'],
            [['password','confirm_password'], 'string', 'min' => 6],
            ['confirm_password', 'compare', 'compareAttribute' => 'password','message' => '{attribute} Tidak sama'],
            // ['verifyCode', 'captcha'],
        ];
    }
 
    /**
     * Changes password.
     *
     * @return boolean if password was changed.
     */
    public function changePassword()
    {
        $model = User::findOne($this->id);
        $model->password = Yii::$app->getSecurity()->generatePasswordHash($this->password);
        $model->save();
        return true;
    }
}