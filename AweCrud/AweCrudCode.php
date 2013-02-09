<?php

Yii::import('system.gii.generators.crud.CrudCode');

class AweCrudCode extends CrudCode {

    public $authtype = 'no_access_control';
    public $validation = 2;
    public $baseControllerClass = 'Controller';
    public $identificationColumn = '';
    public $isJToggleColumnEnabled = true;
    public $dateTypes = array('datetime', 'date', 'time', 'timestamp');
    public $booleanTypes = array('tinyint(1)', 'boolean', 'bool', 'bit');
    public $emailFields = array('email', 'e-mail', 'email_address', 'e-mail_address', 'emailaddress', 'e-mailaddress');
    public $imageFields = array('image', 'picture', 'photo', 'pic', 'profile_pic', 'profile_picture', 'avatar', 'profilepic', 'profilepicture');
    public $urlFields = array('url', 'link', 'uri', 'homepage', 'webpage', 'website', 'profile_url', 'profile_link');
    public $passwordFields = array('password', 'passwd', 'psswrd', 'pass', 'passcode');
    public $create_time = array('create_time', 'createtime', 'created_at', 'createdat', 'created_time', 'createdtime');
    public $update_time = array('changed', 'changed_at', 'updatetime', 'modified_at', 'updated_at', 'update_time', 'timestamp', 'updatedat');

    public function rules() {
        return array_merge(parent::rules(), array(
                    array('identificationColumn, isJToggleColumnEnabled, validation, authtype', 'safe'),
                ));
    }

    public function attributeLabels() {
        return array_merge(parent::attributeLabels(), array(
                    'authtype' => 'Authentication type',
                ));
    }

    //used by getIdentificationColumn as callback for array_map
    private static function getName($column) {
        return $column->name;
    }

    public static function getIdentificationColumnFromTableSchema($tableSchema) {
        $possibleIdentifiers = array('name', 'title', 'slug');

        $columns_name = array_map('self::getName', $tableSchema->columns);
        foreach ($possibleIdentifiers as $possibleIdentifier) {
            if (in_array($possibleIdentifier, $columns_name))
                return $possibleIdentifier;
        }

        foreach ($columns_name as $column_name) {
            if (preg_match('/.*name.*/', $column_name, $matches)) {
                return $column_name;
            }
        }

        foreach ($tableSchema->columns as $column) {
            if (!$column->isForeignKey
                    && !$column->isPrimaryKey
                    && $column->type != 'INT'
                    && $column->type != 'INTEGER'
                    && $column->type != 'BOOLEAN') {
                return $column->name;
            }
        }

        if (is_array($pk = $tableSchema->primaryKey))
            $pk = $pk[0];
        //every table must have a PK
        return $pk;
    }

    public function getIdentificationColumn() {
        if (!empty($this->identificationColumn))
            return $this->identificationColumn;
        return self::getIdentificationColumnFromTableSchema($this->tableSchema);
    }

    public function getDetailViewAttribute($column) {
        
        if ($column->name == 'id' || in_array($column->name, $this->passwordFields)) { // only admin user can see id and password
            $visible=(Yii::app()->hasModule('user'))?"Yii::app()->getModule('user')->isAdmin()":"Yii::app()->user->id=='admin'";
            return "array(
                        'name'=>'{$column->name}',
                        'visible'=>{$visible}
                    ),";
        }

