<?php

/* Register all required HTML and JS to implement WS LogStream */
/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

if (Yii::$app->user->isGuest) {
    $userId = 0;
} else {
    $userId = Yii::$app->user->id;
}

$wsDomain = Yii::$app->params['WS_DOMAIN'];
$wsPORT = Yii::$app->params['WS_PORT'];
$initJs = <<<SCRIPT
        var conn;   //  main WS connection object
        ab.connect('ws://{$wsDomain}:{$wsPORT}',
        function(session) {
                session.subscribe('user_{$userId}_notification', function(topic, message) {
                    if(typeof(message) === "string")
                        data = JSON.parse(message);
                    else
                        data = message;
                   $.notify({
                        icon: 'glyphicon glyphicon-warning-sign',
                        title: data.notificationTitle + "<br>",
                        message: data.notificationMessage,
                    },{type: data.notificationType, placement: {
			from: "bottom",
			align: "right"
                        }
                    });
                });
                $('#ws-connection-status').html('<i class="fa fa-circle text-success"></i> Online');
                window.conn = session;
            },
            function() {
                $('#ws-connection-status').html('<i class="fa fa-circle text-danger"></i> Offline');
            },
            {'skipSubprotocolCheck': true}
        );
SCRIPT;

//  Start connection only for ath users
if ($userId > 0) {
    $this->registerJs($initJs, yii\web\View::POS_HEAD);
}
