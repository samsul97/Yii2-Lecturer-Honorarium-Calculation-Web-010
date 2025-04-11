<?php

namespace app\models;

use Yii;
use yii\helpers\StringHelper;

/**
 * This is the model class for table "m_jurusan".
 *
 * @property int $id
 * @property string $nama
 */
class MJurusan extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'm_jurusan';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nama'], 'required'],
            [['nama'], 'string', 'max' => 100],
            [['nama'], 'unique', 'targetClass' => '\app\models\MJurusan'],
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
        ];
    }
    public static function getList()
    {
        return \yii\helpers\ArrayHelper::map(self::find()->all(), 'id', 'nama');
    }
    public function findAllDosen()
    {
        return Dosen::find()
            ->andWhere(['id_jurusan' => $this->id])
            ->orderBy(['nama' => SORT_ASC])
            ->all();
    }

    public function findAllAkademik()
    {
        return Akademik::find()
            ->andWhere(['id_jurusan' => $this->id])
            ->orderBy(['nama' => SORT_ASC])
            ->all();
    }

    public function findAllKetua()
    {
        return Ketuajurusan::find()
            ->andWhere(['id_jurusan' => $this->id])
            ->orderBy(['nama' => SORT_ASC])
            ->all();
    }
    public function getManyDosen()
    {
        return $this->hasMany(Dosen::class, ['id_jurusan' => 'id']);
    }

    public static function getGrafikList()
    {
        $data = [];
        foreach (static::find()->all() as $jurusan) {
            $data[] = [StringHelper::truncate($jurusan->nama, 20), (int) $jurusan->getManyDosen()->count()];
        }
        return $data;
    }

    public function getManyAkademik()
    {
        return $this->hasMany(Akademik::class, ['id_jurusan' => 'id']);
    }

    public static function getGrafikList2()
    {
        $data = [];
        foreach (static::find()->all() as $jurusan) {
            $data[] = [StringHelper::truncate($jurusan->nama, 20), (int) $jurusan->getManyAkademik()->count()];
        }
        return $data;
    }

    public function getManyKajur()
    {
        return $this->hasMany(Ketuajurusan::class, ['id_jurusan' => 'id']);
    }

    public static function getGrafikList3()
    {
        $data = [];
        foreach (static::find()->all() as $jurusan) {
            $data[] = [StringHelper::truncate($jurusan->nama, 20), (int) $jurusan->getManyKajur()->count()];
        }
        return $data;
    }
}
