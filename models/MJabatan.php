<?php

namespace app\models;

use Yii;
use yii\helpers\StringHelper;
/**
 * This is the model class for table "m_jabatan".
 *
 * @property int $id
 * @property string $nama
 */
class MJabatan extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'm_jabatan';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nama', 'nominal'], 'required'],
            // [['nominal'], 'required'],
            [['nominal'], 'number'],
            [['nama'], 'string', 'max' => 100],
            [['nama'], 'unique', 'targetClass' => '\app\models\MJabatan'],
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
            'nominal' => 'Nominal',
        ];
    }
    public static function getList()
    {
        return \yii\helpers\ArrayHelper::map(self::find()->all(), 'id', 'nama');
    }
    public function findAllDosen()
    {
        return Dosen::find()
            ->andWhere(['id_jabatan' => $this->id])
            ->orderBy(['nama' => SORT_ASC])
            ->all();
    }
    public function getManyDosen()
    {
        return $this->hasMany(Dosen::class, ['id_jabatan' => 'id']);
    }

    public static function getGrafikList()
    {
        $data = [];
        foreach (static::find()->all() as $jabatan) {
            $data[] = [StringHelper::truncate($jabatan->nama, 20), (int) $jabatan->getManyDosen()->count()];
        }
        return $data;
    }
    public static function findAllJabatan()
    {
        return static::find()->all();
    }
    public function getManyJabatan()
    {
        return $this->hasMany(Jabatan::class, ['id_jabatan' => 'id']);
    }
    public function getManyJabatanCount()
    {
        return count($this->manyJabatan);
    }
    public function getDosenJabatan()
    {
        return $this->hasOne(Dosen::class, ['id' => 'id_jabatan']);
    }
}
