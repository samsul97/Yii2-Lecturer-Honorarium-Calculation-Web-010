<?php

namespace app\controllers;

use Yii;
use app\models\Jadwalkuliah;
use app\models\Matakuliah;
use app\models\JadwalkuliahSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOfactory;
use Mpdf\Mpdf;
use PhpOffice\PhpWord\Shared\Converter;
use yii\web\ArrayHelper;
use PhpOffice\PhpSpreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use yii\base\Behavior;
use yii\helpers\Url;
use app\models\User;
use yii\filters\AccessControl;
use Da\QrCode\QrCode;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
// use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * JadwalkuliahController implements the CRUD actions for Jadwalkuliah model.
 */
class JadwalkuliahController extends Controller
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
                    'matchCallback' => function() {
                        return User::isAdmin() || User::isAkademik() || User::isDosen() || User::isKetuajurusan();
                    }
                ],
                    // true berarti bisa mengakses.
                [
                    'actions' => ['view', 'create'],
                    'allow' => false,
                    'roles' => ['@'],
                    'matchCallback' => function()
                    {
                        return User::isDosen() || User::isWadir() || User::isKeuangan(); //|| User::isKetuajurusan()
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
     * Lists all Jadwalkuliah models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new JadwalkuliahSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        if (Yii::$app->user->identity->id_user_role == 2) {  //dosen
            $jadwalkuliah = Yii::$app->user->identity->id_dosen;
            if ($jadwalkuliah) {
                $dataProvider->query->andWhere(['jadwalkuliah.id_dosen' => $jadwalkuliah]);
            }
        }

      if (Yii::$app->user->identity->id_user_role == 4) { //admin jurusan
        $jurusan = Yii::$app->user->identity->id_jurusan;
        $model_mk = Matakuliah::findAll(['id_jurusan' => $jurusan]);
        $id_mk = [];
        foreach ($model_mk as $key => $value) {
            $id_mk[] = $value->id;
        }
        $dataProvider->query->andWhere(['in', 'jadwalkuliah.id_mk', $id_mk]);
    }
    return $this->render('index', [
        'searchModel' => $searchModel,
        'dataProvider' => $dataProvider,
    ]);
}

    /**
     * Displays a single Jadwalkuliah model.
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
     * Creates a new Jadwalkuliah model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Jadwalkuliah();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {

            $sampul = UploadedFile::getInstance($model, 'sampul_upload');
            // var_dump($sampul);
            // die;
            $model->sampul = time(). '_' . $sampul->name;
            $model->save(false);

            $sampul->saveAs(Yii::$app->basePath. '/web/sampul/' . $model->sampul);
            
            $model->tgl = date('Y-m-d');
            Yii::$app->mail->compose('@app/mail/pemberitahuan',['model' => $model])
            ->setFrom('samsulaculhadi@gmail.com')
            ->setTo($model->dosen->email)
            ->setSubject('Pemberitahuan - Jadwal Kuliah')
            ->send();
            $model->save();

            Yii::$app->session->setFlash('success', 'Data berhasil ditambahkan');
            return $this->redirect(['index', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Jadwalkuliah model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        
        $foto_lama = $model->sampul;

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {

            $sampul = UploadedFile::getInstance($model, 'sampul_upload');
            if ($sampul !== null) {
                unlink(Yii::$app->basePath. '/web/sampul/' . $model->sampul);
                $sampul->saveAs(Yii::$app->basePath . '/web/sampul/' . $model->sampul);
            }
            else{
                $model->sampul = $foto_lama;
            }

            $model->save(false);

            $model->tgl = date('Y-m-d');
            Yii::$app->mail->compose('@app/mail/pemberitahuan',['model' => $model])
            ->setFrom('samsulaculhadi@gmail.com')
            ->setTo($model->dosen->email)
            ->setSubject('Pemberitahuan - Jadwal Kuliah')
            ->send();
            $model->save();
            
            Yii::$app->session->setFlash('success', 'Data berhasil di Edit');
            return $this->redirect(['index', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Jadwalkuliah model.
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
     * Finds the Jadwalkuliah model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Jadwalkuliah the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Jadwalkuliah::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionExportExcel()
    {
        if (User::isAdmin() || User::isAkademik() || User::isKetuajurusan() || User::isWadir() || User::isKeuangan())
        {
            $spreadsheet = new Spreadsheet();

            $spreadsheet->setActiveSheetIndex(0);

            $sheet = $spreadsheet->getActiveSheet();

            $setBorderArray = array(
                'borders' => array(
                    'allBorders' => array(
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => array('argb' => '000000'),
                    ),
                ),
            );

            $sheet->getColumnDimension('A')->setWidth(10);
            $sheet->getColumnDimension('B')->setWidth(20);
            $sheet->getColumnDimension('C')->setWidth(20);
            $sheet->getColumnDimension('D')->setWidth(20);
            $sheet->getColumnDimension('E')->setWidth(20);
            $sheet->getColumnDimension('F')->setWidth(20);
            $sheet->getColumnDimension('G')->setWidth(20);
            $sheet->getColumnDimension('H')->setWidth(20);
            $sheet->getColumnDimension('I')->setWidth(20);
            $sheet->getColumnDimension('J')->setWidth(20);
        // $sheet->getColumnDimension('K')->setWidth(20);
        // $sheet->getColumnDimension('L')->setWidth(20);

            $sheet->setCellValue('A3', strtoupper('No'));
            $sheet->setCellValue('B3', strtoupper('Mata Kuliah'));
            $sheet->setCellValue('C3', strtoupper('Dosen'));
            $sheet->setCellValue('D3', strtoupper('Kategori'));
            $sheet->setCellValue('E3', strtoupper('Kelas'));
            $sheet->setCellValue('F3', strtoupper('Tahun Akademik'));
            $sheet->setCellValue('G3', strtoupper('Semester'));
            $sheet->setCellValue('H3', strtoupper('Jam Awal'));
            $sheet->setCellValue('I3', strtoupper('Jam Akhir'));
            $sheet->setCellValue('J3', strtoupper('Ruang'));
        // $sheet->setCellValue('K3', strtoupper('Hari'));

            $spreadsheet->getActiveSheet()->setCellValue('A1', 'Semua Daftar Jadwal Kuliah');

            $spreadsheet->getActiveSheet()->getStyle('A3:J3')->getFill()->setFillType(Fill::FILL_SOLID);
            $spreadsheet->getActiveSheet()->getStyle('A3:J3')->getFill()->getStartColor()->setARGB('d8d8d8');
            $spreadsheet->getActiveSheet()->mergeCells('A1:J1');
            $spreadsheet->getActiveSheet()->getDefaultRowDimension('3')->setRowHeight(25);
            $sheet->getStyle('A1:J3')->getFont()->setBold(true);
            $sheet->getStyle('A1:J3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $row = 3;
            $i=1;

            $searchModel = new Jadwalkuliah();

            foreach($searchModel->find()->all() as $jadwalkuliah){
                $row++;
                $sheet->setCellValue('A' . $row, $i);
                $sheet->setCellValue('B' . $row, $jadwalkuliah->matakuliah->nama);
                $sheet->setCellValue('C' . $row, $jadwalkuliah->dosen->nama);
                $sheet->setCellValue('D' . $row, $jadwalkuliah->kategori->nama);
                $sheet->setCellValue('E' . $row, $jadwalkuliah->kelas->nama);
                $sheet->setCellValue('F' . $row, $jadwalkuliah->thn_akademik);
                $sheet->setCellValue('G' . $row, $jadwalkuliah->semester->nama);
                $sheet->setCellValue('H' . $row, $jadwalkuliah->jam_awal);
                $sheet->setCellValue('I' . $row, $jadwalkuliah->jam_akhir);
                $sheet->setCellValue('J' . $row, $jadwalkuliah->ruang->nama);
            // $sheet->setCellValue('L' . $row, $jadwalkuliah->hari);

                $i++;
            }

            $sheet->getStyle('A3:J' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D3:J' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('E3:J' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);


            $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->getStyle('A3:J' . $row)->applyFromArray($setBorderArray);

            $filename = time() . '_Daftar Jadwal Kuliah.xlsx';
            $path = 'document/' . $filename;
            $writer = new Xlsx($spreadsheet);
            $writer->save($path);

            return $this->redirect($path);
        } 
        elseif(User::isDosen()) {
            $spreadsheet = new Spreadsheet();

            $spreadsheet->setActiveSheetIndex(0);

            $sheet = $spreadsheet->getActiveSheet();

            $setBorderArray = array(
                'borders' => array(
                    'allBorders' => array(
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => array('argb' => '000000'),
                    ),
                ),
            );

            $sheet->getColumnDimension('A')->setWidth(10);
            $sheet->getColumnDimension('B')->setWidth(20);
            $sheet->getColumnDimension('C')->setWidth(20);
            $sheet->getColumnDimension('D')->setWidth(20);
            $sheet->getColumnDimension('E')->setWidth(20);
            $sheet->getColumnDimension('F')->setWidth(20);
            $sheet->getColumnDimension('G')->setWidth(20);
            $sheet->getColumnDimension('H')->setWidth(20);
            $sheet->getColumnDimension('I')->setWidth(20);
            $sheet->getColumnDimension('J')->setWidth(20);
        // $sheet->getColumnDimension('K')->setWidth(20);
        // $sheet->getColumnDimension('L')->setWidth(20);

            $sheet->setCellValue('A3', strtoupper('No'));
            $sheet->setCellValue('B3', strtoupper('Mata Kuliah'));
            $sheet->setCellValue('C3', strtoupper('Dosen'));
            $sheet->setCellValue('D3', strtoupper('Kategori'));
            $sheet->setCellValue('E3', strtoupper('Kelas'));
            $sheet->setCellValue('F3', strtoupper('Tahun Akademik'));
            $sheet->setCellValue('G3', strtoupper('Semester'));
            $sheet->setCellValue('H3', strtoupper('Jam Awal'));
            $sheet->setCellValue('I3', strtoupper('Jam Akhir'));
            $sheet->setCellValue('J3', strtoupper('Ruang'));
        // $sheet->setCellValue('K3', strtoupper('Hari'));

            $spreadsheet->getActiveSheet()->setCellValue('A1', 'Jadwal Kuliah Dosen');

            $spreadsheet->getActiveSheet()->getStyle('A3:J3')->getFill()->setFillType(Fill::FILL_SOLID);
            $spreadsheet->getActiveSheet()->getStyle('A3:J3')->getFill()->getStartColor()->setARGB('d8d8d8');
            $spreadsheet->getActiveSheet()->mergeCells('A1:J1');
            $spreadsheet->getActiveSheet()->getDefaultRowDimension('3')->setRowHeight(25);
            $sheet->getStyle('A1:J3')->getFont()->setBold(true);
            $sheet->getStyle('A1:J3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $row = 3;
            $i=1;

            $searchModel = new Jadwalkuliah();

            foreach($searchModel->find()->andWhere(['jadwalkuliah.id_dosen' => $jadwalkuliah])->all() as $jadwalkuliah){
                $row++;
                $sheet->setCellValue('A' . $row, $i);
                $sheet->setCellValue('B' . $row, $jadwalkuliah->matakuliah->nama);
                $sheet->setCellValue('C' . $row, $jadwalkuliah->dosen->nama);
                $sheet->setCellValue('D' . $row, $jadwalkuliah->kategori->nama);
                $sheet->setCellValue('E' . $row, $jadwalkuliah->kelas->nama);
                $sheet->setCellValue('F' . $row, $jadwalkuliah->thn_akademik);
                $sheet->setCellValue('G' . $row, $jadwalkuliah->semester->nama);
                $sheet->setCellValue('H' . $row, $jadwalkuliah->jam_awal);
                $sheet->setCellValue('I' . $row, $jadwalkuliah->jam_akhir);
                $sheet->setCellValue('J' . $row, $jadwalkuliah->ruang->nama);
            // $sheet->setCellValue('L' . $row, $jadwalkuliah->hari);

                $i++;
            }

            $sheet->getStyle('A3:J' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D3:J' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('E3:J' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);


            $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->getStyle('A3:J' . $row)->applyFromArray($setBorderArray);

            $filename = time() . '_Daftar Jadwal Kuliah.xlsx';
            $path = 'document/' . $filename;
            $writer = new Xlsx($spreadsheet);
            $writer->save($path);

            return $this->redirect($path);
        }
    }
    public function actionExportPdf() 
    {
      $this->layout='main';
      $model = Jadwalkuliah::find()->All();
      $mpdf=new mPDF();
      $mpdf->WriteHTML($this->renderPartial('exportpdf',['model'=>$model]));
      $mpdf->Output('_DataJadwalKuliah.pdf', 'D');
      exit;
  }
// public function actionExportWord()
//   {

//     $phpWord = new phpWord();
//     $section = $phpWord->addSection(
//         [
//             'marginTop' => Converter::cmTotwip(1.80),
//             'marginBottom' => Converter::cmTotwip(1.80),
//             'marginLeft' => Converter::cmTotwip(2.1),
//             'marginRight'=> Converter::cmTotwip(1.6),
//         ]
//     );

//     $fontStyle = [
//         'underline' => 'dash',
//         'bold' => true,
//         'italic' => true,
//     ];
//     $paragraphCenter = [
//         'alignment' => 'center',
//     ];
//     $headerStyle = [
//         'bold' => true,
//         'fgColor' => 'ffffff',
//     ];

//     $section->addText(
//         'Data Buku Perpustakaan SMAN 2 TANGSEL',
//         $headerStyle,
//         $fontStyle,
//         $paragraphCenter
//     );

//     $section->addTextBreak(1);

//     $judul = $section->addTextRun($paragraphCenter);

//     $judul->addText('Keterangan dari', $fontStyle);
//     $judul->addText('Tabel', ['italic' => true, $fontStyle]);
//     $judul->addText('Buku',  ['bold' => true]); 

//     $table =$section->addTable([
//         'alignment' => 'left',
//         'bgColor' => 6,
//         'borderSize' => 6,
//     ]);
//     $table->addRow(null);
//     $table->addCell(500)->addText('No', $headerStyle, $paragraphCenter);
//     $table->addCell(5000)->addText('Nama Buku', $headerStyle, $paragraphCenter);
//     $table->addCell(5000)->addText('Tahun Terbit', $headerStyle, $paragraphCenter);
//     $table->addCell(5000)->addText('Penulis', $headerStyle, $paragraphCenter);
//     $table->addCell(5000)->addText('Penerbit', $headerStyle, $paragraphCenter);
//     $table->addCell(5000)->addText('Kategori', $headerStyle, $paragraphCenter);
//     $table->addCell(5000)->addText('Sinopsis', $headerStyle, $paragraphCenter);

//     $semuaBuku = Buku::find()->all();
//     $nomor = 1;
//     foreach ($semuaBuku as $buku) {
//         $table->addRow(null);
//         $table->addCell(500)->addText($nomor++, null, $headerStyle, $paragraphCenter);
//         $table->addCell(5000)->addText($buku->nama, null);
//         $table->addCell(5000)->addText($buku->tahun_terbit, null, $paragraphCenter);
//         $table->addCell(3000)->addText(@$buku->penulis->nama, null, $paragraphCenter);
//         $table->addCell(3000)->addText(@$buku->penerbit->nama, null, $paragraphCenter);
//         $table->addCell(3000)->addText(@$buku->kategori->nama, null, $paragraphCenter);
//         $table->addCell(5000)->addText($buku->sinopsis, null);
//     }
//         // $filename = time() . 'Data-Buku.docx';
//         // // echo "$path";
//         // // die;
//         // $xmlWriter = IOFactory::createWriter($phpWord, 'Word2007');
//         // $xmlWriter->save($filename);
//         // // return $this->redirect($path);
//         // var_dump($path);
//         // print getcwd($path);
//         // return $this->redirect(['buku/index']);
//         // header('Content-Type: application/vnd.ms-excel');
//         // header('Content-Disposition: attachment;filename="download.docx"');
//         // header('Cache-Control: max-age=0');
//         // $writer->save('php://output');
//     $filename = time().'_Data_buku.docx';
//     header("Content-Description: File Transfer");
//     header('Content-Disposition: attachment; filename="' . $filename . '"');
//     header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
//     header('Content-Transfer-Encoding: binary');
//     header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
//     header('Expires: 0');
//     $xmlWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
//     $xmlWriter->save("php://output"); 
// }

//   public function actionkepo() {

//     $spreadsheet = new PhpSpreadsheet\Spreadsheet();
//     $worksheet = $spreadsheet->getActiveSheet();

//     //Menggunakan Model

//     $database = Jadwalkuliah::find()
//     ->select('id_mk, id_dosen', 'id_jurusan', 'id_kategori', 'id_kelas', 'thn_akademik', 'id_semester', 'hari', 'jam', 'id_ruang')
//     ->all();

//     $worksheet->setCellValue('A1', 'Mata Kuliah');
//     $worksheet->setCellValue('B1', 'Dosen');
//     $worksheet->setCellValue('B1', 'Jurusan');
//     $worksheet->setCellValue('B1', 'Kategori');
//     $worksheet->setCellValue('B1', 'Kelas');
//     $worksheet->setCellValue('B1', 'Tahun');
//     $worksheet->setCellValue('B1', 'Semester');
//     $worksheet->setCellValue('B1', 'Hari');
//     $worksheet->setCellValue('B1', 'Jam');
//     $worksheet->setCellValue('B1', 'Ruang');
//     $database = \yii\helpers\ArrayHelper::toArray($database);
//     $worksheet->fromArray($database, null, 'A2');

//     $writer = new Xlsx($spreadsheet);

//     header('Content-Type: application/vnd.ms-excel');
//     header('Content-Disposition: attachment;filename="JadwalKuliah.xlsx"');
//     header('Cache-Control: max-age=0');
//     $writer->save('php://output');

// }


// public function actionPdf()
// {

//  $mpdf  = new mPDF();
//  $mpdf->WriteHTML($this->renderPartial('pdfSurat'));
//  $mpdf->Output('Formulir-Permohonan-KK.pdf', 'D');
//  exit;

//  $content = $this->renderPartial('pdfSurat');

//  $marginLeft = 20;
//  $marginRight = 15;
//  $marginTop = 5;
//  $marginBottom = 5;
//  $marginHeader = 5;
//  $marginFooter = 5;

//  $cssInline = <<<CSS
//  table {
//     overflow: wrap;
//     font-size: 8pt;
// }

// tr, td {
//     padding: 0px;
// }

// div {
//     overflow: wrap;
// }

// .konten div {
//     box-shadow:
//     2px 0 0 0 #888,
//     0 2px 0 0 #888,
//     2px 2px 0 0 #888,   /* Just to fix the corner */
//     2px 0 0 0 #888 inset,
//     0 2px 0 0 #888 inset;
// }

// .clear {
//     clear: both;
// }

// .kode {
//     border: 1px solid black;
//     float: right;
//     font-size: 15px;
//     font-weight: bold;
//     padding: 0px 10px;
//     height: 35px;
//     line-height: 35px;
//     text-align: center;
//     width: 17%;
// }

// .header {
//     font-size: 8pt;
//     overflow: hidden;
// }

// .header .left {
//     width: 60%;
//     float: left;
// }

// .header .right {
//     width: 40%;
//     float: left;
// }

// .header table {
//     border-spacing: 0px;
//     border-collapse: collapse;
// }

// .header table .caption {
//     width: 45%;
// }

// .header table .point {
//     width: 2%;
// }

// .header table .kotak {
//     width: 5%;
// }

// .kode span {
//     display: inline-block;
//     vertical-align: middle;
//     line-height: normal;
// }

// .debug, .debug tr, .debug td {
//     border: 1px solid black;
// }

// .kotak, .form {
//     border-spacing: 0px;
//     border-collapse: collapse;
// }

// .kotak {
//     border: 1px solid black;
//     height: 15px;
//     width: 2.87%;
//     text-align: center;
// }

// .colspan {
//     padding-left: 2px;
//     text-align: left;
// }

// .kanan {
//     width: 1%;
// }

// .t-center {
//     text-align: center;
// }

// h4 {
//     font-weight: bold;
//     font-family: Arial;
//     font-size: 12pt;
// }

// .form .caption {
//     width: 26.8%;
// }

// .form .point, .section .point {
//     width: 1%;
// }

// .section {
//     border: 2px solid black;
//     padding: 0px;
//     margin: -1px !important;
// }

// .section h5 {
//     margin: 0px;
//     font-weight: bold;
//     text-align: left;
//     font-size: 11px;
// }

// .section table {
//     border-spacing: 0px;
//     border-collapse: collapse;
// }

// .section .nomor {
//     width: 3%;
// }

// .section .caption {
//     width: 24%;
// }

// .section .isi {
//     float: left;
//     overflow: hidden;
//     display: inline-block;
// }

// .border {
//     border: 1px solid black;
// }

// .ttd-left {
//     width: 30%;
//     text-align: center;
// }

// .ttd-middle {
//     width: 40%;
//     text-align: center;
// }

// .ttd-right {
//     width: 30%;
//     text-align: center;
// }

// CSS;

// $pdf = new Mpdf([
//     'mode' => Mpdf::MODE_UTF8,
//             // F4 paper format
//     'format' => [210, 330],
//             // portrait orientation
//     'orientation' => Mpdf::ORIENT_PORTRAIT,
//             // stream to browser inline
//     'destination' => Mpdf::DEST_BROWSER,
//             // your html content input

//     'marginLeft' => $marginLeft,
//     'marginRight' => $marginRight,
//     'marginTop' => $marginTop,
//     'marginBottom' => $marginBottom,
//     'marginHeader' => $marginHeader,
//     'marginFooter' => $marginFooter,

//     'content' => $content,

//             // format content from your own css file if needed or use the
//             // any css to be embedded if required
//     'cssInline' => $cssInline,
//              // set mPDF properties on the fly
//     'options' => ['title' => 'PDF Surat'],
//              // call mPDF methods on the fly
//     'methods' => []
// ]);

// return $pdf->render();
// }


// public function actionExportWord2()
// {
//     $phpWord = new PhpWord();
//     $section = $phpWord->addSection(
//         [
//             'marginTop' => Converter::cmTotwip(1.80),
//             'marginBottom' => Converter::cmTotwip(1.80),
//             'marginLeft' => Converter::cmTotwip(2.1),
//             'marginRight'=> Converter::cmTotwip(1.6),
//         ]
//     );

//     $fontStyle = [
//             // 'underline' => 'dash',
//             // 'bold' => true,
//         'italic' => true,
//         // 'size' => 12,
//     ];

//     // $lorem = 'sdfksdlf'.$var;

//     $fontJudul = [
//         'underline' => 'single',
//         'bold' => true,
//             // 'italic' => true,
//     ];
//     $subJudulBawah = [
//         'alignment' => 'left',
//     ];
//     $paragraphCenter = [
//         'alignment' => 'center',
//         'size' => '8',
//     ];

//     $sizeSmall = [
//         'size' => '9',
//     ];

//     $headerStyle = [
//         'bold' => true,
//         'fgColor' => 'ffffff',
//         'marginLeft' => '20',
//     ];  

//     $section->addText('Lampiran 7 KMA No.477 Tahun 2004',  $sizeSmall,  ['align' => 'right']);
//     $section->addText('Pasal 7 Ayat 2 huruf B',  $sizeSmall,  ['align' => 'right']);

//     // $section->addTextBreak(1);

//     $section->addText(
//         "KANTOR DESA KELURAHAN  : ",
//         $headerStyle,
//         $fontStyle,
//         $paragraphCenter
//     );
//     $section->addText(
//         "KECAMATAN \t \t \t : ",
//         $headerStyle,
//         $fontStyle,
//         $paragraphCenter
//     );
//     $section->addText(
//         "KABUPATEN/KOTA \t \t : ",
//         $headerStyle,
//         $fontStyle,
//         $paragraphCenter
//     );
//     // $section->addTextBreak(1);
//     $judul = $section->addTextRun($paragraphCenter);
//     $subjudul = $section->addTextRun($paragraphCenter);
//     $subjudul1 = $section->addTextRun($subJudulBawah);

//     $judul->addText('SURAT KETERANGAN DARI ORANG TUA', $fontJudul);
//     $subjudul->addText('Nomor : 474.2/', $subJudulBawah);
//     $subjudul->addText(" \t \t \t 429.512.01/2014", ['alignment' => 'right']);
//     $subjudul1->addText('Yang bertanda tangan di bawah ini menerangkan dengan sesungguhnya bahwa :');

//     $semuaBuku = Buku::find()->all();
//     $nomor = 1;
//     foreach ($semuaBuku as $buku) {
//     // $section->addTextBreak(1);
//     // $section->addText('I.', 1);
//         $section->addText("I.   1. Nama lengkap \t \t \t : ".$buku->nama, null, $headerStyle);
//     // $section->addText( $headerStyle);
//         $section->addText("     2. Tempat dan Tanggal Lahir \t : ".$buku->nama, $headerStyle);
//         $section->addText("     3. Warga Negara \t \t \t : ".$buku->nama, $headerStyle);
//         $section->addText("     4. Agama \t \t \t \t : ".$buku->nama, $headerStyle);
//         $section->addText("     5. Pekerjaan \t \t \t : ".$buku->nama, $headerStyle);
//         $section->addText("     6. Tempat Tinggal \t \t \t : ".$buku->nama, $headerStyle);

//     // $section->addTextBreak(1);
//     // $section->addText('II.', 1);
//         $section->addText("II.  1. Nama lengkap \t \t \t : ".$buku->nama, $headerStyle);
//         $section->addText("     2. Tempat dan Tanggal Lahir \t : ".$buku->nama, $headerStyle);
//         $section->addText("     3. Warga Negara \t \t \t : ".$buku->nama, $headerStyle);
//         $section->addText("     4. Agama \t \t \t \t : ".$buku->nama, $headerStyle);
//         $section->addText("     5. Pekerjaan \t \t \t : ".$buku->nama, $headerStyle);
//         $section->addText("     6. Tempat Tinggal \t \t \t : ".$buku->nama, $headerStyle);
//         $section->addText("Adalah benar ayah kandung dan ibu kandung dari seorang \t \t \t", $headerStyle);

//     // $section->addTextBreak(1);
//     // $section->addText('III.', 1);
//         $section->addText("III. 1. Nama lengkap \t \t \t : ".$buku->nama, $headerStyle);
//         $section->addText("     2. Tempat dan Tanggal Lahir \t : ".$buku->nama, $headerStyle);
//         $section->addText("     3. Warga Negara \t \t \t : ".$buku->nama, $headerStyle);
//         $section->addText("     4. Jenis Kelamin \t \t \t : ".$buku->nama, $headerStyle);
//         $section->addText("     5. Agama \t \t \t \t : ".$buku->nama, $headerStyle);
//         $section->addText("     6. Pekerjaan \t \t \t : ".$buku->nama, $headerStyle);
//         $section->addText("     7. Tempat Tinggal \t \t \t : ".$buku->nama, $headerStyle);
//         $section->addText("Demikian surat keterangan ini dibuat dengan mengingat sumpah jabatan dan dipergunakan seperlunya \t \t \t", $headerStyle);
//     }

//     $section->addText("Sumber Beras, 01 September 2014", $headerStyle, ['align' => 'right']);
//     $section->addText(" \t \t \t \t \t \t \t \t \t \t  Kepala Desa/Kelurahan");
//     $section->addTextBreak(3);
//     $section->addText(" \t \t \t \t \t \t \t \t \t \t \t Samsul Hadi", $headerStyle, $fontStyle, $paragraphCenter);
//     // $semuaBuku = Buku::find()->all();
//     // $nomor = 1;
//     // foreach ($semuaBuku as $buku) {
//     //         // $table->addRow(null);
//     //     $section->addText($nomor++, null, $headerStyle, $paragraphCenter);
//     //     $section->addText($buku->nama, null);
//     //     $section->addText($buku->tahun_terbit, null, $paragraphCenter);
//     //     $section->addText(@$buku->penulis->nama, null, $paragraphCenter);
//     //     $section->addText(@$buku->penerbit->nama, null, $paragraphCenter);
//     //     $section->addText(@$buku->kategori->nama, null, $paragraphCenter);
//     //     $section->addText($buku->sinopsis, null);
//     // }
//     // $filename = time() . 'Surat-ortu.docx';
//     // $path = 'export/ ' . $filename;
//     // $xmlWriter = IOFactory::createWriter($phpWord, 'Word2007');
//     // $xmlWriter->save($path);
//     // return $this->redirect($path);
//         // var_dump($xmlWriter);
//         // die;
//         // print getcwd($path);
//     $filename = time().'_Data_buku.docx';
//     header("Content-Description: File Transfer");
//     header('Content-Disposition: attachment; filename="' . $filename . '"');
//     header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
//     header('Content-Transfer-Encoding: binary');
//     header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
//     header('Expires: 0');
//     $xmlWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
//     $xmlWriter->save("php://output");
// }
}
