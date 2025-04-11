<?php

namespace app\controllers;

use Yii;
use app\models\Matakuliah;
use app\models\MatakuliahSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\models\User;
use Mpdf\Mpdf;
use app\models\Kehadiran;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * MatakuliahController implements the CRUD actions for Matakuliah model.
 */
class MatakuliahController extends Controller
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
              'actions' => ['view', 'create', 'index'],
              'allow' => false,
              'roles' => ['@'],
              'matchCallback' => function()
              {
                return User::isWadir() || User::isKeuangan(); //|| User::isKetuajurusan()
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
     * Lists all Matakuliah models.
     * @return mixed
     */
    public function actionIndex()
    {
      $searchModel = new MatakuliahSearch();
      $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

      if (Yii::$app->user->identity->id_user_role == 4) {  //dosen
        $jurusan = Yii::$app->user->identity->id_jurusan;
        if ($jurusan) {
          $dataProvider->query->andWhere(['matakuliah.id_jurusan' => $jurusan]);
        }
      }
      return $this->render('index', [
        'searchModel' => $searchModel,
        'dataProvider' => $dataProvider,
      ]);
    }

    /**
     * Displays a single Matakuliah model.
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
     * Creates a new Matakuliah model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
      $model = new Matakuliah();

      if ($model->load(Yii::$app->request->post()) && $model->save()) {
        Yii::$app->session->setFlash('success', 'Data berhasil ditambahkan');
        return $this->redirect(['index', 'id' => $model->id]);
      }

      return $this->render('create', [
        'model' => $model,
      ]);
    }

    /**
     * Updates an existing Matakuliah model.
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
        return $this->redirect(['index', 'id' => $model->id]);
      }

      return $this->render('update', [
        'model' => $model,
      ]);
    }

    /**
     * Deletes an existing Matakuliah model.
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
     * Finds the Matakuliah model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Matakuliah the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
      if (($model = Matakuliah::findOne($id)) !== null) {
        return $model;
      }

      throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionExportExcel()
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
      // $sheet->getColumnDimension('F')->setWidth(20);
      // $sheet->getColumnDimension('G')->setWidth(20);
      // $sheet->getColumnDimension('H')->setWidth(20);
      // $sheet->getColumnDimension('I')->setWidth(20);
      // $sheet->getColumnDimension('J')->setWidth(20);
      // $sheet->getColumnDimension('K')->setWidth(20);

      $sheet->setCellValue('A3', strtoupper('No'));
      $sheet->setCellValue('B3', strtoupper('Mata Kuliah'));
      $sheet->setCellValue('C3', strtoupper('Teori'));
      $sheet->setCellValue('D3', strtoupper('Praktek'));
      $sheet->setCellValue('E3', strtoupper('Kurikulum'));
      // $sheet->setCellValue('F3', strtoupper('Kelas'));
      // $sheet->setCellValue('G3', strtoupper('Tahun Akademik'));
      // $sheet->setCellValue('H3', strtoupper('Semester'));
      // $sheet->setCellValue('I3', strtoupper('Jam'));
      // $sheet->setCellValue('J3', strtoupper('Ruang'));
      // $sheet->setCellValue('K3', strtoupper('Hari'));

      $spreadsheet->getActiveSheet()->setCellValue('A1', 'Mata Kuliah');

      $spreadsheet->getActiveSheet()->getStyle('A3:E3')->getFill()->setFillType(Fill::FILL_SOLID);
      $spreadsheet->getActiveSheet()->getStyle('A3:E3')->getFill()->getStartColor()->setARGB('d8d8d8');
      $spreadsheet->getActiveSheet()->mergeCells('A1:E1');
      $spreadsheet->getActiveSheet()->getDefaultRowDimension('3')->setRowHeight(25);
      $sheet->getStyle('A1:E3')->getFont()->setBold(true);
      $sheet->getStyle('A1:E3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

      $row = 3;
      $i=1;

      $searchModel = new Matakuliah();

      foreach($searchModel->find()->all() as $matakuliah){
        $row++;
        $sheet->setCellValue('A' . $row, $i);
        $sheet->setCellValue('B' . $row, $matakuliah->nama);
        $sheet->setCellValue('C' . $row, $matakuliah->teori);
        $sheet->setCellValue('D' . $row, $matakuliah->praktek);
        $sheet->setCellValue('E' . $row, $matakuliah->kurikulum->nama);
        // $sheet->setCellValue('F' . $row, $jadwalkuliah->kelas->nama);
        // $sheet->setCellValue('G' . $row, $jadwalkuliah->thn_akademik);
        // $sheet->setCellValue('H' . $row, $jadwalkuliah->semester->nama);
        // $sheet->setCellValue('I' . $row, $jadwalkuliah->jam);
        // $sheet->setCellValue('J' . $row, $jadwalkuliah->ruang->nama);
        // $sheet->setCellValue('K' . $row, $jadwalkuliah->hari);

        $i++;
      }

      $sheet->getStyle('A3:E' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
      $sheet->getStyle('D3:E' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
      $sheet->getStyle('E3:E' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);


      $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

      $sheet->getStyle('A3:E' . $row)->applyFromArray($setBorderArray);

      $filename = time() . '_Daftar Mata Kuliah.xlsx';
      $path = 'document/' . $filename;
      $writer = new Xlsx($spreadsheet);
      $writer->save($path);

      return $this->redirect($path);
    }

    public function actionExportPdf() 
    {
      $this->layout='main';
      $model = Matakuliah::find()->All();
      $mpdf=new mPDF();
      $mpdf->WriteHTML($this->renderPartial('exportpdf',['model'=>$model]));
      $mpdf->Output('_DataMataKuliah.pdf', 'D');
      exit;
    }

    public function actionEditStatus($id, $status)
    {
      $model = $this->findModel($id);
      $model->status = $status;
      if ($model->save(false)) {
        Yii::$app->session->setFlash('Berhasil', 'Status telah di Ubah');
        return $this->redirect(['matakuliah/index']);
      }
      else
      {
        echo "Weak";
        var_dump($model->errors);
        die;
      }
    }
  }
