<?php

namespace app\controllers;

use Yii;
use app\models\Kehadiran;
use app\models\KehadiranSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\models\User;
use Mpdf\Mpdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use app\models\Dosen;
use app\models\MJurusan;
use app\models\Matakuliah;

/**
 * KehadiranController implements the CRUD actions for Kehadiran model.
 */
class KehadiranController extends Controller
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
                return User::isAdmin() || User::isWadir() || User::isAkademik() || User::isDosen() || User::isKetuajurusan();
              }
            ],
                    // true berarti bisa mengakses.
            [
              'actions' => ['view', 'create'],
              'allow' => false,
              'roles' => ['@'],
              'matchCallback' => function()
              {
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
     * Lists all Kehadiran models.
     * @return mixed
     */
    public function actionIndex()
    {
      $searchModel = new KehadiranSearch();
      $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

      if (Yii::$app->user->identity->id_user_role == 2) {  //dosen
        $kehadiran = Yii::$app->user->identity->id_dosen;
        if ($kehadiran) {
          $dataProvider->query->andWhere(['kehadiran.id_dosen' => $kehadiran]);
        }
      }

      if (Yii::$app->user->identity->id_user_role == 4) { //admin jurusan
        //$kehadiran = Yii::$app->user->identity->id_dosen;
        $jurusan = Yii::$app->user->identity->id_jurusan;
        //if ($kehadiran) {
          $model_mk = Matakuliah::findAll(['id_jurusan' => $jurusan]);
          $id_mk = [];
          foreach ($model_mk as $key => $value) {
            $id_mk[] = $value->id;
          }
          // var_dump($id_mk);
          // die;
          $dataProvider->query->andWhere(['in', 'kehadiran.id_mk', $id_mk]);
      }

      return $this->render('index', [
        'searchModel' => $searchModel,
        'dataProvider' => $dataProvider,
      ]);
    }

    /**
     * Displays a single Kehadiran model.
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
     * Creates a new Kehadiran model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
      $model = new Kehadiran();

      if ($model->load(Yii::$app->request->post()) && $model->save()) {
        Yii::$app->session->setFlash('success', 'Data berhasil ditambahkan');
        return $this->redirect(['index', 'id' => $model->id]);
      }

      return $this->render('create', [
        'model' => $model,
      ]);
    }

    /**
     * Updates an existing Kehadiran model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
      $model = $this->findModel($id);

      if ($model->load(Yii::$app->request->post())) {

        if ($model->save()) {

          Yii::$app->session->setFlash('success', 'Data berhasil di Edit');
          return $this->redirect(['index', 'id' => $model->id]);

        } else {

          var_dump($model->errors);
          die;

        }
      }

      return $this->render('update', [
        'model' => $model,
      ]);
    }

    /**
     * Deletes an existing Kehadiran model.
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
     * Finds the Kehadiran model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Kehadiran the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
      if (($model = Kehadiran::findOne($id)) !== null) {
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
      $sheet->getColumnDimension('F')->setWidth(20);
      $sheet->getColumnDimension('G')->setWidth(20);
      

      $sheet->setCellValue('A3', strtoupper('No'));
      $sheet->setCellValue('B3', strtoupper('Tanggal'));
      $sheet->setCellValue('C3', strtoupper('Hadir'));
      $sheet->setCellValue('D3', strtoupper('Kelas'));
      $sheet->setCellValue('E3', strtoupper('Dosen'));
      $sheet->setCellValue('F3', strtoupper('Mata Kuliah'));
      $sheet->setCellValue('G3', strtoupper('Keterangan'));
      

      $spreadsheet->getActiveSheet()->setCellValue('A1', 'Kehadiran Doshen');

      $spreadsheet->getActiveSheet()->getStyle('A3:G3')->getFill()->setFillType(Fill::FILL_SOLID);
      $spreadsheet->getActiveSheet()->getStyle('A3:G3')->getFill()->getStartColor()->setARGB('d8d8d8');
      $spreadsheet->getActiveSheet()->mergeCells('A1:G1');
      $spreadsheet->getActiveSheet()->getDefaultRowDimension('3')->setRowHeight(25);
      $sheet->getStyle('A1:G3')->getFont()->setBold(true);
      $sheet->getStyle('A1:G3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

      $row = 3;
      $i=1;

      $searchModel = new Kehadiran();

      foreach($searchModel->find()->all() as $kehadiran){
        $row++;
        $sheet->setCellValue('A' . $row, $i);
        $sheet->setCellValue('B' . $row, $kehadiran->tgl);
        $sheet->setCellValue('C' . $row, $kehadiran->status);
        $sheet->setCellValue('D' . $row, $kehadiran->kelas->nama);
        $sheet->setCellValue('E' . $row, $kehadiran->dosen->nama);
        $sheet->setCellValue('F' . $row, $kehadiran->matakuliah->nama);
        $sheet->setCellValue('G' . $row, $kehadiran->keterangan);
        $i++;
      }

      $sheet->getStyle('A3:G' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
      $sheet->getStyle('D3:G' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
      $sheet->getStyle('E3:G' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);


      $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

      $sheet->getStyle('A3:G' . $row)->applyFromArray($setBorderArray);

      $filename = time() . '_Daftar Kehadiran Dosen.xlsx';
      $path = 'document/' . $filename;
      $writer = new Xlsx($spreadsheet);
      $writer->save($path);

      return $this->redirect($path);
    }
    public function actionExportPdf() 
    {
      $this->layout='main';
      $model = Kehadiran::find()->All();
      $mpdf=new mPDF();
      $mpdf->WriteHTML($this->renderPartial('exportpdf',['model'=>$model]));
      $mpdf->Output('_DataKehadiran.pdf', 'D');
      exit;
    }
    public function actionImport(){
      $modelImport = new \yii\base\DynamicModel([
        'fileImport'=>'File Import',
      ]);
      $modelImport->addRule(['fileImport'],'required');
      $modelImport->addRule(['fileImport'],'file',['extensions'=>'ods,xls,xlsx'],['maxSize'=>1024*1024]);

      if(Yii::$app->request->post()){
        $modelImport->fileImport = \yii\web\UploadedFile::getInstance($modelImport,'fileImport');
        if($modelImport->fileImport && $modelImport->validate()){
          $inputFileType = \PHPExcel_IOFactory::identify($modelImport->fileImport->tempName);
          $objReader = \PHPExcel_IOFactory::createReader($inputFileType);
          $objPHPExcel = $objReader->load($modelImport->fileImport->tempName);
          $sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
          $baseRow = 3;
          while(!empty($sheetData[$baseRow]['B'])){
            $model = new \common\models\Category;
            $model->title = (string)$sheetData[$baseRow]['B'];
            $model->description = (string)$sheetData[$baseRow]['C'];
            $model->save();
            $baseRow++;
          }
          Yii::$app->getSession()->setFlash('success','Success');
        }else{
          Yii::$app->getSession()->setFlash('error','Error');
        }
      }

      return $this->render('import',[
        'modelImport' => $modelImport,
      ]);
    }
    
  }