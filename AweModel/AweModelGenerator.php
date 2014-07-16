<?php

require_once(__DIR__."/../AweModel/AweModelCode.php");
class AweModelGenerator extends CCodeGenerator {

    public $codeModel = 'AweModelCode';

    protected function getTables() {
        $tables = Yii::app()->db->schema->tableNames;
        $tables[] = '*';
        return $tables;
    }

}