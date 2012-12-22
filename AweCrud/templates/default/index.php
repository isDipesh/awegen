<?php
$label = $this->pluralize($this->class2name($this->modelClass));

echo "<?php\n";
echo "\$this->breadcrumbs = array(
    Yii::t('app', '$label')
);"
?>

if(!isset($this->menu) || $this->menu === array())
$this->menu=array(
        array('label'=>Yii::t('app', 'List')),
    array('label'=>Yii::t('app', 'Create'), 'url'=>array('create')),
    array('label'=>Yii::t('app', 'Manage'), 'url'=>array('manage')),
);
?>

<h1><?php echo "<?php echo Yii::t('app', '".$label."'); ?>" ?></h1>

<?php echo "<?php"; ?> $this->widget('zii.widgets.CListView', array(
    'dataProvider'=>$dataProvider,
    'itemView'=>'_view',
)); ?>
