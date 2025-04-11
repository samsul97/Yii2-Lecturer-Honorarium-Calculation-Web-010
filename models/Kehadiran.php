<?php

namespace app\models;

use Yii;
use yii\helpers\StringHelper;


/**
 * This is the model class for table "kehadiran".
 *
 * @property int $id
 * @property string $tgl
 * @property string $keterangan
 * @property int $id_jadwal
 * @property int $id_dosen
 * @property int $id_mk
 */
class Kehadiran extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'kehadiran';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['tgl', 'id_kelas', 'id_dosen', 'id_mk', 'status'], 'required'],
            [['tgl'], 'safe'],
            [['keterangan'], 'string', 'max' => 100],
            [['status'], 'string'],
            [['id_kelas', 'id_dosen', 'id_mk'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'tgl' => 'Tanggal Hadir',
            'keterangan' => 'Keterangan',
            'id_kelas' => 'Kelas',
            'id_dosen' => 'Dosen',
            'id_mk' => 'Mata Kuliah',
            'status' => 'Status',
        ];
    }
    public static function getCount()
    {
        return static::find()->count();
    }
    public static function getList()
    {
        return \yii\helpers\ArrayHelper::map(self::find()->all(), 'id', 'tgl');
    }
    public function getDosen()
    {
        return $this->hasOne(Dosen::class, ['id' => 'id_dosen']);
    }
    public function getRuang()
    {
        return $this->hasOne(MRuang::class, ['id' => 'id_jadwal']);
    }
    public function getMatakuliah()
    {
        return $this->hasOne(Matakuliah::class, ['id' => 'id_mk']);
    }
    public function getKelas()
    {
        return $this->hasOne(MKelas::class, ['id' => 'id_kelas']);
    }
    // public function getManyKehadiran()
    // {
    //     return $this->hasMany(Matakuliah::class, ['id' => 'id_mk']);
    // }
    // public static function getGrafikList()
    // {
    //     $data = [];
    //     foreach (static::find()->all() as $matkul) {
    //         $data[] = [StringHelper::truncate($matkul->dosen->nama, 20), (int) $matkul->getManyKehadiran()->count()];
    //     }
    //     return $data;
    // }

    // public function getJadwalkuliah()
    // {
    //     return $this->hasOne(Jadwalkuliah::class, ['id_jadwal' => 'id_jadwal']);
    // }
}
