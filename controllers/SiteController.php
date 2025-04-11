<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\User;
use yii\data\ActiveDataProvider;
use yii\widgets\ListView;
use app\models\Hononarium;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use app\models\CreateUser;
use app\models\UserRole;
use app\models\Dosen;
use app\models\Ketuajurusan;
use app\models\Keuangan;
use app\models\Akademik;
use app\models\BagWadir;
use app\models\MJurusan;
use app\models\NewPassword;
use yii\authclient\client\Facebook;
use app\models\NewPass;




class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        if (!Yii::$app->user->isGuest)
        {
            return $this->redirect(['site/dashboard']);
        }
        else
        {
            return $this->redirect(['site/login']);
        }
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }
    public function actionDashboard()
    {

        if (User::isAdmin() || User::isDosen() || User::isAkademik() || User::isKetuajurusan() || User::isKeuangan() || User::isWadir()) {
            $jadwalkuliah = Yii::$app->user->identity->id_dosen;
            $provider = new ActiveDataProvider([
                'query' => \app\models\Jadwalkuliah::find()->andWhere(['jadwalkuliah.id_dosen' => $jadwalkuliah]),
                'pagination' => [
                    'pageSize' => 6
                ],
            ]);
            return $this->render('dashboard', ['provider' => $provider]);
        }

        return $this->redirect('site/login');
    }

    public function actionAkun()
    {
        // $this->layout = 'main-login';
        $model = new CreateUser();
        if ($model->load(Yii::$app->request->post()) && $model->validate())
        {
             // ini udah bener id tertentu
            if ($model->id_user_role == 2)
            {
                $dosen = new Dosen();
                $dosen->nama = $model->nama;
                $dosen->email = $model->email;
                $dosen->telp = $model->telp;
                $dosen->id_jabatan = 1;
                $dosen->id_tugastambah = 1;
                $dosen->id_bebanminimal = 1;
                $dosen->id_jurusan = $model->id_jurusan;

                $foto = UploadedFile::getInstance($model, 'foto');
                $model->foto = time(). '_' . $foto->name;
                $foto->saveAs(Yii::$app->basePath. '/web/user/' . $model->foto);
                $dosen->foto = $model->foto;
                if (!$dosen->save())
                {
                    echo 'Error di Dosen<br>';
                    var_dump($dosen->errors);
                    die;
                }
        // 2. Save User Role
                $userrole = new UserRole();
                $userrole->nama = $model->nama;
                

        // 3. Save Jurusan
                $jurusan = new MJurusan();
                $jurusan->nama = $model->nama;
                
        // 4. Save User
                $user = new User();
                $user->id_dosen = $dosen->id;
                $user->id_user_role = $userrole->id;
                $user->id_jurusan = $dosen->id_jurusan;
                // $user->id_user_role = $userrole->id;
                // $user->id_jurusan = $jurusan->id;
                $user->username = $model->username;
                $user->password = Yii::$app->getSecurity()->generatePasswordHash($model->password);
                $user->id_ketuajurusan = 0;
                $user->id_akademik = 0;
                $user->id_keuangan = 0;
                $user->id_wadir = 0;
                $user->id_user_role = 2;
                $user->status = 1;
                $user->token = Yii::$app->getSecurity()->generateRandomString(100);
                if (!$user->save())
                {
                    echo 'Error di User<br>';
                    var_dump($user->errors);
                    die;
                }

                Yii::$app->session->setFlash('success', 'Berhasil Tambah Akun Dosen.');

                return $this->redirect(['dosen/index']);
            }

            if ($model->id_user_role==3) {
                $akademik = new Akademik();
                $akademik->nama = $model->nama;
                $akademik->email = $model->email;
                $akademik->telp = $model->telp;
                $akademik->id_jurusan = $model->id_jurusan;

                $foto = UploadedFile::getInstance($model, 'foto');
                $model->foto = time(). '_' . $foto->name;
                $foto->saveAs(Yii::$app->basePath. '/web/user/' . $model->foto);
                $akademik->foto = $model->foto;
                if (!$akademik->save())
                {
                    echo 'Error di Akademik<br>';
                    var_dump($akademik->errors);
                    die;
                }

                // 2. Save User Role
                $userrole = new UserRole();
                $userrole->nama = $model->nama;
                

        // 3. Save Jurusan
                $jurusan = new MJurusan();
                $jurusan->nama = $model->nama;

                $user = new User();
                $user->id_akademik = $akademik->id;
                $user->id_user_role = $akademik->id;
                $user->id_jurusan = $akademik->id_jurusan;
                // $user->id_user_role = $userrole->id;
                $user->username = $model->username;
                $user->password = Yii::$app->getSecurity()->generatePasswordHash($model->password);
                $user->id_dosen = 0;
                $user->id_ketuajurusan = 0;
                $user->id_keuangan = 0;
                $user->id_wadir = 0;
                $user->id_user_role = 3;
                $user->status = 1;
            // token berfungsi untuk membedakan atau menjadikan identitas sebuah user. untuk mengamankan sebuah transaksi.
                $user->token = Yii::$app->getSecurity()->generateRandomString(100);
                $user->save();
                Yii::$app->session->setFlash('success', 'Berhasil Tambah Akun Akademik.');

                return $this->redirect(['akademik/index']);
            }

            if ($model->id_user_role==4) {
                $ketuajurusan = new Ketuajurusan();
                $ketuajurusan->nama = $model->nama;
                $ketuajurusan->email = $model->email;
                $ketuajurusan->telp = $model->telp;
                $ketuajurusan->id_jurusan = $model->id_jurusan;

                $foto = UploadedFile::getInstance($model, 'foto');
                $model->foto = time(). '_' . $foto->name;
                $foto->saveAs(Yii::$app->basePath. '/web/user/' . $model->foto);
                $ketuajurusan->foto = $model->foto;
                if (!$ketuajurusan->save())
                {
                    echo 'Error di Ketua Jurusan<br>';
                    var_dump($ketuajurusan->errors);
                    die;
                }

                // 2. Save User Role
                $userrole = new UserRole();
                $userrole->nama = $model->nama;
                

        // 3. Save Jurusan
                $jurusan = new MJurusan();
                $jurusan->nama = $model->nama;

                $user = new User();
                $user->id_ketuajurusan = $ketuajurusan->id;
                $user->id_user_role = $ketuajurusan->id;
                $user->id_jurusan = $ketuajurusan->id_jurusan;
                // $user->id_user_role = $userrole->id;
                $user->username = $model->username;
                $user->password = Yii::$app->getSecurity()->generatePasswordHash($model->password);
                $user->id_akademik = 0;
                $user->id_keuangan = 0;
                $user->id_dosen = 0;
                $user->id_wadir = 0;
                $user->id_user_role = 4;
                $user->status = 1;
            // token berfungsi untuk membedakan atau menjadikan identitas sebuah user. untuk mengamankan sebuah transaksi.
                $user->token = Yii::$app->getSecurity()->generateRandomString(100);
                $user->save();
                Yii::$app->session->setFlash('success', 'Berhasil Tambah Akun Jurusan.');

                return $this->redirect(['ketuajurusan/index']);
            }


            if ($model->id_user_role==5) {
                $keuangan = new Keuangan();
                $keuangan->nama = $model->nama;
                $keuangan->email = $model->email;
                $keuangan->telp = $model->telp;
                $keuangan->id_jurusan = $model->id_jurusan;

                $foto = UploadedFile::getInstance($model, 'foto');
                $model->foto = time(). '_' . $foto->name;
                $foto->saveAs(Yii::$app->basePath. '/web/user/' . $model->foto);
                $keuangan->foto = $model->foto;
                if (!$keuangan->save())
                {
                    echo 'Error di Keuangan<br>';
                    var_dump($keuangan->errors);
                    die;
                }

                // 2. Save User Role
                $userrole = new UserRole();
                $userrole->nama = $model->nama;
                

        // 3. Save Jurusan
                $jurusan = new MJurusan();
                $jurusan->nama = $model->nama;

                $user = new User();
                $user->id_keuangan = $keuangan->id;
                $user->id_user_role = $keuangan->id;
                $user->id_jurusan = $keuangan->id_jurusan;
                // $user->id_user_role = $userrole->id;
                $user->username = $model->username;
                $user->password = Yii::$app->getSecurity()->generatePasswordHash($model->password);
                $user->id_dosen = 0;
                $user->id_ketuajurusan = 0;
                $user->id_wadir = 0;
                $user->id_akademik = 0;
                $user->id_user_role = 5;
                $user->status = 1;
            // token berfungsi untuk membedakan atau menjadikan identitas sebuah user. untuk mengamankan sebuah transaksi.
                $user->token = Yii::$app->getSecurity()->generateRandomString(100);
                $user->save();
                Yii::$app->session->setFlash('success', 'Berhasil Tambah Akun Keuangan.');

                return $this->redirect(['keuangan/index']);
            }

            if ($model->id_user_role==6) {
                $wadir = new BagWadir();
                $wadir->nama = $model->nama;
                $wadir->email = $model->email;
                $wadir->telp = $model->telp;
                $wadir->id_jurusan = $model->id_jurusan;
                
                $foto = UploadedFile::getInstance($model, 'foto');
                $model->foto = time(). '_' . $foto->name;
                $foto->saveAs(Yii::$app->basePath. '/web/user/' . $model->foto);
                $wadir->foto = $model->foto;
                if (!$wadir->save())
                {
                    echo 'Error di Wadir<br>';
                    var_dump($wadir->errors);
                    die;
                }

                // 2. Save User Role
                $userrole = new UserRole();
                $userrole->nama = $model->nama;
                

        // 3. Save Jurusan
                $jurusan = new MJurusan();
                $jurusan->nama = $model->nama;
                

                $user = new User();
                $user->id_wadir = $wadir->id;
                $user->id_user_role = $wadir->id;
                $user->id_jurusan = $wadir->id_jurusan;
                // $user->id_user_role = $userrole->id;
                $user->username = $model->username;
                $user->password = Yii::$app->getSecurity()->generatePasswordHash($model->password);
                $user->id_dosen = 0;
                $user->id_ketuajurusan = 0;
                $user->id_akademik = 0;
                $user->id_keuangan = 0;
                $user->id_user_role = 6;
                $user->status = 1;
            // token berfungsi untuk membedakan atau menjadikan identitas sebuah user. untuk mengamankan sebuah transaksi.
                $user->token = Yii::$app->getSecurity()->generateRandomString(100);
                $user->save();
                Yii::$app->session->setFlash('success', 'Berhasil Tambah Akun Wadir.');

                return $this->redirect(['bag-wadir/index']);
            }
            else {
                return $this->redirect(['site/akun']);
            }
        }
        return $this->render('akun', [
            'model' => $model,
        ]);
    }
    public function actionLupa()
    {
        $this->layout = 'main-login1';
        return $this->render('lupa');
    }
}
