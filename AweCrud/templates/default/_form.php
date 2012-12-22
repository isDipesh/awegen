<div class="form">
    <p class="note">
        <?php echo "<?php echo Yii::t('app','Fields with');?> <span class=\"required\">*</span> <?php echo Yii::t('app','are required');?>"; ?>.
    </p>

    <?php echo '<?php'; ?>

    $form=$this->beginWidget('CActiveForm', array(
    'id'=>'<?php echo $this->class2id($this->modelClass); ?>-form',
    'enableAjaxValidation'=><?php echo $this->validation == 1 || $this->validation == 3 ? 'true' : 'false'; ?>,
    'enableClientValidation'=><?php echo $this->validation == 2 || $this->validation == 3 ? 'true' : 'false'; ?>,
    ));

    echo $form->errorSummary($model);
    ?>
    <?php
    foreach ($this->tableSchema->columns as $column) {
        //continue if it is an auto-increment field or if it's a timestamp kinda' stuff
        if ($column->autoIncrement || in_array($column->name, array_merge($this->create_time, $this->update_time)))
            continue;

        //skip many to many relations, they are rendered below, this allows handling of nm relationships
        foreach ($this->getRelations() as $relation) {
            if ($relation[2] == $column->name && $relation[0] == 'CManyManyRelation')
                continue 2;
        }
        ?>

        <div class="row">
            <?php echo "<?php echo " . $this->generateActiveLabel($this->modelClass, $column) . "; ?>\n"; ?>
            <?php echo "<?php " . $this->generateField($column, $this->modelClass) . "; ?>\n"; ?>
            <?php echo "<?php echo \$form->error(\$model,'{$column->name}'); ?>\n"; ?>
        </div>
        <?php
    }

    foreach ($this->getRelations() as $relatedModelClass => $relation) {
        if ($relation[0] == 'CManyManyRelation') {
            echo "<div class=\"row nm_row\">\n";
            echo $this->getNMField($relation, $relatedModelClass, $this->modelClass);
            echo "</div>\n\n";
        }
    }
    ?>
    <?php echo "<div class=\"row buttons\">
        <?php
        echo CHtml::submitButton(Yii::t('app', 'Save'));
echo CHtml::Button(Yii::t('app', 'Cancel'), array(
            'submit' => 'javascript:history.go(-1)'));
        ?>
    </div>
    <?php
\$this->endWidget(); ?>\n"; ?>
</div> <!-- form -->