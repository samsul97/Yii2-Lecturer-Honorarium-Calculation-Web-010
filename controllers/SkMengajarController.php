<?php

namespace app\controllers;

use Yii;
use app\models\SkMengajar;
use app\models\SkMengajarSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;
use app\models\User;
use yii\base\Behavior;
use yii\filters\AccessControl;

/**
 * SkMengajarController implements the CRUD actions for SkMengajar model.
 */
class SkMengajarController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::className(),
                'only' => ['logout', 'index'],
                'rules' => [
                    [
                        'actions' => ['view', 'create', 'index', 'delete'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function() {
                            return User::isAdmin() || User::isWadir() || User::isDosen() || User::isKeuangan() || User::isKetuajurusan() || User::isAkademik();
                        }
                    ],
                    // true berarti bisa mengakses.
                    [
                        'actions' => ['create', 'delete'],
                        'allow' => false,
                        'roles' => ['@'],
                        'matchCallback' => function()
                        {
                            return User::isDosen() || User::isKeuangan() || User::isKetuajurusan() || User::isAkademik();
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
     * Lists all SkMengajar models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new SkMengajarSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single SkMengajar model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new SkMengajar model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new SkMengajar();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {

            $file = UploadedFile::getInstance($model, 'file_upload');
            // var_dump($file);
            // die;
            
            $model->file = time(). '_' . $file->name;
            
            // if ($file == null) {
            //     return true;
            // }

            $model->save(false);

            $file->saveAs(Yii::$app->basePath. '/web/upload/' . $model->file);
            Yii::$app->session->setFlash('success', 'Berhasil menambahkan SK Mengajar');

            return $this->redirect(['index', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing SkMengajar model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $file_lama = $model->file;
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $file = UploadedFile::getInstance($model, 'file_upload');
            if ($file !== null) {
                unlink(Yii::$app->basePath . '/web/upload/' . $file_lama);
                $model->file = time() . '_' . $file->name;
                $file->saveAs(Yii::$app->basePath . '/web/upload/' . $model->file);
            } else{
                $model->file = $file_lama;
            }
            $model->save(false);
            Yii::$app->session->setFlash('success', 'Data berhasil di Edit');
            return $this->redirect(['index', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing SkMengajar model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the SkMengajar model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return SkMengajar the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = SkMengajar::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
