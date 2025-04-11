<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "beban_minimal".
 *
 * @property int $id
 * @property string $jumlah
 */
class BebanMinimal extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'm_beban_minimal';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['jumlah', 'nama'], 'required'],
            [['jumlah', 'nama'], 'string', 'max' => 100],
            ['jumlah', 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nama' => 'Nama',
            'jumlah' => 'Beban Minimal',
        ];
    }
    public static function getList()
    {
        return \yii\helpers\ArrayHelper::map(self::find()->all(), 'id', 'jumlah');
    }
}
