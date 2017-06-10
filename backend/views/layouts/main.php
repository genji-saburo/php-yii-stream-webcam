<?php

use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $content string */

$actionId = Yii::$app->controller->action->id;
$controllerId = Yii::$app->controller->id;
$initJs = <<<SCRIPT
   //   Logging options
   var logWriteActive = false;
   var logUserActivity = function(){
       if(!logWriteActive){
            logWriteActive = true;
            $.post("/log/write",{name: "User online",details: JSON.stringify({controller: "{$controllerId}", action: "{$actionId}"})})
                .always(function(){
                    logWriteActive = false;
                });
       }
   }
   logUserActivity();
   setInterval(logUserActivity, 30000);  
SCRIPT;
$this->registerJs($initJs, yii\web\View::POS_READY);


//  Add WS connection
echo $this->render('_ws-log-stream');


if (class_exists('backend\assets\AppAsset')) {
    backend\assets\AppAsset::register($this);
} else {
    app\assets\AppAsset::register($this);
}

dmstr\web\AdminLteAsset::register($this);

$directoryAsset = Yii::$app->assetManager->getPublishedUrl('@vendor/almasaeed2010/adminlte/dist');
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>"/>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?= Html::csrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?></title>
        <?php $this->head() ?>
    </head>
    <body class="hold-transition skin-blue sidebar-mini">
        <?php $this->beginBody() ?>
        <div class="wrapper">

            <?=
            $this->render(
                    'header', ['directoryAsset' => $directoryAsset]
            )
            ?>

            <?=
            $this->render(
                    'left', ['directoryAsset' => $directoryAsset]
            )
            ?>

            <?=
            $this->render(
                    'content', ['content' => $content, 'directoryAsset' => $directoryAsset]
            )
            ?>

        </div>

        <?php $this->endBody() ?>
    </body>
</html>

<?php
echo $this->render('_verify_vlc');
?>

<?php $this->endPage() ?>