<?php

use yii\helpers\Url;
use yii\helpers\Html;

?>
<div class="row">
	<center>
		<?= Html::img('@web/sampul/' . @$model->sampul_upload, ['style'=>'height:250px', 'width:250px;']);?>
		<hr width="60%">
		<b><h2>Perubahan Jadwal Kuliah Kampus Politeknik Negeri Indramayu</h2></b>
		<h3>Pada Tanggal : </h3><h4><?= $model->tgl ?></h4>
		<p><h3><b>Mata Kuliah :</b></h3><h4><?= @$model->matakuliah->nama; ?></h4></p>
		<p><h3><b>Kelas : </b></h3><h4><?= @$model->kelas->nama; ?></h4></p>
		<p><h3><b>Ruang : </b></h3><h4><?= @$model->ruang->nama; ?></h4></p>
		<hr width="60%">
	</center>
</div>