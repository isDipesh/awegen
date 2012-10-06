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

      <div class="control-group">
        <?php echo "<?php echo \$form->labelEx(\$model,'{$column->name}',array('class'=>'control-label')) ; ?>\n"; ?>
        <div class="controls">
          <?php echo "<?php " . $this->generateField($column, $this->modelClass) . "; ?>\n"; ?>
          <div class="help-inline"><?php echo "<?php echo \$form->error(\$model,'{$column->name}'); ?>\n"; ?></div>
        </div>
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

  <div class="form-actions">
    <?php echo "<?php
        echo CHtml::submitButton(Yii::t('app', 'Save'),array('class'=>'btn btn-success'));
        echo '&nbsp;';
        echo CHtml::Button(Yii::t('app', 'Cancel'), array(
			                                                'submit' => 'javascript:history.go(-1)',
			                                                'class'  => 'btn btn-inverse'
			                                                )
			                                              );
        \$this->endWidget(); ?>\n"; ?>
  </div>
</div> <!-- form -->