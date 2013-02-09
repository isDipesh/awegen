<div class="view">

    <?php
    $possibleIdentifiers = array('name', 'title', 'slug');
    $identificationColumn = $this->getIdentificationColumn();
    if (!in_array($identificationColumn, $possibleIdentifiers)) {
        echo "<h2><?php echo CHtml::encode(\$data->getAttributeLabel('{$identificationColumn}')); ?>:</h2>\n";
    }
    echo "<h2><?php echo CHtml::link(CHtml::encode(\$data->{$identificationColumn}), array('view', '".Awecms::getPrimaryKeyColumn($this)."' => \$data->".Awecms::getPrimaryKeyColumn($this).")); ?></h2>\n";
    foreach ($this->tableSchema->columns as $column) {
        if ($column->name !== $identificationColumn && !$column->isPrimaryKey && !in_array(strtolower($column->name), $this->passwordFields)) {
            
            $columnName = $column->name;
            if ($column->isForeignKey) {
                $relations = $this->getRelations();
                foreach ($relations as $relationName => $relation){
                if($relation[2]==$columnName){
                $relatedModel = CActiveRecord::model($relation[1]);   
                $columnName = $relationName.'->'.  AweCrudCode::getIdentificationColumnFromTableSchema ($relatedModel->tableSchema);
                }
                }
            }
            
            if (!in_array($column->dbType,$this->booleanTypes))
                    echo "
    <?php
    if (!empty(\$data->{$columnName})) {
        ?>";
            echo "
    <div class=\"field\">
            <div class=\"field_name\">
                <b><?php echo CHtml::encode(\$data->getAttributeLabel('{$column->name}')); ?>:</b>
            </div>
<div class=\"field_value\">\n";
            if (in_array($column->dbType, $this->dateTypes)) {
                /*
                echo "\techo Yii::app()->getDateFormatter()->formatDateTime(\$data->{$columnName}, 'medium', 'medium'); ?>\n\t<br />\n\n";
                */
                echo "                <?php
                echo date('D, d M y H:i:s', strtotime(\$data->" . $columnName . "));
                ?>

        </div>
        </div>\n";
            } else if (in_array($column->dbType, $this->booleanTypes)) {
                echo "
                <?php
                echo CHtml::encode(\$data->{$columnName} == 1 ? 'True' : 'False');
                ?>

            </div>
        </div>";
            } else if (in_array(strtolower($columnName), $this->emailFields)) {
                echo "
                <?php
                echo CHtml::mailto(\$data->{$columnName});
                ?>

            </div>
        </div>";
            } else if (in_array($column->dbType, array('longtext'))) {
                echo "
                <?php
                echo nl2br(\$data->{$columnName});
                ?>

            </div>
        </div>";
            } else if (in_array(strtolower($columnName), $this->imageFields)) {
                
                /*
                echo "                <a href=\"\<?php echo \$data->{$columnName} ?>\" target=\"_blank\" >"; 
                */
                echo "<img alt=\"<?php echo \$data->{$identificationColumn} ?>\" title=\"<?php echo \$data->{$identificationColumn} ?>\" src=\"<?php echo \$data->{$columnName} ?>\" />";
                /*
                echo 'echo "</a>";
                 */
                echo "</div>";
                echo "</div>";
            } else if (in_array(strtolower($columnName), $this->urlFields)) {
                echo "
                <?php
                echo Awecms::formatUrl(\$data->{$columnName},true);
                ?>

            </div>
        </div>";
            }
            else {
                echo "
                <?php
                echo CHtml::encode(\$data->{$columnName});
                ?>

            </div>
        </div>";
            }

            if (!in_array($column->dbType,$this->booleanTypes)) echo "
        <?php
    }
    ?>";
        }
    }
    ?>

</div>