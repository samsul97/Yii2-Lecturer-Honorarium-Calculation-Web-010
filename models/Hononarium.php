<?php

namespace app\models;

use Yii;
use app\models\FilterSemester;

/**
 * This is the model class for table "hononarium".
 *
 * @property int $id
 * @property int $id_hadir
 * @property int $id_dosen
 * @property int $id_mk
 * @property string $periode
 * @property string $jum_sks
 * @property string $jum_hadir
 * @property string $jum_honor
 */
class Hononarium extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'hononarium';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_hadir', 'id_dosen', 'id_mk', 'periode', 'jum_sks', 'jum_hadir', 'jum_honor'], 'required'],
            [['id_hadir', 'id_dosen', 'id_mk'], 'integer'],
            [['periode', 'jum_sks', 'jum_hadir', 'jum_honor'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'id_hadir' => 'Kehadiran',
            'id_dosen' => 'Dosen',
            'id_mk' => 'Mata Kuliah',
            'periode' => 'Periode',
            'jum_sks' => 'Total SKS',
            'jum_hadir' => 'Total Kehadiran',
            'jum_honor' => 'Total Honor',
        ];
    }
    public static function getCount()
    {
        return static::find()->count();
    }
}
