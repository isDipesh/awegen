<div class="wide form">

<?php echo "<?php \$form = \$this->beginWidget('CActiveForm', array(
	'action' => Yii::app()->createUrl(\$this->route),
	'method' => 'get',
)); ?>\n"; ?>

<?php foreach($this->tableSchema->columns as $column): ?>
<?php
	if (in_array(strtolower($column->name), $this->passwordFields))
		continue;
?>
  <div class="control-group">
    <?php echo "<?php echo \$form->labelEx(\$model,'{$column->name}',array('class'=>'control-label')) ; ?>\n"; ?>
    <div class="controls">
      <?php echo "<?php " . $this->generateField($column,$this->modelClass, true)."; ?>\n"; ?>
    </div>
  </div>

<?php endforeach; ?>
	<div class="row buttons">
		<?php echo "<?php echo CHtml::submitButton(Yii::t('app', 'Search')); ?>\n"; ?>
	</div>

<?php echo "<?php \$this->endWidget(); ?>\n"; ?>

</div><!-- search-form -->