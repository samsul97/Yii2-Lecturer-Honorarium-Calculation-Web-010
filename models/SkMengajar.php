<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "sk_mengajar".
 *
 * @property int $id
 * @property string $nama
 * @property string $file
 */
class SkMengajar extends \yii\db\ActiveRecord
{
    public $file_upload;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'sk_mengajar';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nama'], 'required'],
            [['nama'], 'string', 'max' => 100],
            [['nama'], 'unique'],
            [['file_upload'], 'file', 'extensions'=>'docx, doc, pdf, xls, xlsx'],
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
            'file' => 'File',
        ];
    }
}
