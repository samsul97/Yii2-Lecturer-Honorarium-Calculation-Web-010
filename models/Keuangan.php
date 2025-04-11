<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "keuangan".
 *
 * @property int $id
 * @property string $nama
 * @property string $email
 * @property string $telp
 */
class Keuangan extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'bag_keuangan';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nama', 'email', 'telp'], 'required'],
            [['id_jurusan'], 'integer'],
            [['nama', 'email', 'telp'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nama' => 'Nama',
            'email' => 'Email',
            'telp' => 'Telp',
            'id_jurusan' => 'Jurusan',
        ];
    }
    public static function getCount()
    {
        return static::find()->count();
    }
    public function getJurusan()
    {
        return $this->hasOne(MJurusan::class, ['id' => 'id_jurusan']);
    }
    public function findAllAkun()
    {
        return User::find()
            ->andWhere(['id_keuangan' => $this->id])
            ->orderBy(['username' => SORT_ASC])
            ->all();
    }
}
