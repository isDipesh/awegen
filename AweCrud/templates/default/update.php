<?php
echo "<?php\n";
$label = $this->pluralize($this->class2name($this->modelClass));
echo "\$this->breadcrumbs = array(
    Yii::t('app', '$label') => array('index'),
    Yii::t('app', \$model->{$this->getIdentificationColumn()}) => array('view','".Awecms::getPrimaryKey($this)."'=>\$model->".Awecms::getPrimaryKey($this)."),
    Yii::t('app', 'Update'),
);";
?>

if(!isset($this->menu) || $this->menu === array())
$this->menu=array(
<<<<<<< HEAD
        array('label'=>Yii::t('app', 'View'), 'url'=>array('view', 'id'=>$model-><?php echo $this->tableSchema->primaryKey; ?>)),
        array('label'=>Yii::t('app', 'Update')),
        array('label'=>Yii::t('app', 'Delete'), 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model-><?php echo $this->tableSchema->primaryKey; ?>),'confirm'=>'Are you sure you want to delete this item?')),
        array('label'=>Yii::t('app', 'List'), 'url'=>array('index')),
        array('label'=>Yii::t('app', 'Create'), 'url'=>array('create')),
        array('label'=>Yii::t('app', 'Manage'), 'url'=>array('manage')),
=======
	array('label'=>Yii::t('app', 'Delete') , 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model-><?php echo Awecms::getPrimaryKey($this); ?>),'confirm'=>'Are you sure you want to delete this item?')),
	//array('label'=>Yii::t('app', 'Create') , 'url'=>array('create')),
	//array('label'=>Yii::t('app', 'Manage') , 'url'=>array('admin')),
>>>>>>> b298667... handle  composite foreign keys
);
?>

<?php 
$pk = "\$model->" . $this->getIdentificationColumn();
printf('<h1> %s %s </h1>',
'<?php echo Yii::t(\'app\', \'Update\');?>',
'<?php echo ' . $pk . '; ?>'); ?>

<?php echo "<?php\n"; ?>
$this->renderPartial('_form', array(
            'model'=>$model));
?>