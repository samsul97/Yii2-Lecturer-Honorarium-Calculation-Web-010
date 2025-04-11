<?php

namespace app\models;

use Yii;
use yii\helpers\StringHelper;

/**
 * This is the model class for table "matakuliah".
 *
 * @property int $id
 * @property string $nama
 * @property string $teori
 * @property string $kurikulum
 * @property string $praktek
 * @property string $wp
 */
class Matakuliah extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'matakuliah';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nama', 'teori', 'id_kurikulum', 'id_jurusan', 'praktek', 'jml_kelas'], 'required', 'message' => 'Data tidak boleh kosong'],
            [['id_kurikulum', 'teori', 'praktek', 'jml_kelas', 'id_jurusan', 'status'], 'integer'],
            ['teori', 'number', 'message' => 'Hanya angka'],
            ['praktek', 'number', 'message' => 'Hanya angka'],
            ['jml_kelas', 'number', 'message' => 'Hanya angka'],
            [['nama'], 'unique', 'targetClass' => '\app\models\Matakuliah'],
            // [['status'], 'integer'],
            // [['nama', 'teori', 'praktek'], 'string', 'max' => 100],
            // ['nama', 'match', 'pattern' => '/^[a-z]\w*$/i'],
            // ['kurikulum', 'number', 'message' => 'Hanya angka'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nama' => 'Nama Mata Kuliah',
            'teori' => 'Teori',
            'praktek' => 'Praktek',
            'id_kurikulum' => 'Kurikulum',
            'jml_kelas' => 'Jumlah Kelas',
            'id_jurusan' => 'Jurusan',
            'status' => 'Status',
        ];
    }
    public static function getCount()
    {
        return static::find()->count();
    }
    public static function getList()
    {
        return \yii\helpers\ArrayHelper::map(self::find()->all(), 'id', 'nama');
    }
    public function getKurikulum()
    {
        return $this->hasOne(MKurikulum::class, ['id' => 'id_kurikulum']);
    }
    public function getManyMatkul()
    {
        return $this->hasMany(Jadwalkuliah::class, ['id_mk' => 'id']);
    }
    public function getJurusan()
    {
        return $this->hasOne(MJurusan::class, ['id' => 'id_jurusan']);
    }
    public static function getGrafikList()
    {
        $data = [];
        foreach (static::find()->all() as $matkul) {
            $data[] = [StringHelper::truncate($matkul->nama, 20), (int) $matkul->getManyMatkul()->count()];
        }
        return $data;
    }
    // public function findAllDosen()
    // {
    //     return Matakuliah::find()
    //         ->andWhere(['id_dosen' => $this->id])
    //         ->orderBy(['nama' => SORT_ASC])
    //         ->all();
    // }
}