        if (in_array(strtolower($column->name), $this->imageFields)) {
            return "array(
                        'name'=>'{$column->name}',
                        'type'=>'image'
                    ),";
        }

        if (in_array(strtolower($column->name), $this->emailFields)) {
            return "array(
                        'name'=>'{$column->name}',
                        'type'=>'email'
                    ),";
        }

        if (in_array(strtolower($column->name), $this->urlFields)) {
            return "array(
                        'name'=>'{$column->name}',
                        'type'=>'url'
                    ),";
        }

        $type_conversion = array(
            'longtext' => 'ntext',
            'time' => 'time',
            'boolean' => 'boolean',
            'bool' => 'boolean',
            'tinyint(1)' => 'boolean',
        );

        if (array_key_exists(strtolower($column->dbType), $type_conversion)) {
            return "array(
                        'name'=>'{$column->name}',
                        'type'=>'" . $type_conversion[strtolower($column->dbType)] . "'
                    ),";
        }

        return "'{$column->name}',";
    }

    public function findRelation($modelClass, $column) {
        if (!$column->isForeignKey)
            return null;
        $relations = CActiveRecord::model($modelClass)->relations();
        // Find the relation for this attribute.
        foreach ($relations as $relationName => $relation) {
            // For attributes on this model, relation must be BELONGS_TO.
            if ($relation[0] == CActiveRecord::BELONGS_TO && $relation[2] == $column->name) {
                return array(
                    $relationName, // the relation name
                    $relation[0], // the relation type
                    $relation[2], // the foreign key
                    $relation[1] // the related active record class name
                );
            }
        }
        // None found.
        return null;
    }

    public function getNMField($relation, $relatedModelClass, $modelClass)
    {
        $foreign_pk = Awecms::getPrimaryKeyColumn(CActiveRecord::model($relation[1]));

        $foreign_identificationColumn = self::getIdentificationColumnFromTableSchema(CActiveRecord::model($relation[1])->getTableSchema());
        $friendlyName = ucfirst($relatedModelClass);
        $str = "<label for=\"$relatedModelClass\"><?php echo Yii::t('app', '$friendlyName'); ?></label>\n";
        $str .= "<?php echo CHtml::checkBoxList('{$modelClass}[{$relatedModelClass}]', array_map('Awecms::getPrimaryKey',\$model->{$relatedModelClass}),
            CHtml::listData({$relation[1]}::model()->findAll(),'{$foreign_pk}', '{$foreign_identificationColumn}'),
            array('attributeitem' => '{$foreign_pk}', 'checkAll' => Yii::t('app','Select All'))); ?>";
        return $str;
    }

    public function generateField($column, $modelClass, $search=false) {
        if ($column->isForeignKey) {
            $relation = $this->findRelation($modelClass, $column);
            //get primary key of the foreign model
            $foreign_pk = Awecms::getPrimaryKeyColumn(CActiveRecord::model($relation[3]));
            $foreign_identificationColumn = self::getIdentificationColumnFromTableSchema(CActiveRecord::model($relation[3])->getTableSchema());
            //if the relation name is parent or child and if the relation is with items from same model,
            //don't allow any item to be parent/child of itself

            $prompt = '';
            if ($column->allowNull && $column->defaultValue == NULL) {
                $prompt = ", array('prompt' => 'None')";
            }

            if (($relation[0] == 'parent' || $relation[0] == 'child') && $relation[3] == $modelClass && !$search) {

                $str = "\$allModels = {$relation[3]}::model()->findAll();
                ";
                $str .= 'foreach ($allModels as $key => $aModel) {
                    ';
                $str .= '    if ($aModel->id == $model->id)
                    ';
                $str .= '        unset($allModels[$key]);
                    ';
                $str .= '}
                    ';
                $str .= "echo \$form->dropDownList(\$model, '{$relation[0]}', CHtml::listData(\$allModels, '{$foreign_pk}', '{$foreign_identificationColumn}'){$prompt});\n";
                return $str;
            }
            //requires EActiveRecordRelationBehavior
            return "echo \$form->dropDownList(\$model, '{$relation[0]}', CHtml::listData({$relation[3]}::model()->findAll(),'{$foreign_pk}', '{$foreign_identificationColumn}'){$prompt})";
        } else {

            if (in_array(strtolower($column->dbType), $this->booleanTypes))
                return "echo \$form->checkBox(\$model,'{$column->name}')";
            //if the column name looks like that of an image and if it's a string
            if (in_array(strtolower($column->name), $this->imageFields) && $column->type == 'string') {
                //find maximum length and size
                if (($size = $maxLength = $column->size) > 60)
                    $size = 60;
                //generate the textField
                $string = "echo \$form->textField(\$model,'{$column->name}',array('size'=>$size,'maxlength'=>$maxLength))";
                //also show the image and make it clickable if the field the something
                $string .= ";\nif (!empty(\$model->{$column->name})){ ?> <div class=\"right\"><a href=\"<?php echo \$model->{$column->name} ?>\" target=\"_blank\" title=\"<?php echo Awecms::generateFriendlyName('{$column->name}') ?>\"><img src=\"<?php echo \$model->{$column->name} ?>\"  alt=\"<?php echo Awecms::generateFriendlyName('{$column->name}') ?>\" title=\"<?php echo Awecms::generateFriendlyName('{$column->name}') ?>\"/></a></div><?php }";
                return $string;
            } else if (strtolower($column->dbType) == 'longtext') {
                return "\$this->widget('EMarkitupWidget', array(
                        'model' => \$model,
                        'attribute' => '{$column->name}',
                        ));";
                return "echo \$form->textArea(\$model,'{$column->name}',array('rows'=>6, 'cols'=>50))";
            } else if (stripos($column->dbType, 'text') !== false)
                return "echo \$form->textArea(\$model,'{$column->name}',array('rows'=>6, 'cols'=>50))";
            else if (substr(strtolower($column->dbType), 0, 4) == 'enum') {
                $string = sprintf("echo CHtml::activeDropDownList(\$model, '%s', array(\n", $column->name);

                $enum_values = explode(',', substr($column->dbType, 4, strlen($column->dbType) - 1));

                foreach ($enum_values as $value) {
                    $value = trim($value, "()'");
                    $string .= "\t\t\t'$value' => Yii::t('app', '" . Awecms::generateFriendlyName($value) . "') ,\n";
                }
                $string .= '))';

                return $string;
            } else if (substr(strtolower($column->dbType), 0, 3) == 'set') {
                $string = sprintf("echo CHtml::activeCheckBoxList(\$model, '%s', array(\n", $column->name);
                $set_values = explode(',', substr($column->dbType, 4, strlen($column->dbType) - 1));

                foreach ($set_values as $value) {
                    $value = trim($value, "()'");
                    $string .= "\t\t\t'$value' => Yii::t('app', '" . Awecms::generateFriendlyName($value) . "') ,\n";
                }
                $string .= '))';

                return $string;
            } else if (in_array(strtolower($column->dbType), $this->dateTypes)) {
                $mode = strtolower(($column->dbType == 'timestamp') ? 'datetime' : $column->dbType);
                return ("\$this->widget('CJuiDateTimePicker',
                         array(
                            'model'=>\$model,
                                                        'name'=>'{$modelClass}[{$column->name}]',
                            //'language'=> substr(Yii::app()->language,0,strpos(Yii::app()->language,'_')),
                                                        'language'=> '',
                            'value'=>\$model->{$column->name},
                                                        'mode' => '" . $mode . "',
                            'options'=>array(
                                                                        'showAnim'=>'fold', // 'show' (the default), 'slideDown', 'fadeIn', 'fold'
                                                                        'showButtonPanel'=>true,
                                                                        'changeYear'=>true,
                                                                        'changeMonth'=>true,
                                                                        'dateFormat'=>'yy-mm-dd',
                                                                        ),
                                                    )
                    );
                    ");
            } else {
                if (in_array(strtolower($column->name), $this->passwordFields))
                    $inputField = 'passwordField';
                else
                    $inputField = 'textField';

                if ($column->type !== 'string' || $column->size === null)
                    return "echo \$form->{$inputField}(\$model,'{$column->name}')";
                else {
                    if (($size = $maxLength = $column->size) > 60)
                        $size = 60;
                    return "echo \$form->{$inputField}(\$model,'{$column->name}',array('size'=>$size,'maxlength'=>$maxLength))";
                }
            }
        }
    }

    public function generateGridViewColumn($column) {

        if ($column->isForeignKey) {
            $columnName = $column->name;
            $relations = $this->getRelations();
            $relatedModel = null;
            $relatedModelName=null;
            foreach ($relations as $relationName => $relation) {
                if ($relation[2] == $columnName) {
                    $relatedModel = CActiveRecord::model($relation[1]);
                    $relatedColumnName = $relationName . '->' . AweCrudCode::getIdentificationColumnFromTableSchema($relatedModel->tableSchema);
                    $relatedModelName = $relation[1];
                }
            }

            $filter = '';
            if( $relatedModel )
            {
              $foreign_pk = Awecms::getPrimaryKeyColumn($relatedModel);
              $foreign_identificationColumn = self::getIdentificationColumnFromTableSchema($relatedModel->getTableSchema());
              $relatedModelName = get_class($relatedModel);
              $filter = "CHtml::listData({$relatedModelName}::model()->findAll(),'{$foreign_pk}','{$foreign_identificationColumn}')";
            }

              return "array(
                            'name'   => '{$column->name}',
                      'value'  => 'isset(\$data->{$relatedColumnName})?\$data->{$relatedColumnName}:\"N/A\"',
                      'filter' => $filter,
                )";



        }

        // Boolean or bit.
        if (strtoupper($column->dbType) == 'TINYINT(1)'
                || strtoupper($column->dbType) == 'BIT'
                || strtoupper($column->dbType) == 'BOOL'
                || strtoupper($column->dbType) == 'BOOLEAN') {
            if ($this->isJToggleColumnEnabled) {
                return "array(
                                        'class' => 'JToggleColumn',
                    'name' => '{$column->name}',
                    'filter' => array('0' => Yii::t('app', 'No'), '1' => Yii::t('app', 'Yes')),
                                        'model' => get_class(\$model),
                                        'htmlOptions' => array('style' => 'text-align:center;min-width:60px;')
                    )";
            }else
                return "array(
                    'name' => '{$column->name}',
                    'value' => '(\$data->{$column->name} === 0) ? Yii::t(\\'app\\', \\'No\\') : Yii::t(\\'app\\', \\'Yes\\')',
                    'filter' => array('0' => Yii::t('app', 'No'), '1' => Yii::t('app', 'Yes')),
                    )";
        } else // Common column.
            return "'{$column->name}'";
    }

    public function getRelations() {
        return CActiveRecord::model($this->modelClass)->relations();
    }

    public function resolveController($relation) {
        $model = new $relation[1];
        $reflection = new ReflectionClass($model);
        $module = preg_match("/\/modules\/([a-zA-Z0-9]+)\//", $reflection->getFileName(), $matches);
        $modulePrefix = (isset($matches[$module])) ? "/" . $matches[$module] . "/" : "/";
        $controller = $modulePrefix . strtolower(substr($relation[1], 0, 1)) . substr($relation[1], 1);
        return $controller;
    }

    public function hasBooleanColumns($columns) {
        foreach ($columns as $column)
            if (in_array(strtolower($column->dbType), $this->booleanTypes))
                return true;
        return false;
    }

}