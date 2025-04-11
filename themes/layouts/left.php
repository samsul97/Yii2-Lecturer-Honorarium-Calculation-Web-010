<?php
use app\models\User;
?>
<aside class="main-sidebar">

    <section class="sidebar">

        <!-- Sidebar user panel -->
        <div class="user-panel">
            <div class="pull-left image">
                <?php if (User::isAdmin()): ?>
                   <?= User::getFotoAdmin(['class' => 'img-circle']); ?>
               <?php endif ?>
               <?php if (User::isDosen()): ?>
                   <?= User::getFotoDosen(['class' => 'img-circle']); ?>
               <?php endif ?>
               <?php if (User::isAkademik()): ?>
                   <?= User::getFotoAkademik(['class' => 'img-circle']); ?>
               <?php endif ?>
               <?php if (User::isKetuajurusan()): ?>
                   <?= User::getFotoKetuajurusan(['class' => 'img-circle']); ?>
               <?php endif ?>
               <?php if (User::isWadir()): ?>
                   <?= User::getFotoWadir(['class' => 'img-circle']); ?>
               <?php endif ?>
               <?php if (User::isKeuangan()): ?>
                   <?= User::getFotoKeuangan(['class' => 'img-circle']); ?>
               <?php endif ?>
           </div>
           <div class="pull-left info">
            <p>
                <?= Yii::$app->user->identity->username ?>
            </p>

            <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
        </div>
    </div>

    <!-- search form -->
    <!-- <form action="#" method="get" class="sidebar-form">
        <div class="input-group">
            <input type="text" name="q" class="form-control" placeholder="Search..."/>
            <span class="input-group-btn">
                <button type='submit' name='search' id='search-btn' class="btn btn-flat"><i class="fa fa-search"></i>
                </button>
            </span>
        </div>
    </form> -->
    <!-- /.search form -->

    <?php if (User::isAdmin()){ ?>

        <?= dmstr\widgets\Menu::widget(
            [
                'options' => ['class' => 'sidebar-menu tree', 'data-widget'=> 'tree'],
                'items' => [
                    ['label' => 'Menu A-Rium', 'options' => ['class' => 'header']],
                    ['label' => 'Dashboard', 'icon' => 'dashboard', 'url' => ['site/dashboard']],
                    ['label' => 'Mata Kuliah', 'icon' => 'graduation-cap', 'url' => ['matakuliah/index']],
                    ['label' => 'Jadwal Kuliah', 'icon' => 'calendar', 'url' => ['jadwalkuliah/index']],
                    ['label' => 'Kehadiran', 'icon' => 'globe', 'url' => ['kehadiran/index']],
                    ['label' => 'Honorarium', 'icon' => 'wrench', 'url' => ['hononarium/index']],
                    // ['label' => 'Gaji', 'icon' => 'money', 'url' => ['gaji/index']],
                    [
                        'label' => 'Data Pengguna',
                        'icon' => 'users',
                        'url' => '#',
                        'items' => [
                            ['label' => 'Dosen', 'icon' => 'user-o text-aqua', 'url' => ['dosen/index']],
                            ['label' => 'Ketua Jurusan', 'icon' => 'user-o text-blue', 'url' => ['ketuajurusan/index']],
                            ['label' => 'Akademik', 'icon' => 'user-o text-green', 'url' => ['akademik/index']],
                            ['label' => 'Keuangan', 'icon' => 'user-o text-yellow', 'url' => ['keuangan/index']],
                            ['label' => 'Wadir 1', 'icon' => 'user-o text-red', 'url' => ['bag-wadir/index']],
                            // ['label' => 'User', 'icon' => 'user', 'url' => ['user/index']],
                            // ['label' => 'User Role', 'icon' => 'user', 'url' => ['user-role/index']],
                        ],
                    ],

                    [
                        'label' => 'Akun',
                        'icon' => 'user',
                        'url' => '#',
                        'items' => [
                            // ['label' => 'Akademik', 'icon' => 'user-o text-aqua', 'url' => ['akademik/createakademik']],
                            ['label' => 'Akun', 'icon' => 'user-o text-yellow', 'url' => ['site/akun']],
                            // ['label' => 'Ketua Jurusan', 'icon' => 'user-o text-red', 'url' => ['ketuajurusan/createkajur']],
                            // ['label' => 'Keuangan', 'icon' => 'user-o text-green', 'url' => ['keuangan/createkeuangan']],
                            // ['label' => 'Wadir 1', 'icon' => 'user-o text-blue', 'url' => ['bag-wadir/createwadir']],
                        ],
                    ],

                    [
                        'label' => 'Data Master',
                        'icon' => 'tasks',
                        'url' => '#',
                        'items' => [
                            ['label' => 'Jurusan', 'icon' => 'circle-o', 'url' => ['mjurusan/index']],
                            ['label' => 'Jabatan', 'icon' => 'circle-o', 'url' => ['m-jabatan/index']],
                            ['label' => 'Kelas', 'icon' => 'circle-o', 'url' => ['m-kelas/index']],
                            ['label' => 'Semester', 'icon' => 'circle-o', 'url' => ['m-semester/index']],
                            ['label' => 'Ruang', 'icon' => 'circle-o', 'url' => ['m-ruang/index']],
                            ['label' => 'Kategori', 'icon' => 'circle-o', 'url' => ['m-kategori/index']],
                            ['label' => 'Kurikulum', 'icon' => 'circle-o', 'url' => ['m-kurikulum/index']],
                            ['label' => 'Beban Mengajar', 'icon' => 'circle-o', 'url' => ['beban-minimal/index']],
                            ['label' => 'Tugas Tambahan', 'icon' => 'circle-o', 'url' => ['tugas-tambahan/index']],
                            ['label' => 'Filter Semester', 'icon' => 'circle-o', 'url' => ['filter-semester/index']],
                            // ['label' => 'Kategori', 'icon' => 'circle-o', 'url' => ['m-kategori/index']],
                            // ['label' => 'Hari', 'icon' => 'circle-o', 'url' => ['m-hari/index']],
                            // ['label' => 'Golongan', 'icon' => 'circle-o', 'url' => ['m-golongan/index']],
                        ],
                    ],
                    [
                        'label' => 'Surat Keputusan',
                        'icon' => 'book',
                        'url' => '#',
                        'items' => [
                            ['label' => 'SK Kelebihan Jam Mengajar', 'icon' => 'wrench', 'url' => ['sk-mengajar/index']],
                            ['label' => 'SK Hononarium', 'icon' => 'wrench', 'url' => ['s-k-honor/index']],    
                        ],
                    ],
                    // ['label' => 'Dosen', 'url' => ['site/login'], 'visible' => Yii::$app->user->isGuest],

                ],
            ]
        ) ?>

    <?php } elseif(User::isDosen() && Yii::$app->user->identity->id_jurusan == 1) { ?>
        <?= dmstr\widgets\Menu::widget(
            [
                'options' => ['class' => 'sidebar-menu tree', 'data-widget'=> 'tree'],
                'items' => [
                    ['label' => 'Menu A-Rium', 'options' => ['class' => 'header']],
                    ['label' => 'Dashboard', 'icon' => 'dashboard', 'url' => ['site/dashboard']],
                    ['label' => 'Jadwal Kuliah', 'icon' => 'calendar', 'url' => ['jadwalkuliah/index']],
                    ['label' => 'Mata Kuliah', 'icon' => 'graduation-cap', 'url' => ['matakuliah/index']],
                    ['label' => 'Kehadiran', 'icon' => 'globe', 'url' => ['kehadiran/index']],
                    ['label' => 'Honorarium', 'icon' => 'wrench', 'url' => ['hononarium/index']],
                    ['label' => 'Teknik Informatika', 'icon' => 'dashboard', 'url' => ['site/about']],
                    // ['label' => 'Reset Password', ["user/view","id" => Yii::$app->user->identity->id], 'icon' => 'key', 'url' => ['user/change-password']],
                    // ['label' => 'Gaji', 'icon' => 'wrench', 'url' => ['gaji/index']],
                ],  
            ]
        ) ?>

    <?php } elseif(User::isDosen() && Yii::$app->user->identity->id_jurusan == 2) { ?>
        <?= dmstr\widgets\Menu::widget(
            [
                'options' => ['class' => 'sidebar-menu tree', 'data-widget'=> 'tree'],
                'items' => [
                    ['label' => 'Menu A-Rium', 'options' => ['class' => 'header']],
                    ['label' => 'Dashboard', 'icon' => 'dashboard', 'url' => ['site/dashboard']],
                    ['label' => 'Jadwal Kuliah', 'icon' => 'calendar', 'url' => ['jadwalkuliah/index']],
                    ['label' => 'Mata Kuliah', 'icon' => 'graduation-cap', 'url' => ['matakuliah/index']],
                    ['label' => 'Kehadiran', 'icon' => 'globe', 'url' => ['kehadiran/index']],
                    ['label' => 'Honorarium', 'icon' => 'wrench', 'url' => ['hononarium/index']],
                    ['label' => 'Teknik Mesin', 'icon' => 'dashboard', 'url' => ['site/about']],
                    // ['label' => 'Reset Password', ["user/view","id" => Yii::$app->user->identity->id], 'icon' => 'key', 'url' => ['user/change-password']],
                    // ['label' => 'Gaji', 'icon' => 'wrench', 'url' => ['gaji/index']],
                ],  
            ]
        ) ?>

    <?php } elseif(User::isDosen() && Yii::$app->user->identity->id_jurusan == 3) { ?>
        <?= dmstr\widgets\Menu::widget(
            [
                'options' => ['class' => 'sidebar-menu tree', 'data-widget'=> 'tree'],
                'items' => [
                    ['label' => 'Menu A-Rium', 'options' => ['class' => 'header']],
                    ['label' => 'Dashboard', 'icon' => 'dashboard', 'url' => ['site/dashboard']],
                    ['label' => 'Jadwal Kuliah', 'icon' => 'calendar', 'url' => ['jadwalkuliah/index']],
                    ['label' => 'Mata Kuliah', 'icon' => 'graduation-cap', 'url' => ['matakuliah/index']],
                    ['label' => 'Kehadiran', 'icon' => 'globe', 'url' => ['kehadiran/index']],
                    ['label' => 'Honorarium', 'icon' => 'wrench', 'url' => ['hononarium/index']],
                    ['label' => 'Teknik Pendingin', 'icon' => 'dashboard', 'url' => ['site/about']],
                    // ['label' => 'Reset Password', ["user/view","id" => Yii::$app->user->identity->id], 'icon' => 'key', 'url' => ['user/change-password']],
                    // ['label' => 'Gaji', 'icon' => 'wrench', 'url' => ['gaji/index']],
                ],  
            ]
        ) ?>


    <?php } elseif(User::isAkademik()) { ?>
        <?= dmstr\widgets\Menu::widget(
            [
                'options' => ['class' => 'sidebar-menu tree', 'data-widget'=> 'tree'],
                'items' => [
                    ['label' => 'Menu A-Rium', 'options' => ['class' => 'header']],
                    ['label' => 'Dashboard', 'icon' => 'dashboard', 'url' => ['site/dashboard']],
                    ['label' => 'Kehadiran', 'icon' => 'globe', 'url' => ['kehadiran/index']],
                    ['label' => 'Jadwal Kuliah', 'icon' => 'book', 'url' => ['jadwalkuliah/index']],
                    ['label' => 'Mata Kuliah', 'icon' => 'graduation-cap', 'url' => ['matakuliah/index']],
                    ['label' => 'SK Kelebihan Jam Mengajar', 'icon' => 'wrench', 'url' => ['sk-mengajar/index']],
                    ['label' => 'SK Honor', 'icon' => 'wrench', 'url' => ['s-k-honor/index']],
                    // ['label' => 'Hononarium', 'icon' => 'wrench', 'url' => ['hononarium/index']],
                    // ['label' => 'Gaji', 'icon' => 'wrench', 'url' => ['gaji/index']],
                ],  
            ]
        ) ?>

    <?php } elseif(User::isKetuajurusan() && Yii::$app->user->identity->id_jurusan == 1) {?>

        <?= dmstr\widgets\Menu::widget(
         [
            'options' => ['class' => 'sidebar-menu tree', 'data-widget'=> 'tree'],
            'items' => [
                ['label' => 'Menu A-Rium', 'options' => ['class' => 'header']],
                ['label' => 'Dashboard', 'icon' => 'dashboard', 'url' => ['site/dashboard']],
                ['label' => 'Kehadiran', 'icon' => 'globe', 'url' => ['kehadiran/index']],
                ['label' => 'Mata Kuliah', 'icon' => 'graduation-cap', 'url' => ['matakuliah/index']],
                ['label' => 'Jadwal Kuliah', 'icon' => 'calendar', 'url' => ['jadwalkuliah/index']],
                ['label' => 'Hononarium', 'icon' => 'wrench', 'url' => ['hononarium/index']],
                ['label' => 'SK Kelebihan Jam Mengajar', 'icon' => 'wrench', 'url' => ['sk-mengajar/index']],
                ['label' => 'SK Honor', 'icon' => 'wrench', 'url' => ['s-k-honor/index']],
                ['label' => 'Jurusan TI', 'icon' => 'wrench', 'url' => ['site/dashboard']],
                    // ['label' => 'Gaji', 'icon' => 'wrench', 'url' => ['gaji/index']],
            ],  
        ]
    ) ?>

<?php } elseif(User::isKetuajurusan() && Yii::$app->user->identity->id_jurusan == 2) {?>

        <?= dmstr\widgets\Menu::widget(
         [
            'options' => ['class' => 'sidebar-menu tree', 'data-widget'=> 'tree'],
            'items' => [
                ['label' => 'Menu A-Rium', 'options' => ['class' => 'header']],
                ['label' => 'Dashboard', 'icon' => 'dashboard', 'url' => ['site/dashboard']],
                ['label' => 'Mata Kuliah', 'icon' => 'graduation-cap', 'url' => ['matakuliah/index']],
                ['label' => 'Kehadiran', 'icon' => 'globe', 'url' => ['kehadiran/index']],
                ['label' => 'Jadwal Kuliah', 'icon' => 'calendar', 'url' => ['jadwalkuliah/index']],
                ['label' => 'SK Kelebihan Jam Mengajar', 'icon' => 'wrench', 'url' => ['sk-mengajar/index']],
                ['label' => 'SK Honor', 'icon' => 'wrench', 'url' => ['s-k-honor/index']],
                ['label' => 'Hononarium', 'icon' => 'wrench', 'url' => ['hononarium/index']],
                ['label' => 'Jurusan TM', 'icon' => 'wrench', 'url' => ['site/dashboard']],
                    // ['label' => 'Gaji', 'icon' => 'wrench', 'url' => ['gaji/index']],
            ],  
        ]
    ) ?>

<?php } elseif(User::isKetuajurusan() && Yii::$app->user->identity->id_jurusan == 3) {?>

        <?= dmstr\widgets\Menu::widget(
         [
            'options' => ['class' => 'sidebar-menu tree', 'data-widget'=> 'tree'],
            'items' => [
                ['label' => 'Menu A-Rium', 'options' => ['class' => 'header']],
                ['label' => 'Dashboard', 'icon' => 'dashboard', 'url' => ['site/dashboard']],
                ['label' => 'Mata Kuliah', 'icon' => 'graduation-cap', 'url' => ['matakuliah/index']],
                ['label' => 'Kehadiran', 'icon' => 'globe', 'url' => ['kehadiran/index']],
                ['label' => 'Jadwal Kuliah', 'icon' => 'calendar', 'url' => ['jadwalkuliah/index']],
                ['label' => 'Hononarium', 'icon' => 'wrench', 'url' => ['hononarium/index']],
                ['label' => 'SK Kelebihan Jam Mengajar', 'icon' => 'wrench', 'url' => ['sk-mengajar/index']],
                ['label' => 'SK Honor', 'icon' => 'wrench', 'url' => ['s-k-honor/index']],
                ['label' => 'Jurusan TP', 'icon' => 'wrench', 'url' => ['site/dashboard']],
                    // ['label' => 'Gaji', 'icon' => 'wrench', 'url' => ['gaji/index']],
            ],  
        ]
    ) ?>

<?php } elseif(User::isKeuangan()) {?>

    <?= dmstr\widgets\Menu::widget(
     [
        'options' => ['class' => 'sidebar-menu tree', 'data-widget'=> 'tree'],
        'items' => [
            ['label' => 'Menu A-Rium', 'options' => ['class' => 'header']],
            ['label' => 'Dashboard', 'icon' => 'dashboard', 'url' => ['site/dashboard']],
            // ['label' => 'Jadwal Kuliah', 'icon' => 'calendar', 'url' => ['jadwalkuliah/index']],
            // ['label' => 'Mata Kuliah', 'icon' => 'graduation-cap', 'url' => ['matakuliah/index']],
            // ['label' => 'Kehadiran', 'icon' => 'globe', 'url' => ['kehadiran/index']],
            ['label' => 'Honorarium', 'icon' => 'wrench', 'url' => ['hononarium/index']],
            // ['label' => 'Laporan Keuangan', 'icon' => 'wrench', 'url' => ['hononarium/index']],
                    // ['label' => 'Gaji', 'icon' => 'wrench', 'url' => ['gaji/index']],
            [
                'label' => 'Surat Keputusan',
                'icon' => 'book',
                'url' => '#',
                'items' => [
                    ['label' => 'SK Kelebihan Jam Mengajar', 'icon' => 'wrench', 'url' => ['sk-mengajar/index']],
                    ['label' => 'SK Hononarium', 'icon' => 'wrench', 'url' => ['s-k-honor/index']],    
                ],
            ],
        ],  
    ]
) ?>



<?php } elseif(User::isWadir()) {?>

    <?= dmstr\widgets\Menu::widget(
     [
        'options' => ['class' => 'sidebar-menu tree', 'data-widget'=> 'tree'],
        'items' => [
            ['label' => 'Menu A-Rium', 'options' => ['class' => 'header']],
            ['label' => 'Dashboard', 'icon' => 'dashboard', 'url' => ['site/dashboard']],
            // ['label' => 'Jadwal Kuliah', 'icon' => 'calendar', 'url' => ['jadwalkuliah/index']],
            // ['label' => 'Mata Kuliah', 'icon' => 'graduation-cap', 'url' => ['matakuliah/index']],
            // ['label' => 'Kehadiran', 'icon' => 'globe', 'url' => ['kehadiran/index']],
            ['label' => 'Honorarium', 'icon' => 'wrench', 'url' => ['hononarium/index']],
            [
                'label' => 'Surat Keputusan',
                'icon' => 'book',
                'url' => '#',
                'items' => [
                    ['label' => 'SK Kelebihan Jam Mengajar', 'icon' => 'wrench', 'url' => ['sk-mengajar/index']],
                    ['label' => 'SK Hononarium', 'icon' => 'wrench', 'url' => ['s-k-honor/index']],    
                ],
            ],
                    // ['label' => 'Gaji', 'icon' => 'wrench', 'url' => ['gaji/index']],
        ],  
    ]
) ?>

<?php } ?>
</section>
</aside>
