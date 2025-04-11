<?php

namespace app\controllers;

use app\models\BebanMinimal;
use app\models\Dosen;
use app\models\FilterSemester;
use app\models\Hononarium;
use app\models\Jadwalkuliah;
use app\models\Kehadiran;
use app\models\Matakuliah;
use app\models\MJabatan;
use app\models\MTugasTambahan;
use app\models\User;
use arogachev\excel\export\basic\Exporter;
use Yii;
use yii\data\ArrayDataProvider;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii2tech\spreadsheet\Spreadsheet;

/**
 * HononariumController implements the CRUD actions for Hononarium model.
 */
class HononariumController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::className(),
                'only' => ['logout', 'index'],
                'rules' => [
                    [
                        'actions' => ['view', 'create', 'index'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function () {
                            return User::isAdmin() || User::isWadir() || User::isAkademik() || User::isDosen() || User::isKetuajurusan() || User::isKeuangan();
                        }
                    ],
                    // true berarti bisa mengakses.
                    [
                        'actions' => ['view', 'create'],
                        'allow' => false,
                        'roles' => ['@'],
                        'matchCallback' => function () {
                            return User::isDosen() || User::isKeuangan() || User::isKetuajurusan();
                        },
                    ],
                    // false berarti tidak bisa mengakses
                    // [
                    //     'actions' => ['index', 'create', 'update'],
                    //     'allow' => true,
                    //     'roles' => ['@'],
                    //     'matchCallback' => function()
                    //     {
                    //         return User::isPetugas();
                    //     },
                    // ],
                ],
            ],

            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Hononarium models.
     * @return mixed
     */
    public function actionIndex()
    {
        $currentSemester = FilterSemester::find()->all();//where(['semester' => Yii::$app->request->post('semester') ?: 'Semester Ganjil' ])->all();                                    
        $dosens = Yii::$app->user->identity->id_dosen === 0 ?
        Dosen::find()->where(Yii::$app->user->identity->id_user_role === 4 ? ['id_jurusan'=>Yii::$app->user->identity->id_jurusan] : [])->all() :
        Dosen::find()->where(['id' => Yii::$app->user->identity->id_dosen])->all();
        // $dosens = Yii::$app->user->identity->id_dosen === 0 ?
        //             Dosen::find()->where(['id' => [2,10,9]])->orderBY(['email' => ASC])->all() : 
        //             Dosen::find()->where(['id' => Yii::$app->user->identity->id_dosen])->all();

        // $err = FilterSemester::find();
        // var_dump($err->createCommand()->getRawSql());
        // die;

        /* ----------------- Export Excell -------------------- */
        if (Yii::$app->request->post('action')) {

            $action = Yii::$app->request->post('action');
            $currentSemester = FilterSemester::find()->where([
                'tahun' => Yii::$app->request->post('year'),
                'semester' => Yii::$app->request->post('semester') //KIN DEFAULT,
            ])->all();


            $i = 1;
            $data = [];

            foreach ($currentSemester as $semester):

                foreach ($dosens as $key => $dosen):

                    $data['dosen'][$key]['no'] =  $i++;
                    $data['dosen'][$key]['semester'] =  $semester->semester;
                    if (count($dosens) > 1):
                        $data['dosen'][$key]['nama'] = $dosen->nama;
                    endif;
                    
                    $data['dosen'][$key]['jabatan'] =  $dosen->jabatan->nominal;
                    $data['dosen'][$key]['bebanMinimal'] =  $dosen->bebanMinimal->jumlah;

                    $jadwalkuliah = $dosen->getManyMatakuliah($semester->id);
                    $kehadiran = $dosen->getManyKehadiran($semester->id);
                    foreach ($jadwalkuliah as $key2 => $value) {

                        $matkul = Matakuliah::find()->where(['id' =>$value['id_mk']])->one();
                        $jamRencana = ($matkul->teori*1) + ($matkul->praktek*2) * 1;

                        $tatapMuka = $dosen->getTatapMuka2($semester->id, $value['id_kelas'], $value['id_mk']);

                        $jam_rencana = $jamRencana * 14;
                        $jam_pelaksana = $tatapMuka / 14 * $jam_rencana;

                        $data['dosen'][$key]['kelebihan_sks'] = $jam_pelaksana > $jam_rencana ? $jam_rencana : $jam_pelaksana;
                    }

                    $jadwalkuliah = $dosen->getManyMatakuliah($semester->id);
                    $tot_sks = 0;
                    $honor = 0;
                    $kelebihan_sks = 0;

                    foreach ($jadwalkuliah as $key2 => $value) 
                    {
                        $matkul = Matakuliah::find()->where(['id' =>$value['id_mk']])->one();
                        $jamRencana = ($matkul->teori*1) + ($matkul->praktek*2) * 1;
                        $tatapMuka = $dosen->getTatapMuka2($semester->id, $value['id_kelas'], $value['id_mk']);
                        $totalSKS = 0;
                        $totalSKS = $matkul->teori + $matkul->praktek * 1;
                        $jam_rencana = $jamRencana * 14; //jam rencana
                        $jam_pelaksana = $tatapMuka / 14 * $jam_rencana; // jam pelaksana
                        $jam_diakui = $jam_pelaksana > $jam_rencana ? $jam_rencana : $jam_pelaksana; // jam di akui
                        $sks_pelasana = $jam_diakui / $jam_rencana *$totalSKS; //sks pelaksanaan
                        $tot_sks = $tot_sks + $sks_pelasana; // total sks

                        //kelebihan sks
                        $beban_minimal = $dosen->bebanMinimal->jumlah; // beban hidup
                        $flag_kelebihanSKS = $tot_sks - $beban_minimal;
                        if ($flag_kelebihanSKS <= 4) {
                            $kelebihan_sks = $flag_kelebihanSKS;
                        } else {
                            $kelebihan_sks = 4;
                        }
                        $jmlKehadiran = 14;
                        if ($flag_kelebihanSKS <= 4) {
                            $kelebihan_sks = $flag_kelebihanSKS;
                        } else {
                            $kelebihan_sks = 4;
                        }

                        
                        if ($dosen->id_tugastambah > 1) {
                            $honor = $kelebihan_sks * $jmlKehadiran * $dosen->getTunjanganJabatan()->nominal;
                        }
                        if ($dosen->id_jabatan == 2 && $dosen->id_tugastambah > 1) {
                            $honor = $kelebihan_sks * $jmlKehadiran * $dosen->getTunjanganJabatan()->nominal;
                        }
                        if ($dosen->id_jabatan == 3 && $dosen->id_tugastambah > 1) {
                            $honor =  $kelebihan_sks * $jmlKehadiran * $dosen->getTunjanganJabatan()->nominal;
                        }

                        $data['dosen'][$key]['honor'] = $honor < 1 ?  'Tidak dapat Honor' : number_format($honor, 2, ',', '.');

                        $kelebihan_sks = $kelebihan_sks < 1 ?  0 : round($kelebihan_sks, 2);
                    }

                endforeach;
            endforeach;

            // var_dump($data['dosen']);
            // die;
            
            if ($action === 'Download') {
                $exporter = new Spreadsheet([
                    'dataProvider' => new ArrayDataProvider([
                        'allModels' => $data['dosen'],
                    ]),
                    /*'columns' => [
                        [
                            'attribute' => 'no',
                        ],
                        [
                            'attribute' => 'semester',
                        ],
                        [
                            'attribute' => 'nama',
                        ],
                        [
                            'attribute' => 'jabatan',
                        ],
                        [
                            'attribute' => 'bebanMinimal',
                        ],
                        [
                            'attribute' => 'kelebihan_sks',
                        ],
                        [
                            'attribute' => 'honor',
                        ],
                    ],*/
                ]);
                return $exporter->send('export.xls');
            }

            // $exportData = [];
            // $i = 1;
            // foreach ($currentSemester as $semester)
            // {
            //     foreach ($dosens as $dosen) {
            //         $data['No'] = $i++;
            //         $data['Semester'] = $semester->semester;
            //         $data['Nama'] = $dosen->nama;
            //         $data['Beban Minimal'] = $dosen->bebanMinimal->jumlah;
            //         $data['Jabatan'] = $dosen->jabatan->nominal;
            //         $data['Total SKS'] = $dosen->getTotalSKS($semester->id);
            //         $data['Kelebihan SKS'] = $dosen->getKelebihanSKS($semester->id);
            //         $data['Honor'] = $dosen->getHonor($semester->id);
            //         $exportData[] = $data;
            //     }
            // }
        }

        return $this->render('index', compact('currentSemester', 'dosens'));
    }

    /**
     * Displays a single Hononarium model.
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
     * Creates a new Hononarium model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Hononarium();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Data berhasil ditambahkan');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Hononarium model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Data berhasil di Edit');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Hononarium model.
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
     * Finds the Hononarium model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Hononarium the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Hononarium::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function getHonorDosen($idDosen, $filterSemesterId = 1)
    {
        $totalJmlSKS = 0;
        // jadwalKuliah mencari berdasarkan id_dosen
        $jadwalKuliah = Jadwalkuliah::find()->where(['id_dosen' => $idDosen])->all();
        // mencari matakuliah dengan jumlah sks yang di ampu dosen + sks yang di ambil dosen
        foreach ($jadwalKuliah as $data) {

            $Matakuliah = Matakuliah::find()->where(['id' => $data->id_mk])->one();
            $totalJmlSKS = $totalJmlSKS + $Matakuliah->sks;
        }
        // format bulan/ filter bulan
        // $date = date('Y-m');
        $date = date('Y-m-d');
        // format semester/ filter semester
        $filter = FilterSemester::findOne(2);
        // kehadiran mencari berdasarkan tanggal lalu dijumlahkan dengan count
        // $jmlKehadiran= Kehadiran::find()->where(['like','tgl',$date])->andWhere(['id_dosen' => $idDosen])->count();
        $jmlKehadiran = Kehadiran::find()->where(['between', 'tgl', $filter->dari_tgl, $filter->ke_tgl])->andWhere(['id_dosen' => $idDosen])->count();
        // dosen mencari dimana id dengan iddosen
        $dosen = Dosen::find()->where(['id' => $idDosen])->one();
        // mencari kelebihan sks dengan jumlah sks yg didapat - beben minimal pengajaran
        $bebanminimal = BebanMinimal::find()->where(['id' => $dosen->id_bebanminimal])->one();
        // simple nya
        // $kelebihanSKS = ($totalJmlSKS - $bebanminimal->jumlah) <=4 ? $totalJmlSKS - $bebanminimal->jumlah : 4;

        // rumit nya
        $kelebihanSKS = 0;
        $flag_kelebihanSKS = $totalJmlSKS - $bebanminimal->jumlah;

        if ($flag_kelebihanSKS <= 4) {
            $kelebihanSKS = $flag;
        } else {
            $kelebihanSKS = 4;
        }
        // Total sks 13
        // beban sumaridin 3
        // jumlah 8
        // tugas tambahan 3
        // jabatan akademik 

        // tugas tambah mencari dimana id dengan iddosen
        $tugastambah = TugasTambahan::find()->where(['id' => $dosen->id_tugastambah])->one();
        if ($dosen->id_tugastambah > 1) {

            $pembayaran = MJabatan::find()->where(['id' => $dosen->id_jabatan])->one();
            $honor = $kelebihanSKS * $jmlKehadiran * $pembayaran->nominal;
            return $honor;
            // pembayaran berdasarkan id_jabatan
            //$pembayaran= MJabatan::find()->where(['id' =>$dosen->id_jabatan])->one();
            //return $jmlKehadiran*$beban*$pembayaran->nominal;
            // var_dump($jmlKehadiran*$beban*$pembayaran->nominal);
        } else if ($dosen->id_jabatan == 2 && $dosen->id_tugastambah > 1) {
            // pembayaran berdasarkan id_jabatan
            $pembayaran = MJabatan::find()->where(['id' => $dosen->id_jabatan])->one();
            return $kelebihanSKS * $jmlKehadiran * $pembayaran->nominal;
            // var_dump($jmlKehadiran*$beban*$pembayaran->nominal);

        } else if ($dosen->id_jabatan == 3 && $dosen->id_tugastambah > 1) {
            // pembayaran berdasarkan id_jabatan
            $pembayaran = MJabatan::find()->where(['id' => $dosen->id_jabatan])->one();
            return $kelebihanSKS * $jmlKehadiran * $pembayaran->nominal;
            // var_dump($jmlKehadiran*$beban*$pembayaran->nominal);

        } else {
            return "Maaf Anda tidak mendapatkan Honor";
            $dosen = Dosen::findOne($idDosen);
            if ($dosen) {
                $honor = $dosen->getHonor($filterSemesterId, $filterSemesterId);
                if ($honor > 0) {
                    return $honor;
                }
                return 'Maaf Anda tidak mendapatkan Honor';
            }
            return null;
        }
    }

    // public function ExportExcelHonor()
    // {
    //     $spreadsheet = new Spreadsheet();
    //     $spreadsheet->setActiveSheetIndex(0);
    //     $sheet = $spreadsheet->getActiveSheet();
    //     $setBorderArray = array(
    //         'borders' => array(
    //             'allBorders' => array(
    //                 'borderStyle' => Border::BORDER_THIN,
    //                 'color' => array('argb' => '000000'),
    //             ),
    //         ),
    //     );

    //     $sheet->getColumnDimension('A')->setWidth(10);
    //     $sheet->getColumnDimension('B')->setWidth(20);
    //     $sheet->getColumnDimension('C')->setWidth(20);
    //     $sheet->getColumnDimension('D')->setWidth(20);
    //     $sheet->getColumnDimension('E')->setWidth(20);
    //     $sheet->getColumnDimension('F')->setWidth(20);
    //     $sheet->getColumnDimension('G')->setWidth(20);
    //     $sheet->getColumnDimension('H')->setWidth(20);
    //     $sheet->getColumnDimension('I')->setWidth(20);
    //     $sheet->getColumnDimension('J')->setWidth(20);
    //     $sheet->getColumnDimension('K')->setWidth(20);

    //     $sheet->setCellValue('A3', strtoupper('No'));
    //     $sheet->setCellValue('B3', strtoupper('Nama Dosen'));
    //     $sheet->setCellValue('C3', strtoupper('Mata Kuliah'));
    //     $sheet->setCellValue('D3', strtoupper('Penelitian/Pengabdian'));
    //     $sheet->setCellValue('E3', strtoupper('Beban Pengajaran Minimal'));
    //     $sheet->setCellValue('F3', strtoupper('Jabatan Akademik'));
    //     $sheet->setCellValue('G3', strtoupper('Rencana'));
    //     $sheet->setCellValue('H3', strtoupper('Pelaksanaan'));
    //     $sheet->setCellValue('I3', strtoupper('Kelebiihan SKS'));
    //     $sheet->setCellValue('J3', strtoupper('Honor'));
    //     // $sheet->setCellValue('K3', strtoupper('Hari'));

    //     $spreadsheet->getActiveSheet()->setCellValue('A1', 'Honor Dosen');

    //     $spreadsheet->getActiveSheet()->getStyle('A3:K3')->getFill()->setFillType(Fill::FILL_SOLID);
    //     $spreadsheet->getActiveSheet()->getStyle('A3:K3')->getFill()->getStartColor()->setARGB('d8d8d8');
    //     $spreadsheet->getActiveSheet()->mergeCells('A1:K1');
    //     $spreadsheet->getActiveSheet()->getDefaultRowDimension('3')->setRowHeight(25);
    //     $sheet->getStyle('A1:K3')->getFont()->setBold(true);
    //     $sheet->getStyle('A1:K3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    //     $row = 3;
    //     $i = 1;

    //     $searchModel = new Jadwalkuliah();

    //     foreach ($searchModel->find()->all() as $jadwalkuliah) {
    //         $row++;
    //         $sheet->setCellValue('A' . $row, $i);
    //         $sheet->setCellValue('B' . $row, $jadwalkuliah->matakuliah->nama);
    //         $sheet->setCellValue('C' . $row, $jadwalkuliah->dosen->nama);
    //         $sheet->setCellValue('D' . $row, $jadwalkuliah->jurusan->nama);
    //         $sheet->setCellValue('E' . $row, $jadwalkuliah->kategori->nama);
    //         $sheet->setCellValue('F' . $row, $jadwalkuliah->kelas->nama);
    //         $sheet->setCellValue('G' . $row, $jadwalkuliah->thn_akademik);
    //         $sheet->setCellValue('H' . $row, $jadwalkuliah->semester->nama);
    //         $sheet->setCellValue('I' . $row, $jadwalkuliah->jam);
    //         $sheet->setCellValue('J' . $row, $jadwalkuliah->ruang->nama);
    //         $sheet->setCellValue('K' . $row, $jadwalkuliah->hari);

    //         $i++;
    //     }

    //     $sheet->getStyle('A3:K' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    //     $sheet->getStyle('D3:K' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    //     $sheet->getStyle('E3:K' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);


    //     $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    //     $sheet->getStyle('A3:K' . $row)->applyFromArray($setBorderArray);

    //     $filename = time() . '_Daftar Jadwal Kuliah.xlsx';
    //     $path = 'document/' . $filename;
    //     $writer = new Xlsx($spreadsheet);
    //     $writer->save($path);

    //     return $this->redirect($path);
    //     $exportData = [];
    //     $i = 1;
    //     foreach ($currentSemester as $semester) {
    //         foreach ($dosens as $dosen) {
    //             $data['No'] = $i++;
    //             $data['Semester'] = $semester->semester;
    //             $data['Nama'] = $dosen->nama;
    //             $data['Beban Minimal'] = $dosen->bebanMinimal->jumlah;
    //             $data['Jabatan'] = $dosen->jabatan->nominal;
    //             $exportData[] = $data;
    //         }
    //     }
    //     if ($action === 'Download') {
    //         $exporter = new Spreadsheet([
    //             'dataProvider' => new ArrayDataProvider([
    //                 'allModels' => $exportData,
    //             ]),
    //         ]);
    //         return $exporter->send('export.xls');
    //     }
    // }



    // private function renderExcel($currentSemester, $dosens)
    // {
        // $spreadsheet = new Spreadsheet();
        // $spreadsheet->setActiveSheetIndex(0);
        // $sheet = $spreadsheet->getActiveSheet();
        // $setBorderArray = array(
        //     'borders' => array(
        //         'allBorders' => array(
        //             'borderStyle' => Border::BORDER_THIN,
        //             'color' => array('argb' => '000000'),
        //         ),
        //     ),
        // );

        // $sheet->getColumnDimension('A')->setWidth(10);
        // $sheet->getColumnDimension('B')->setWidth(20);
        // $sheet->getColumnDimension('C')->setWidth(20);
        // $sheet->getColumnDimension('D')->setWidth(20);
        // $sheet->getColumnDimension('E')->setWidth(20);
        // $sheet->getColumnDimension('F')->setWidth(20);
        // $sheet->getColumnDimension('G')->setWidth(20);
        // $sheet->getColumnDimension('H')->setWidth(20);
        // $sheet->getColumnDimension('I')->setWidth(20);

        // $sheet->setCellValue('A3', strtoupper('No'));
        // $sheet->setCellValue('B3', strtoupper('Semester'));
        // $sheet->setCellValue('C3', strtoupper('Dosen'));
        // $sheet->setCellValue('D3', strtoupper('Penelitian'));
        // $sheet->setCellValue('E3', strtoupper('Bebam Minimal'));
        // $sheet->setCellValue('F3', strtoupper('Jabatan akademik'));
        // $sheet->setCellValue('G3', strtoupper('Total SKS'));
        // $sheet->setCellValue('H3', strtoupper('Kelebihan SKS'));
        // $sheet->setCellValue('I3', strtoupper('Honor'));

        // $spreadsheet->getActiveSheet()->setCellValue('A1', 'Honor Dosen');

        // $spreadsheet->getActiveSheet()->getStyle('A3:I3')->getFill()->setFillType(Fill::FILL_SOLID);
        // $spreadsheet->getActiveSheet()->getStyle('A3:I3')->getFill()->getStartColor()->setARGB('d8d8d8');
        // $spreadsheet->getActiveSheet()->mergeCells('A1:I1');
        // $spreadsheet->getActiveSheet()->getDefaultRowDimension('3')->setRowHeight(25);
        // $sheet->getStyle('A1:I3')->getFont()->setBold(true);
        // $sheet->getStyle('A1:I3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // $row = 3;
        // $i = 1;

        // $exportData = [];
        // foreach ($currentSemester as $semester) {
        //     foreach ($dosens as $dosen) {
        //     $row++;
        //     $sheet->setCellValue('A' . $row, $i);
        //     $sheet->setCellValue('B' . $row, $semester->semester);
        //     $sheet->setCellValue('C' . $row, $dosen->nama);
        //     $sheet->setCellValue('D' . $row, $dosen->tugastambahan->nama);
        //     $sheet->setCellValue('E' . $row, $dosen->bebanMinimal->jumlah);
        //     $sheet->setCellValue('F' . $row, $dosen->jabatan->nominal);
        //     $sheet->setCellValue('G' . $row, $dosen->getTotalSKS());
        //     $sheet->setCellValue('H' . $row, $dosen->getKelebihanSKS());
        //     $sheet->setCellValue('I' . $row, $dosen->getHonor());
        //     $exportData[] = $data;
        //     $i++;
        // }
        // }

        // $sheet->getStyle('A3:I' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        // $sheet->getStyle('D3:I' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        // $sheet->getStyle('E3:I' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);


        // $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // $sheet->getStyle('A3:I' . $row)->applyFromArray($setBorderArray);

        // $exporter = new Spreadsheet([
        //     'dataProvider' => new ArrayDataProvider([
        //         'allModels' => $exportData,
        //     ]),
        // ]);
        // return $exporter->send('export.xls');


        // $filename = time() . '_Daftar_Honorarium_Dosen.xlsx';
        // $path = 'document/' . $filename;
        // $writer = new Xlsx($spreadsheet);
        // $writer->save($path);

        // return $this->redirect($path);

    //     $exportData = [];
    //     $i = 1;
    //     foreach ($currentSemester as $semester) {
    //         foreach ($dosens as $dosen) {
    //             $data['No'] = $i++;
    //             $data['Semester'] = $semester->semester;
    //             $data['Nama'] = $dosen->nama;
    //             $data['Beban Minimal'] = $dosen->bebanMinimal->jumlah;
    //             $data['Jabatan'] = $dosen->jabatan->nominal;
    //             // $data['Total SKS'] = $dosen->getTotalSKS($semester->id); //total sks
    //             // $data['Kelebihan SKS'] = $dosen->getKelebihanSKS($semester->id); //kelebihan sks
    //             // $data['Honor'] = $dosen->getHonor($semester->id); // honor
    //             $exportData[] = $data;
    //         }
    //     }

    // }
}
