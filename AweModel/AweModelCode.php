<?php

Yii::import('system.gii.generators.model.ModelCode');

class AweModelCode extends ModelCode {

    /**
     * @var string The (base) model base class name.
     */
    public $baseClass = 'CActiveRecord';

    /**
     * @var string The path of the base model.
     */
    public $baseModelPath;

    /**
     * @var string The base model class name.
     */
    public $baseModelClass;
    public $tables;
    public $booleanTypes = array('tinyint(1)', 'boolean', 'bool');
    public $emailFields = array('email', 'e-mail', 'email_address', 'e-mail_address', 'emailaddress', 'e-mailaddress');
    public $urlFields = array('url', 'link', 'uri', 'homepage', 'webpage', 'website', 'profile_url', 'profile_link');
    public $create_time = array('create_time', 'createtime', 'created_at', 'createdat', 'created_time', 'createdtime');
    public $update_time = array('changed', 'changed_at', 'updatetime', 'modified_at', 'updated_at', 'update_time', 'timestamp', 'updatedat');
    public $time_fields;

    public function init() {
        $this->time_fields = array_merge($this->create_time, $this->update_time);
        //parent::init();
    }

    public function prepare() {
        parent::prepare();

        $templatePath = $this->templatePath;

        if (($pos = strrpos($this->tableName, '.')) !== false) {
            $schema = substr($this->tableName, 0, $pos);
            $tableName = substr($this->tableName, $pos + 1);
        } else {
            $schema = '';
            $tableName = $this->tableName;
        }
        if ($tableName[strlen($tableName) - 1] === '*') {
            $this->tables = Yii::app()->db->schema->getTables($schema);
            if ($this->tablePrefix != '') {
                foreach ($this->tables as $i => $table) {
                    if (strpos($table->name, $this->tablePrefix) !== 0)
                        unset($this->tables[$i]);
                }
            }
        }
        else
            $this->tables = array($this->getTableSchema($this->tableName));

        $this->relations = $this->generateRelations();
            $this->files = array();

        $this->files = array();
        foreach ($this->tables as $table) {

            foreach ($table->columns as $key => $column)
                if (in_array($column->dbType, array('timestamp')))
                    unset($table->columns[$key]);


            $tableName = $this->removePrefix($table->name);
            $className = $this->generateClassName($table->name);

            $this->baseModelPath = $this->modelPath . '._base';
            $this->baseModelClass = 'Base' . $className;

            $params = array(
                'tableName' => $schema === '' ? $tableName : $schema . '.' . $tableName,
                'modelClass' => $className,
                'columns' => $table->columns,
                'labels' => $this->generateLabels($table),
                'rules' => $this->generateRules($table),
                'relations' => isset($this->relations[$className]) ? $this->relations[$className] : array(),
            );

            $this->files[] = new CCodeFile(
                            Yii::getPathOfAlias($this->modelPath . '.' . $className) . '.php',
                            $this->render($templatePath . DIRECTORY_SEPARATOR . 'model.php', $params)
            );

            $this->files[] = new CCodeFile(
                            Yii::getPathOfAlias($this->baseModelPath . '.' . $this->baseModelClass) . '.php',
                            $this->render($templatePath . DIRECTORY_SEPARATOR . '_base' . DIRECTORY_SEPARATOR . 'basemodel.php', $params)
            );
        }
    }

    public function requiredTemplates() {
        return array(
            'model.php',
            '_base' . DIRECTORY_SEPARATOR . 'basemodel.php',
        );
    }

    public function getBehaviors($columns) {
        $behaviors = 'return array(';
        if (count($this->relations) > 0)
            $behaviors .= "'CSaveRelationsBehavior', array(
                'class' => 'CSaveRelationsBehavior'),";

        foreach ($columns as $column) {
            if (in_array($column->name, $this->time_fields)) {
                $behaviors .= sprintf("\n\t\t'CTimestampBehavior' => array(
                'class' => 'zii.behaviors.CTimestampBehavior',
                'createAttribute' => %s,
                'updateAttribute' => %s,
                \t),\n", $this->getCreatetimeAttribute($columns), $this->getUpdatetimeAttribute($columns));
                break; // once a column is found, we are done
            }
        }

        $behaviors .= "\n);\n";
        return $behaviors;
    }

    public function generateRules($table) {
        $rules = array();
        $required = array();
        $null = array();
        $integers = array();
        $numerical = array();
        $length = array();
        $safe = array();
        $email = array();
        $url = array();
        foreach ($table->columns as $column) {
            if ($column->autoIncrement && $table->sequenceName !== null)
                continue;
            //find timestamp fields
            $t = in_array($column->name, $this->time_fields);
            //find if boolean types
            $b = in_array($column->dbType, $this->booleanTypes);
            $r = !$column->allowNull && $column->defaultValue === null;
            //null and timestamp fields are not required, also boolean fields need not be mentioned as required
            if ($r && !$t & !$b)
                $required[] = $column->name;
            //null fields
            if (!$r)
                $null[] = $column->name;
            if ($column->type === 'integer')
                $integers[] = $column->name;
            else if ($column->type === 'double')
                $numerical[] = $column->name;
            else if ($column->type === 'string' && $column->size > 0) {
                $length[$column->size][] = $column->name;
            } else if (!$column->isPrimaryKey && !$r) {
                $safe[] = $column->name;
            }
            if (in_array($column->name, $this->emailFields)) {
                $email[] = $column->name;
            }
            if (in_array($column->name, $this->urlFields)) {
                $url[] = $column->name;
            }
        }
        if ($required !== array())
            $rules[] = "array('" . implode(', ', $required) . "', 'required')";
        if ($null !== array())
            $rules[] = "array('" . implode(', ', $null) . "', 'default', 'setOnEmpty' => true, 'value' => null)";
        if ($integers !== array())
            $rules[] = "array('" . implode(', ', $integers) . "', 'numerical', 'integerOnly' => true)";
        if ($numerical !== array())
            $rules[] = "array('" . implode(', ', $numerical) . "', 'numerical')";
        if ($email !== array())
            $rules[] = "array('" . implode(', ', $email) . "', 'email')";
        if ($url !== array())
            $rules[] = "array('" . implode(', ', $url) . "', 'url', 'defaultScheme' => 'http')";
        if ($length !== array()) {
            foreach ($length as $len => $cols)
                $rules[] = "array('" . implode(', ', $cols) . "', 'length', 'max' => $len)";
        }
        if ($safe !== array())
            $rules[] = "array('" . implode(', ', $safe) . "', 'safe')";
        return $rules;
    }

    function getCreatetimeAttribute($columns) {
        foreach ($this->create_time as $try)
            foreach ($columns as $column)
                if ($try == $column->name)
                    return sprintf("'%s'", $column->name);
        return 'null';
    }

    function getUpdatetimeAttribute($columns) {
        foreach ($this->update_time as $try)
            foreach ($columns as $column)
                if ($try == $column->name)
                    return sprintf("'%s'", $column->name);
        return 'null';
    }

}
