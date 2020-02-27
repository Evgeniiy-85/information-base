<?php
use yii\widgets\ActiveForm;
use yii\helpers\Html;

$this->title = 'Testing task';
?>
<div class="site-index">

    <div class="jumbotron">
        <h1>Поиск человека по ИНН</h1>
    </div>

    <div class="body-content">
        <div class="form-search">
            <?$form = ActiveForm::begin([
                'id' => 'form-search',
                'action' => "/",
                'method' => "GET"
            ]);?>

            <div class="search-input-container">
                <?php print Html::input('text', 'itn', urldecode( Yii::$app->request->get('itn')), [
                    'class' => 'input-search',
                    'placeholder' => 'Введите ИНН для поиска'
                ]) ?>

                <button title="Найти" class="btn-search">Поиск</button>
            </div>
            <?php ActiveForm::end() ?>
        </div>

        <?if(!empty($model)):?>
            <div class="form-result">
                <?if ($model->hasMessages()):
                    $model->showMessages();
                else:?>
                    <h2>Результат поиска:</h2>

                    <?$form = ActiveForm::begin([
                        'id' => 'form-result',
                        'action' => "/",
                        'method' => "POST"
                    ]);?>

                    <?foreach ($fields as $key => $field):
                        $value = $model->$field;

                        if ($field == 'ppl_birth_date') {
                            $value = date('d.m.Y', $value);
                        }?>

                        <span><?=$key?>: </span>
                        <span><?=$value?></span><br>

                        <?=Html::activeInput('hidden', $model, $field, [
                        'value' => $value
                    ])?>
                    <?endforeach;?>

                    <?if(!$model->ppl_id):?>
                        <button title="Сохранить результат" class="btn-save">Сохранить</button>
                    <?endif?>
                    <?php ActiveForm::end() ?>
                <?endif?>
            </div>
        <?endif?>
    </div>
</div>