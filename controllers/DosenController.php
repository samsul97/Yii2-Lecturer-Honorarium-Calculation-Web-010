<?php

namespace app\controllers;

use Yii;
use app\models\Dosen;
use app\models\User;
use app\models\DosenSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use PhpOffice\PhpSpreadsheet;
use app\models\NewPass;
/**
 * DosenController implements the CRUD actions for Dosen model.
 */
class DosenController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Dosen models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new DosenSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Dosen model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Dosen model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Dosen();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index', 'id' => $model->id]);
        }
        Yii::$app->session->setFlash('success', 'Data berhasil ditambahkan');
        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Dosen model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        if ($model->load(Yii::$app->request->post()) && $model->save()) {

            if (User::isAdmin()) {
                return $this->redirect(['index', 'id' => $model->id]);
            }
            elseif ($model->load(Yii::$app->request->post()) && $model->save()) {
                if (User::isDosen()) {
                    return $this->redirect(['update', 'id' => $model->id]);
                }
            }
        }
        else
        {
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                return $this->redirect(['index', 'id' => $model->id]);
            }
        } 
        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Dosen model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Dosen model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Dosen the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Dosen::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
    public function actionNew()
    {
        // $this->layout = 'main-login';
        $model = new NewPass();
        // Untuk mendapatkan token yang ada di tabel user yang dimana sudah di relasikan di anggota model
        $idDosen = $this->id;
        $user = User::findOne(['dosen' => $idDosen]);
        if ($user === null) {
            throw new NotFoundHttpException("Halaman tidak ditemukan", 404);
        }
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $user->password = Yii::$app->getSecurity()->generatePasswordHash($model->new_password);
            $user->save();
            Yii::$app->getSession()->setFlash('success', 'Data berhasil di ubah');
            return $this->redirect(['dosen/index']);
        }
        return $this->render('forgetpass', [
            'model' => $model,
        ]);
    }

    public function actionImport()
    {
        $modelImport = new \yii\base\DynamicModel([
            'fileImport' => 'File Import',
        ]);

        $modelImport->addRule(['fileImport'], 'required');
        $modelImport->addRule(['fileImport'], 'file', ['extensions' => 'xls,xlsx']);

        if (Yii::$app->request->post()) {
            $modelImport->fileImport = \yii\web\UploadedFile::getInstance($modelImport, 'fileImport');

            if ($modelImport->fileImport && $modelImport->validate()) {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                $spreadsheet = $reader->load($modelImport->fileImport->tempName);
                $spreadsheetData = $spreadsheet->getActiveSheet()->toArray();

                // echo "<pre>";print_r($spreadsheetData);exit;

                if (count($spreadsheetData) > 5) {
                    foreach ($spreadsheetData as $i => $data) {
                        // if first index (column name), continue
                        if ($i < 5) {
                            continue;
                        }

                        // normalize format
                        $dataOld = $data;
                        foreach ($dataOld as $i => $_data) {
                            // hide no from excel
                            $j = $i-1;

                            $data[$j] = trim($_data);
                        }
                        // echo "<pre>";
                        // print_r($data);
                        // exit;

                        $dataBdt = DataBdt::findOne($data[0]) ?? new DataBdt();
                        $dataBdt->IDBDT = $data[0];
                        $dataBdt->RUTA6 = $data[1];
                        $dataBdt->no_peserta_bdt = $data[2];

                        
                        $prop = Prop::findOne(['prop_kode' => $data[3]]);
                        if (!$prop) {
                            $prop = new Prop();
                            // $prop->prop_id = $data[3];
                            $prop->prop_nama = $data[3];
                            $prop->prop_kode = $data[3];
                            $prop->prop_status = 1;
                            $prop->save(false);
                        }
                        $dataBdt->kd_prop = $prop->prop_id;

                        // var_dump($dataBdt->kd_prop);die;

                        
                        // $kota = Kota::findOne(['kota_kode' => $data[4]]);
                        // if (!$kota) {
                        //     $kota = new Kota();
                        //     $kota->kota_nama = $data[4];
                        //     $kota->kota_kode = $data[4];
                        //     // $kota->id_prop = $data[3];
                        //     $kota->kota_status = 1;
                        //     $kota->save(false);
                        // }
                        // $dataBdt->kd_kota = $kota->kota_id;
                        

                        // $kec = Kecamatan::findOne(['kec_kode' => $data[5]]);
                        // if (!$kec) {
                        //     $kec = new Kecamatan();
                        //     $kec->kec_id = $data[5];
                        //     $kec->kec_nama = $data[5];
                        //     $kec->kec_kode = $data[5];
                        //     // $kec->id_kota = $data[4];
                        //     $kec->kec_status = 1;
                        //     $kec->save(false);
                        // }
                        // $dataBdt->kd_kec = $kec->kec_id;


                        $kelurahan = Kelurahan::findOne(['kel_kode' => $data[7], 'id_kec'=>$kec->kec_id]);
                        if (! $kelurahan) {
                            $kelurahan = new Kelurahan;
                            $kelurahan->kel_nama = $data[7];
                            $kelurahan->kel_kode = $data[7];
                            // $kelurahan->id_kec = $kec->kec_kode;
                            $kelurahan->kel_status = 1;
                            $kec->save(false);
                        }
                        $dataBdt->kd_kel = $kelurahan->kel_id;

                        
                        $dataBdt->alamat = $data[9];
                        $dataBdt->no_pkh = $data[10];
                        $dataBdt->no_peserta_kks = $data[11];

                        $dataBdt->no_pbi = $data[12];
                        // if ($data[11] !=  "NULL" || $data[11] !=  "0") {
                            // var_dump($data[11]);die;
                        $dataBdt->no_kip = $data[13];

                        // }
                        $dataBdt->nama_sls = $data[14];
                        $dataBdt->nama_krt = $data[15];
                        $dataBdt->jumlah_art = $data[16];
                        $dataBdt->jumlah_keluarga = $data[17];
                        $dataBdt->save(false);

                        // $bangunanRt = new BangunanRt();
                        // $bangunanRt->id_bdt = $dataBdt->IDBDT;
                        // $bangunanRt->bangunan_stat_bangunan = $data[18];
                        // $bangunanRt->bangunan_stat_lahan = $data[19];
                        // $bangunanRt->bangunan_luas_lantai = $data[20];
                        // $bangunanRt->bangunan_lantai = $data[21];
                        // $bangunanRt->bangunan_dinding = $data[22];
                        // $bangunanRt->bangunan_kondisi_dinding = $data[23];
                        // $bangunanRt->bangunan_atap = $data[24];
                        // $bangunanRt->bangunan_kondisi_atap = $data[25];
                        // $bangunanRt->bangunan_kamar = $data[26];
                        // $bangunanRt->save(false);


                        // //untuk tabel sumber RT
                        $sumberRt = new SumberRt();
                        $sumberRt->id_bdt = $dataBdt->IDBDT;
                        $sumberRt->sumber_minum = $data[27];
                        $sumberRt->sumber_no_meteran = $data[28];
                        $sumberRt->sumber_peroleh_minum = $data[29];
                        $sumberRt->sumber_penerangan = $data[30];
                        $sumberRt->sumber_daya_listrik = $data[31];
                        $sumberRt->sumber_no_pln = $data[32];
                        $sumberRt->sumber_bb_masak = $data[33];
                        $sumberRt->sumber_no_gas = $data[34];
                        $sumberRt->save(false);

                        // //untuk tabel fasilitas RT
                        // $fasilitasRt = new FasilitasRt();
                        // $fasilitasRt->id_bdt = $dataBdt->IDBDT;
                        // $fasilitasRt->fasilitas_fasbab = $data[35];
                        // $fasilitasRt->fasilitas_kloset = $data[36];
                        // $fasilitasRt->fasilitas_buang_tinja = $data[37];
                        // $fasilitasRt->save(false);

                        // //untuk tabel barang RT
                        // $barangRt = new BarangRt();
                        // $barangRt->id_bdt = $dataBdt->IDBDT;
                        // $barangRt->barang_ada_gas = $data[38];
                        // $barangRt->barang_kulkas = $data[39];
                        // $barangRt->barang_ac = $data[40];
                        // $barangRt->barang_pemanas = $data[41];
                        // $barangRt->barang_telp = $data[42];
                        // $barangRt->barang_tv = $data[43];
                        // $barangRt->barang_emas = $data[44];
                        // $barangRt->barang_komputer = $data[45];
                        // $barangRt->barang_sepeda = $data[46];
                        // $barangRt->barang_motor = $data[47];
                        // $barangRt->barang_mobil = $data[48];
                        // $barangRt->barang_perahu = $data[49];
                        // $barangRt->barang_motor_tempel = $data[50];
                        // $barangRt->barang_perahu_motor = $data[51];
                        // $barangRt->barang_kapal = $data[52];
                        // $barangRt->save(false);

                        // //untuk tabel aset RT
                        // $asetRt = new AsetRt();
                        // $asetRt->id_bdt = $dataBdt->IDBDT;
                        // $asetRt->aset_tak_bergerak = $data[53];
                        // $asetRt->aset_luas_atb = $data[54];
                        // $asetRt->aset_rumah_lain = $data[55];
                        // $asetRt->aset_sapi = $data[56];
                        // $asetRt->aset_kerbau = $data[57];
                        // $asetRt->aset_kuda = $data[58];
                        // $asetRt->aset_babi = $data[59];
                        // $asetRt->aset_kambing = $data[60];
                        // $asetRt->aset_stat_art_usaha = $data[61];
                        // $asetRt->save(false);

                        //untuk tabel jaminan RT
                        // $jaminanRt = new JaminanRt();
                        // $jaminanRt->id_bdt = $dataBdt->IDBDT;
                        // $jaminanRt->jaminan_stat_kks = $data[62];
                        // $jaminanRt->jaminan_stat_kip = $data[63];
                        // $jaminanRt->jaminan_stat_kis = $data[64];
                        // $jaminanRt->jaminan_stat_bpjs = $data[65];
                        // $jaminanRt->jaminan_stat_jamsostek = $data[66];
                        // $jaminanRt->jaminan_stat_asuransi = $data[67];
                        // $jaminanRt->jaminan_stat_pkh = $data[68];
                        // $jaminanRt->jaminan_stat_rastra = $data[69];
                        // $jaminanRt->jaminan_stat_kur = $data[70];
                        // $jaminanRt->sta_keberadaan_RT = $data[71];
                        // $jaminanRt->percentile = $data[72];
                        // $jaminanRt->save(false);

                        
                    }
                }

                Yii::$app->getSession()->setFlash('success', 'Success');
                return $this->redirect(['dosen/index']);
            } else {
                Yii::$app->getSession()->setFlash('error', 'Error');
            }
        }

        return $this->render('import', [
            'modelImport' => $modelImport,
        ]);
    }
}