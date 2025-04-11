<?php

class ImportForm extends CFormModel{
    public $file_excel;
    
    public function rules(){
        return array(
            array('file_excel','file','types'=>'xls',
                'allowEmpty'=>false),
        );
    }
    
    public function attributeLabels(){
        return array(
            'file_excel'=>'File',
        );
    }
}
?>