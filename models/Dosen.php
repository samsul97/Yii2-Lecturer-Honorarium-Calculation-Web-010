<?php

namespace app\models;

use Yii;
use yii\helpers\StringHelper;

/**
 * This is the model class for table "dosen".
 *
 * @property int $id
 * @property string $nama
 * @property int $status
 * @property string $golongan
 * @property int $jabatan
 */
class Dosen extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'bag_dosen';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nama', 'email', 'telp', 'foto', 'id_jabatan', 'id_jurusan', 'id_tugastambah', 'id_bebanminimal'], 'required'],
            [['id_jabatan', 'id_jurusan', 'id_bebanminimal', 'id_tugastambah'], 'integer'],
            [['nama', 'email', 'telp'], 'string', 'max' => 100],
            [['foto'], 'file', 'extensions'=>'jpg, gif, png', 'maxSize'=>5218288, 'tooBig' => 'batas limit upload gambar 5mb'],
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
            'telp' => 'Telepon',
            // 'status' => 'Status',
            // 'golongan' => 'Golongan',
            'id_jabatan' => 'Jabatan',
            'id_bebanminimal' => 'Beban Minimal',
            'id_tugastambah' => 'Tugas Tambahan',
            'id_jurusan' => 'Jurusan',
            'foto' => 'Foto',
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
    public function getJabatan()
    {
        return $this->hasOne(MJabatan::class, ['id' => 'id_jabatan']);
    }
    public function getJurusan()
    {
        return $this->hasOne(MJurusan::class, ['id' => 'id_jurusan']);
    }
    public function getTugasTambah()
    {
        return $this->hasOne(TugasTambahan::class, ['id' => 'id_tugastambah']);
    }
    public function getBebanMinimal()
    {
        return $this->hasOne(BebanMinimal::class, ['id' => 'id_bebanminimal']);
    }
    public function findAllAkun()
    {
        return User::find()
        ->andWhere(['id_dosen' => $this->id])
        ->orderBy(['username' => SORT_ASC])
        ->all();
    }
    public static function findAllJabatan()
    {
        return static::find()->all();
    }
    public function getManyJabatan()
    {
        return $this->hasMany(MJabatan::class, ['id' => 'id_jabatan']);
    }
    public function getManyJabatanCount()
    {
        return count($this->manyJabatan);
    }
    public function getDosenJabatan()
    {
        return $this->hasOne(Dosen::class, ['id' => 'id_jabatan']);
    }
    public function getManyKehadiran()
    {
        return $this->hasMany(Kehadiran::class, ['id_dosen' => 'id']);
    }
    public static function getGrafikList()
    {
        $data = [];
        foreach (static::find()->all() as $matkul) {
            $data[] = [StringHelper::truncate($matkul->nama, 20), (int) $matkul->getManyKehadiran()->count()];
        }
        return $data;
    }
    public function getManyMatkul()
    {
        return $this->hasMany(Jadwalkuliah::class, ['id_mk' => 'id']);
         // return $this->hasMany(Jadwalkuliah::class, ['id_dosen' => 'id']);
    }
    public static function getGrafikList1()
    {
        $data = [];
        foreach (static::find()->all() as $matkul) {
            $data[] = [StringHelper::truncate($matkul->nama, 20), (int) $matkul->getManyMatkul()->count()];
        }
        return $data;
    }
    public function getPenelitian()
    {
        return TugasTambahan::find()->where(['id' => $this->id_tugastambah])->one()->nama;
    }
    public function getTunjanganJabatan()
    {
        return MJabatan::find()->where(['id' => $this->id_jabatan])->one();
    }

    /* ------------------------------------------------- PERENCANAAN -------------------------------------------- */

    public function getManyMatakuliah($filterSemesterId)
    {
        $idDosen = $this->id;
        $jadwalKuliah = Jadwalkuliah::find()->where(['id_semester' => $filterSemesterId, 'id_dosen' => $idDosen])->groupBy(['id_kelas', 'id_mk'])->orderBy(['id_mk' => SORT_ASC])->all(); //ini di group
        //var_dump($jadwalKuliah->createCommand()->getRawSql());
        //die;
        /*foreach ($jadwalKuliah as $data)
        {
            $Matakuliah = Matakuliah::find()->where(['id' =>$data->id_mk]); // ->count(); // ???????
        }*/
        return $jadwalKuliah;
    }
    public function getJumlahKelas($filterSemesterId)
    {
        $idDosen = $this->id;
        $jadwalKuliah = Jadwalkuliah::find()->where(['id_semester' => $filterSemesterId, 'id_dosen' => $idDosen])->groupBy(['id_kelas', 'id_mk'])->orderBy(['id_mk' => SORT_ASC])->all();
        //var_dump($jadwalKuliah->createCommand()->getRawSql());
        //die;
       /* $arr = [];
        foreach ($jadwalKuliah as $data) {
            $matkul = Matakuliah::find()->where(['id' =>$data['id_mk']])->one();
            if ($data->id_mk == $matkul->id)
                $totalJumlahKelas++;
        }
        return 1;*/
        return $jadwalKuliah;
    }
    public function getTotalSKSRencana()
    {
        $idDosen = $this->id;
        $filter = FilterSemester::findOne($filterSemesterId);
        $jadwalKuliah = Jadwalkuliah::find()->where(['id_dosen' =>$idDosen])->groupBy(['id_kelas', 'id_mk'])->orderBy(['id_mk' => SORT_ASC])->all();
        return $jadwalKuliah;
    }
    public function getJamRencana()
    {
        $idDosen = $this->id;
        $filter = FilterSemester::findOne($filterSemesterId);
        $jadwalKuliah = Jadwalkuliah::find()->where(['id_dosen' =>$idDosen])->groupBy(['id_kelas', 'id_mk'])->orderBy(['id_mk' => SORT_ASC])->all();
        return $jadwalKuliah;
    }
    public function getTatapMukaRencana()
    {
        $idDosen = $this->id;
        $filter = FilterSemester::findOne($filterSemesterId);
        $jadwalKuliah = Jadwalkuliah::find()->where(['id_dosen' =>$idDosen])->groupBy(['id_kelas', 'id_mk'])->orderBy(['id_mk' => SORT_ASC])->all();
        return $jadwalKuliah;
    }
    public function getTotalJamRencana()
    {
        $idDosen = $this->id;
        $filter = FilterSemester::findOne($filterSemesterId);
        $jadwalKuliah = Jadwalkuliah::find()->where(['id_dosen' =>$idDosen])->groupBy(['id_kelas', 'id_mk'])->orderBy(['id_mk' => SORT_ASC])->all();
        return $jadwalKuliah;
    }

    /* ------------------------------------------------- PELAKSANAAN !!! -------------------------------------------- */


    public function getTatapMuka($filterSemesterId)
    {
        $idDosen = $this->id;
        $filter = FilterSemester::findOne($filterSemesterId);
        $tatapMuka = Kehadiran::find()->where(['between', 'tgl', $filter->dari_tgl, $filter->ke_tgl])->andWhere(['id_dosen' => $idDosen, 'status' => 'Hadir'])->all();
        return $tatapMuka;
    }

    public function getTatapMuka2($filterSemesterId, $filterkelas, $filtermk)
    {
        $idDosen = $this->id;
        $filter = FilterSemester::findOne($filterSemesterId);
        $tatapMuka = Kehadiran::find()->where(['between', 'tgl', $filter->dari_tgl, $filter->ke_tgl])->andWhere(['id_dosen' => $idDosen, 'status' => 'Hadir', 'id_kelas' => $filterkelas, 'id_mk' => $filtermk ])->count();
        return $tatapMuka;
    }
    public function getJam()
    {
        $idDosen = $this->id;
        $filter = FilterSemester::findOne($filterSemesterId);
        $jam = Kehadiran::find()->where(['id_dosen' =>$idDosen])->all();
        return $jam;
    }
    public function getJamPelaksanaan($filterSemesterId)
    {
        $idDosen = $this->id;
        $filter = FilterSemester::findOne($filterSemesterId);
        $jamPelaksanaan = Kehadiran::find()->where(['id_dosen' =>$idDosen])->groupBy(['id_kelas', 'id_mk'])->all();
        return $jamPelaksanaan;
    }
    public function getSKSPelaksanaan()
    {
        $idDosen = $this->id;
        $filter = FilterSemester::findOne($filterSemesterId);
        $sksPelaksanaan = Kehadiran::find()->where(['id_dosen' =>$idDosen])->groupBy(['id_kelas', 'id_mk'])->all();
        return $sksPelaksanaan;
    }
    public function getTotalSKS($filterSemesterId)
    {
        $filter = FilterSemester::findOne($filterSemesterId);
        $idDosen = $this->id;
        $totalJmlSKS = 0;
        $jadwalKuliah = Kehadiran::find()->where(['between', 'tgl', $filter->dari_tgl, $filter->ke_tgl])->andWhere(['id_dosen' =>$idDosen])->all();
        foreach ($jadwalKuliah as $data)
        {
            $Matakuliah = Matakuliah::find()->where(['id' =>$data->id_mk])->one();
            $totalJmlSKS += $Matakuliah->teori + $Matakuliah->praktek;
        }
        return $totalJmlSKS;
    }
    public function getKelebihanSKS($filterSemesterId)
    {
        $idDosen = $this->id;
        $totalJmlSKS=0;
        $jadwalKuliah =Kehadiran::find()->where(['id_dosen' =>$idDosen])->all();
        foreach ($jadwalKuliah as $data){
            $Matakuliah = Matakuliah::find()->where(['id' =>$data->id_mk])->one();
            $totalJmlSKS += $Matakuliah->teori + $Matakuliah->praktek;
        }
        $jmlKehadiran = $this->getTatapMuka($filterSemesterId);
        $dosen= Dosen::find()->where(['id' =>$idDosen])->one();
        $kelebihanSKS = 0;
        $flag_kelebihanSKS = $totalJmlSKS - $this->bebanMinimal->jumlah;
        if ($flag_kelebihanSKS <= 4) {
            $kelebihanSKS = $flag_kelebihanSKS;
        } else {
            $kelebihanSKS = 4;
        }
        return $kelebihanSKS;
    }
    public function getHonor($filterSemesterId) //ini rumusnya
    {
        $idDosen = $this->id;
        $totalJmlSKS = 0;
        // jadwalKuliah mencari berdasarkan id_dosen
        $jadwalKuliah = Kehadiran::find()->where(['id_dosen' => $idDosen])->all();
        // mencari matakuliah dengan jumlah sks yang di ampu dosen + sks yang di ambil dosen
        foreach ($jadwalKuliah as $data) {
            $Matakuliah = Matakuliah::find()->where(['id' => $data->id_mk])->one();
            $totalJmlSKS += $Matakuliah->teori + $Matakuliah->praktek;
        }
        // kehadiran mencari berdasarkan tanggal lalu dijumlahkan dengan count
        // $jmlKehadiran= Kehadiran::find()->where(['like','tgl',$date])->andWhere(['id_dosen' => $idDosen])->count();
        $jmlKehadiran = $this->getTatapMuka($filterSemesterId);
        // dosen mencari dimana id dengan iddosen
        $dosen = Dosen::find()->where(['id' => $idDosen])->one();
        // simple nya
        // $kelebihanSKS = ($totalJmlSKS - $bebanminimal->jumlah) <=4 ? $totalJmlSKS - $bebanminimal->jumlah : 4;
        // rumit nya
        $kelebihanSKS = 0;
        $flag_kelebihanSKS = $totalJmlSKS - $this->bebanMinimal->jumlah;
        if ($flag_kelebihanSKS <= 4) {
            $kelebihanSKS = $flag_kelebihanSKS;
        } else {
            $kelebihanSKS = 4;
        }
        if ($dosen->id_tugastambah > 1) {
            //return $kelebihanSKS * $jmlKehadiran * $this->getTunjanganJabatan()->nominal;
        }
        if ($dosen->id_jabatan == 2 && $dosen->id_tugastambah > 1) {
            //return $kelebihanSKS * $jmlKehadiran * $this->getTunjanganJabatan()->nominal;
        }
        if ($dosen->id_jabatan == 3 && $dosen->id_tugastambah > 1) {
            //return $kelebihanSKS * $jmlKehadiran * $this->getTunjanganJabatan()->nominal;
        }
        return 0;
    }
}