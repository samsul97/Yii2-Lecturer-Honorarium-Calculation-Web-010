<?php

namespace app\models;

use Yii;
use app\models\FilterSemester;
use app\models\Kehadiran;
use yii\helpers\StringHelper;
/**
 * This is the model class for table "filter_semester".
 *
 * @property int $id
 * @property string $tahun
 * @property string $semester
 * @property string $dari_tgl
 * @property string $ke_tgl
 */
class FilterSemester extends \yii\db\ActiveRecord
{
    // public $status;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'filter_semester';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['tahun', 'semester', 'dari_tgl', 'ke_tgl'], 'required'],
            [['dari_tgl', 'ke_tgl'], 'safe'],
            [['tahun'], 'string', 'max' => 4],
            [['tahun'], 'number'],
            [['semester'], 'string', 'max' => 100],
            // [['status'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'tahun' => 'Tahun',
            'semester' => 'Nama Semester',
            'dari_tgl' => 'Dari Tanggal',
            'ke_tgl' => 'Sampai Tanggal',
        ];
    }
    public function getTatapMuka($dari, $ke)
    {
        $idDosen = Yii::$app->user->identity->id_dosen;
        return Kehadiran::find()->where(['between','tgl',$dari, $ke])->andWhere(['id_dosen' => $idDosen])->count();
    }
    public function getJabatan()
    {
        $idDosen = Yii::$app->user->identity->id_dosen;
        $dosen = Dosen::find()->where(['id' => $idDosen])->one();
        return MJabatan::find()->andWhere(['id' => $dosen->id_jabatan])->one();
    }
    public function getBebanMinimal()
    {
        $idDosen = Yii::$app->user->identity->id_dosen;
        $dosen = Dosen::find()->where(['id' => $idDosen])->one();
        return BebanMinimal::find()->where(['id' =>$dosen->id_bebanminimal])->one();
    }
    public function getTotalSKS()
    {
        $idDosen = Yii::$app->user->identity->id_dosen;
        $dosen = Dosen::find()->where(['id' => $idDosen])->one();
        $totalJmlSKS = 0;
        $jadwalKuliah = Jadwalkuliah::find()->where(['id_dosen' =>$idDosen])->all();
        foreach ($jadwalKuliah as $data)
        {
            $Matakuliah = Matakuliah::find()->where(['id' =>$data->id_mk])->one();
            $totalJmlSKS += $Matakuliah->teori + $Matakuliah->praktek;
        }
            return $totalJmlSKS;
    }
    public function getKelebihanSKS($dari, $ke)
    {
        $idDosen = Yii::$app->user->identity->id_dosen;
        $dosen = Dosen::find()->where(['id' => $idDosen])->one();
        $totalJmlSKS=0;
        $jadwalKuliah =Jadwalkuliah::find()->where(['id_dosen' =>$idDosen])->all();
        foreach ($jadwalKuliah as $data){
            $Matakuliah = Matakuliah::find()->where(['id' =>$data->id_mk])->one();
            $totalJmlSKS += $Matakuliah->teori + $Matakuliah->praktek;
        }
        $jmlKehadiran= Kehadiran::find()->where(['between','tgl',$dari, $ke])->andWhere(['id_dosen' => $idDosen])->count();
        $dosen= Dosen::find()->where(['id' =>$idDosen])->one();
        $bebanminimal= BebanMinimal::find()->where(['id' =>$dosen->id_bebanminimal])->one();
        $kelebihanSKS = 0;
        $flag_kelebihanSKS = $totalJmlSKS - $bebanminimal->jumlah;
        if ($flag_kelebihanSKS <= 4) {
            $kelebihanSKS = $flag;
        } else {
            $kelebihanSKS = 4;
        }
        return $kelebihanSKS;
    }
    public function getHonor($dari, $ke)
    {
        $idDosen = Yii::$app->user->identity->id_dosen;
        $totalJmlSKS=0;
        $jadwalKuliah =Jadwalkuliah::find()->where(['id_dosen' =>$idDosen])->all();
        foreach ($jadwalKuliah as $data){

            $Matakuliah = Matakuliah::find()->where(['id' =>$data->id_mk])->one();
            $totalJmlSKS += $Matakuliah->teori + $Matakuliah->praktek;
        }
        $jmlKehadiran= Kehadiran::find()->where(['between','tgl',$dari, $ke])->andWhere(['id_dosen' => $idDosen])->count();
        $dosen= Dosen::find()->where(['id' =>$idDosen])->one();
        $bebanminimal= BebanMinimal::find()->where(['id' =>$dosen->id_bebanminimal])->one();
        $kelebihanSKS = 0;
        $flag_kelebihanSKS = $totalJmlSKS - $bebanminimal->jumlah;

        if ($flag_kelebihanSKS <= 4) {
            $kelebihanSKS = $flag;
        } else {
            $kelebihanSKS = 4;
        }
        $tugastambah = TugasTambahan::find()->where(['id' =>$dosen->id_tugastambah])->one();

        if ($dosen->id_tugastambah >  1) {

            $pembayaran = MJabatan::find()->where(['id' =>$dosen->id_jabatan])->one();
            $honor = $kelebihanSKS * $jmlKehadiran * $pembayaran->nominal;
            return $honor;
            
        }
        else if ($dosen->id_jabatan == 2 && $dosen->id_tugastambah >  1) {
            $pembayaran= MJabatan::find()->where(['id' =>$dosen->id_jabatan])->one();
            return $kelebihanSKS*$jmlKehadiran*$pembayaran->nominal;
        }
        else if ($dosen->id_jabatan == 3 && $dosen->id_tugastambah >  1) {

            $pembayaran= MJabatan::find()->where(['id' =>$dosen->id_jabatan])->one();
            return $kelebihanSKS*$jmlKehadiran*$pembayaran->nominal;
        }
        else
        {
            echo "Maaf Anda tidak mendapatkan Honor";
        }
    }
    public static function getListBulanGrafik()
    {
        $list = [];

        for ($i=1; $i <= 12 ; $i++) {
            $list[] = self::getBulanSingkat($i);
        }
        return $list;
    }
    public static function getCountGrafik()
    {
        $list = [];
        for ($i = 1; $i <= 12; $i++) {
            if (strlen($i) == 1) $i = '0' . $i;
            $count = static::findCountGrafik($i);
            $list [] = (int)@$count->count();
        }
        return $list;
    }
    public static function findCountGrafik($bulan,$dari, $ke)
    {
        $tahun = date('Y');
        $date = date('Y-m-d');
        $idDosen = Yii::$app->user->identity->id_dosen;
        return Kehadiran::find()->where(['between','tgl',$dari, $ke])->andWhere(['id_dosen' => $idDosen])->count();
    }
}