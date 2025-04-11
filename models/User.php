<?php

namespace app\models;

use Yii;
use yii\helpers\Html;
/**
 * This is the model class for table "user".
 *
 * @property int $id
 * @property string $username
 * @property string $password
 * @property int $id_anggota
 * @property int $id_Kepsek
 * @property int $id_user_role
 * @property int $status
 */

// Pada user model kegunaan dari masing-masing syntax adalah Autentikasi yang berarti untuk memverifikasi atau konfirmasi untuk mengamankan data pengguna. 

class User extends \yii\db\ActiveRecord  implements \yii\web\IdentityInterface
{
 public static function getList()
 {
  return \yii\helpers\ArrayHelper::map(self::find()->all(), 'id', 'nama');
}
         //untuk menampilkan di peminjaman buku sebagai nama
public function getDosen()
{
  return $this->hasOne(Dosen::className(), ['id' => 'id_dosen']);
}
         //untuk menampilkan di peminjaman buku sebagai nama
public function getAkademik()
{
  return $this->hasOne(Akademik::className(), ['id' => 'id_akademik']);
}
public function getKetuajurusan()
{
  return $this->hasOne(Ketuajurusan::className(), ['id' => 'id_ketuajurusan']);
}
public function getKeuangan()
{
  return $this->hasOne(Keuangan::className(), ['id' => 'id_keuangan']);
}
public function getWadir()
{
  return $this->hasOne(BagWadir::className(), ['id' => 'id_wadir']);
}
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
      return 'user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
      return [
        [['username', 'password'], 'required'],
        [['id_jurusan', 'id_dosen', 'id_akademik', 'id_ketuajurusan', 'id_keuangan', 'id_wadir', 'id_user_role', 'status'], 'integer'],
        [['username', 'token'], 'string', 'max' => 100],
        [['password'], 'string', 'max' => 100],
            // password varchar harus sama dengan database;
      ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
      return [
        'id' => 'ID',
        'username' => 'Username',
        'password' => 'Password',
        'id_jurusan' => 'Jurusan',
        'id_dosen' => 'Dosen',
        'id_akademik' => 'Akademik',
        'id_ketuajurusan' => 'Ketua Jurusan',
        'id_keuangan' => 'Keuangan',
        'id_wadir' => 'Wadir',
        'id_user_role' => 'Id User Role',
        'status' => 'Status',
        'token' => 'Token',
      ];
    }
    public static function findIdentity($id)
    {
      return self::findOne($id);
    }
    public static function findIdentityByAccessToken($token, $Type = null)
    {
      return static::findOne(['access_token' => $token]);
    }
    public function getId()
    {   
      return $this->id;
    }
    public function getAuthKey()
    {
      return null;
    }
    public function validateAuthKey($authKey)
    {
      return $this->authKey === $authKey;
    }
    public static function findByUsername($username)
    {
      return self::findOne(['username' =>$username]);    
    }
    public function validatePassword($password)
    {
      // return $this->password == $password;
        return Yii::$app->getSecurity()->validatePassword($password, $this->password);
    }
    public static function isAdmin()
    {
      if (Yii::$app->user->isGuest) {
        return false;
      }
      $model = User::findOne(['username' => Yii::$app->user->identity->username]);
      if ($model == null) {
        return false;
      } elseif ($model->id_user_role == 1) {
        return true;
      }
      return false;
    }

    public static function isDosen()
    {
      if (Yii::$app->user->isGuest) {
        return false;
      }
      $model = User::findOne(['username' => Yii::$app->user->identity->username]);
      if ($model == null) {
        return false;
      } elseif ($model->id_user_role == 2) {
        return true;
      }
      return false;
    }

    public static function isAkademik()
    {
      if (Yii::$app->user->isGuest) {
        return false;
      }
      $model = User::findOne(['username' => Yii::$app->user->identity->username]);
      if ($model == null) {
        return false;
      } elseif ($model->id_user_role == 3) {
        return true;
      }
      return false;
    }
    
    public static function isKetuajurusan()
    {
      if (Yii::$app->user->isGuest) {
        return false;
      }
      $model = User::findOne(['username' => Yii::$app->user->identity->username]);
      if ($model == null) {
        return false;
      } elseif ($model->id_user_role == 4) {
        return true;
      }
      return false;
    }

    public static function isKeuangan()
    {
      if (Yii::$app->user->isGuest) {
        return false;
      }
      $model = User::findOne(['username' => Yii::$app->user->identity->username]);
      if ($model == null) {
        return false;
      } elseif ($model->id_user_role == 5) {
        return true;
      }
      return false;
    }

    public static function isWadir()
    {
      if (Yii::$app->user->isGuest) {
        return false;
      }
      $model = User::findOne(['username' => Yii::$app->user->identity->username]);
      if ($model == null) {
        return false;
      } elseif ($model->id_user_role == 6) {
        return true;
      }
      return false;
    }

    public static function getFotoAdmin($htmlOptions=[])
    {
      return Html::img('@web/user/admin.jpg', $htmlOptions);
    }

    public static function getFotoDosen($htmlOptions=[])
    {
     $query = Dosen::find()
     ->andWhere(['id' => Yii::$app->user->identity->id_dosen])
     ->one();

     if ($query->foto != null) {
       return Html::img('@web/user/' . $query->foto, $htmlOptions);
     } else {
       return Html::img('@web/user/no-images.png', $htmlOptions);
     }
   }

   public static function getFotoAkademik($htmlOptions=[])
   {
     $query = Akademik::find()
     ->andWhere(['id' => Yii::$app->user->identity->id_akademik])
     ->one();

     if ($query->foto != null) {
       return Html::img('@web/user/' . $query->foto, $htmlOptions);
     } else {
       return Html::img('@web/user/no-images.png', $htmlOptions);
     }
   }

   public static function getFotoKetuajurusan($htmlOptions=[])
   {
     $query = Ketuajurusan::find()
     ->andWhere(['id' => Yii::$app->user->identity->id_ketuajurusan])
     ->one();

     if ($query->foto != null) {
       return Html::img('@web/user/' . $query->foto, $htmlOptions);
     } else {
       return Html::img('@web/user/no-images.png', $htmlOptions);
     }
   }

   public static function getFotoKeuangan($htmlOptions=[])
   {
     $query = Keuangan::find()
     ->andWhere(['id' => Yii::$app->user->identity->id_keuangan])
     ->one();

     if ($query->foto != null) {
       return Html::img('@web/user/' . $query->foto, $htmlOptions);
     } else {
       return Html::img('@web/user/no-images.png', $htmlOptions);
     }
   }

   public static function getFotoWadir($htmlOptions=[])
   {
     $query = BagWadir::find()
     ->andWhere(['id' => Yii::$app->user->identity->id_wadir])
     ->one();

     if ($query->foto != null) {
       return Html::img('@web/user/' . $query->foto, $htmlOptions);
     } else {
       return Html::img('@web/user/no-images.png', $htmlOptions);
     }
   }
 }