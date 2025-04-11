<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "jadwalkuliah".
 *
 * @property int $id
 * @property int $id_mk
 * @property int $id_dosen
 * @property string $thn_akademik
 * @property string $smstr_akademik
 * @property string $hari
 * @property string $jam
 * @property string $kelas
 * @property string $ruang
 */
class Jadwalkuliah extends \yii\db\ActiveRecord
{
    public $sampul_upload;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'jadwalkuliah';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_mk', 'id_dosen', 'thn_akademik', 'id_semester', 'hari', 'id_kelas', 'id_ruang', 'hari'], 'required'],
            [['id_mk', 'id_dosen', 'id_kategori', 'id_kelas', 'id_semester', 'id_ruang'], 'integer'],
            // [['thn_akademik'], 'safe'],
            ['thn_akademik', 'number', 'message' => 'Hanya Angka'],
            [['hari', 'jam_awal', 'jam_akhir'], 'string', 'max' => 100],
            [['sampul_upload'], 'file', 'extensions'=>'jpg, gif, png', 'maxSize'=>5218288, 'tooBig' => 'batas limit upload gambar 5mb'
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'id_mk' => 'Mata Kuliah',
            'id_dosen' => 'Dosen',
            // 'id_jurusan' => 'Jurusan',
            'id_kategori' => 'Kategori',
            'id_kelas' => 'Kelas',
            'thn_akademik' => 'Tahun',
            'id_semester' => 'Semester',
            // 'id_hari' => 'Hari',
            'jam_awal' => 'Jam Awal',
            'jam_akhir' => 'Jam Akhir',
            'id_kelas' => 'Kelas',
            'id_ruang' => 'Ruang',
            'hari' => 'Hari',
            'sampul_upload' => 'Sampul',
        ];
    }
    public static function getCount()
    {
        return static::find()->count();
    }
    public function getMatakuliah()
    {
        return $this->hasOne(Matakuliah::class, ['id' => 'id_mk']);
    }
    public function getDosen()
    {
        return $this->hasOne(Dosen::class, ['id' => 'id_dosen']);
    }
    public function getSemester()
    {
        return $this->hasOne(MSemester::class, ['id' => 'id_semester']);
    }
    public function getKelas()
    {
        return $this->hasOne(MKelas::class, ['id' => 'id_kelas']);
    }
    public function getRuang()
    {
        return $this->hasOne(MRuang::class, ['id' => 'id_ruang']);
    }
    // public function getJurusan()
    // {
    //     return $this->hasOne(MJurusan::class, ['id' => 'id_jurusan']);
    // }
    public function getKategori()
    {
        return $this->hasOne(MKategori::class, ['id' => 'id_kategori']);
    }
    public function getList()
    {
        return \yii\helpers\ArrayHelper::map(self::find()->all(), 'id', 'kelas.nama');
    }
    // public function getJumlahSKS()
    // {
    //     return Jadwalkuliah::find()
    //     ->andwhere(['id' => $this->id_mk])
    //     ->one();
    // }
    // public function getList2()
    // {
    //     return \yii\helpers\ArrayHelper::map(self::find()->all(), 'id', 'id_ruang');
    // }
    // public static function getList()
    // {
    //     return $this->getName(Jadwalkuliah::class, ['id_kelas' => 'nama']);
    // }
    // public function getHari()
    // {
    //     return $this->hasOne(MHari::class, ['id' => 'id_hari']);
    // }
    // public function getJadual()
    // {
    //     return $this->hasOne(Mruang::class, ['id' => 'id_jadwal']);
    // }
}
