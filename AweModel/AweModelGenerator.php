<?php

class AweModelGenerator extends CCodeGenerator {

    public $codeModel = 'ext.awegen.AweModel.AweModelCode';

    protected function getTables() {
        $tables = Yii::app()->db->schema->tableNames;
        $tables[] = '*';
        return $tables;
    }

}