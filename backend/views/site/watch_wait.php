<?php

use yii\helpers\Html;
use common\models\User;

/* @var $this yii\web\View */

$this->title = 'There is no active alert for you now';

$this->registerAssetBundle(yii\jui\JuiAsset::className());

$curUser = Yii::$app->user->getIdentity();

$multiscreenMode = (\Yii::$app->session->get(User::SETTINGS_MULTISCREEN, false) ? 1 : 0);
//  Agent only alert notification script
$alertJs = <<<SCRIPT
   var checkActive = false; 
   var alertAssigned = false;   //  Don't check any more after alert assigned
   var alertAccepted = false;
   var multiscreenMode = {$multiscreenMode};
   var lastNotificationTime = (new Date()).getTime() - 30000;     
   
   var showLayout = function(layoutId){
       switch(layoutId){
            case "activation-info":
                $("#wait-info").addClass("hidden");
                $("#activation-info").removeClass("hidden");
                break;
            case "wait-info":
                $("#wait-info").removeClass("hidden");
                $("#activation-info").addClass("hidden");
                break;
            default:
                $("#wait-info").removeClass("hidden");
                $("#activation-info").addClass("hidden");
       }
   }    
        
   var markReadyToWork = function(){
        checkActive = false; 
        alertAssigned = false; 
        alertAccepted = false;
        showLayout("wait-info");
        var message = {'userId': '{$curUser->id}'};
        window.conn.call('readyToWork', message );
   }
   $(".mark-ready-to-work").click(markReadyToWork);
        
   //   Alert options
   var skipAlert = function(alertId, callback){
       alertAccepted = false;
       $.get("/alert/skip?alertId=" + alertId, function(data){
            //console.log(data.result);
            if(callback && $.isFunction(callback))
                callback();
       })
   };
   var acceptAlert = function(alertId){
        alertAccepted = true;
        window.location.href = '/watch?alert_id=' + alertId;
   }
   var checkAlerts = function(){
    if(!checkActive && !alertAssigned){
       checkActive = true;
       $.get("/alert/check", function(data){
           if(data.result){
               alertAssigned = true;
               var audio = new Audio('/sound/alert_notification.mp3');
               audio.play();
               var popupTemplate =
                '<div class="modal fade">' +
                '  <div class="modal-dialog">' +
                '    <div class="modal-content">' +
                '      <div class="modal-header">' +
                '        <h4 class="modal-title">Camera alert notification</h4>' +
                '      </div>' +
                '      <div class="modal-body">' +
                '           <video id="test_video" controls autoplay style="width: 100%">' +
                '               <source src="rtsp://127.0.0.1:8554/test" type="application/x-rtsp">' +
                '           </video>' +
                '      </div>' +
                '      <div class="modal-footer">' +
                '        <button type="button" onclick="acceptAlert(' + data.alert_id + ')" class="btn btn-primary" data-dismiss="modal">Accept</button>' +
                '        <button type="button" onclick="skipAlert(' + data.alert_id + ')" class="btn btn-link" data-dismiss="modal">Skip</button>' +
                '      </div>' +
                '    </div>' +
                '  </div>' +
                '</div>';
                $(popupTemplate).modal().on('shown.bs.modal', function (e) {
                    let p = new WSPlayer('test_video', {
                        modules: [
                            {
                                client: RTSPClient,
                                transport: wsTransport
                            }
                        ]
                    });
                }).on('hidden.bs.modal',function(e){
                    if(alertAssigned && !alertAccepted)
                        showLayout("activation-info");
                    else
                        showLayout("wait-info");
                });
           }
        else{
            var currentLocation = window.location.href;
            if(!multiscreenMode && data.alert_id && ((new Date()).getTime() - lastNotificationTime ) >= 300 && currentLocation.indexOf("watch?alert_id=") === -1){
                $.notify({
                    icon: 'fa fa-warning',
                    title: "You've got assigned alert<br>",
                    message: "You've got assigned alert and will not recieve new one until close current alert. <a href='/watch?alert_id=" + data.alert_id + "'>Go to the Camera panel</a>",
               },{
                   delay: 15000,
                   placement: {
			from: "bottom",
			align: "right"
                    }, 
                   type: 'info'
                   });
               lastNotificationTime = (new Date()).getTime();
            }
        }
       }).always(function(){checkActive = false;});
     }
   }
   //   Move check to the WS
   checkAlerts();
   setInterval(checkAlerts, 2000);
        
   //   Add real time alert notification
   var subscribeWaitWath = function(){
       if(window.conn === undefined)
            return;
       window.clearTimeout(subscribeTimeoutHandler);
       
       window.conn.subscribe('user_{$curUser->id}_watch_wait', function(topic, message) {
                    if(typeof(message) === "string")
                        data = JSON.parse(message);
                    else
                        data = message;
                    if(data.result){
                         alertAssigned = true;
                         var audio = new Audio('/sound/alert_notification.mp3');
                          audio.play();
                          var popupTemplate =
                            '<div class="modal fade">' +
                            '  <div class="modal-dialog">' +
                            '    <div class="modal-content">' +
                            '      <div class="modal-header">' +
                            '        <h4 class="modal-title">Camera alert notification</h4>' +
                            '      </div>' +
                            '      <div class="modal-body">' +
                            '           <video id="test_video" controls autoplay style="width: 100%">' +
                            '               <source src="rtsp://admin:DanielPass5@166.255.152.193:8888/cam/realmonitor?channel=1&subtype=1" type="application/x-rtsp">' +
                            '           </video>' +
                            '      </div>' +
                            '      <div class="modal-footer">' +
                            '        <button type="button" onclick="acceptAlert(' + data.alert_id + ')" class="btn btn-primary" data-dismiss="modal">Accept</button>' +
                            '        <button type="button" onclick="skipAlert(' + data.alert_id + ')" class="btn btn-link" data-dismiss="modal">Skip</button>' +
                            '      </div>' +
                            '    </div>' +
                            '  </div>' +
                            '</div>';
                        $(popupTemplate).modal().on('shown.bs.modal', function (e) {
                            let p = new WSPlayer('test_video', {
                                modules: [
                                    {
                                        client: RTSPClient,
                                        transport: wsTransport
                                    }
                                ]
                            });
                        }).on('hidden.bs.modal',function(e){
                            if(alertAssigned && !alertAccepted)
                                showLayout("activation-info");
                            else
                                showLayout("wait-info");
                        });
                }
                else{
                    var currentLocation = window.location.href;
                    if(!multiscreenMode && data.alert_id && ((new Date()).getTime() - lastNotificationTime ) >= 300 && currentLocation.indexOf("watch?alert_id=") === -1){
                        $.notify({
                            icon: 'fa fa-warning',
                            title: "You've got assigned alert<br>",
                            message: "You've got assigned alert and will not recieve new one until close current alert. <a href='/watch?alert_id=" + data.alert_id + "'>Go to the Camera panel</a>",
                        },{
                            delay: 15000,
                            placement: {
                                from: "bottom",
                            align: "right"
                        }, 
                            type: 'info'
                        });
                        lastNotificationTime = (new Date()).getTime();
                    }
                }
            });
   };
   var subscribeTimeoutHandler = setTimeout(subscribeWaitWath, 1000);
   
   $(".multiscreen-btn").click(function(){
        var link = $(this);
        link.find('i').show();
        $.post("/user/settings", {action: "multiscreen"}, function(data){
            if(data.result){
                multiscreenMode = data.value;
                if(!data.value){
                    $('.multiscreen-disabled-info').show('slide', {direction: 'right'});
                    $('.multiscreen-enabled-info').hide('slide');
                }
                else{
                    $('.multiscreen-disabled-info').hide('slide');
                    $('.multiscreen-enabled-info').show('slide', {direction: 'right'});
                }
            
                $.notify({
                    icon: 'fa fa-warning',
                    title: "Settings change result<br>",
                    message: data.message,
               },{
                   delay: 5000,
                   placement: {
			from: "bottom",
			align: "right"
                    }, 
                   type: 'success'
                   });
            }
            else{
                $.notify({
                    icon: 'fa fa-warning',
                    title: "Settings change result<br>",
                    message: data.message,
               },{
                   delay: 5000,
                   placement: {
			from: "bottom",
			align: "right"
                    }, 
                   type: 'danger'
                   });
            }
        }).always(function(){
            link.find('i').hide();
        });
   });     
SCRIPT;
$this->registerJsFile('/js/streamedian.js');

if ($curUser->role === \common\models\User::ROLE_AGENT) {
    //  Check only if agent doesn't have active alerts 
    //if (common\models\Alert::getActive()->andWhere(['user_id' => $curUser->id])->count() === 0)
    $this->registerJs($alertJs, yii\web\View::POS_END);
}
?>
<?php if ($curUser->role === \common\models\User::ROLE_AGENT): ?>
    <section class="content">

        <div class="watch-wait-page" id="wait-info">
            <div class="text-center">
                <h3>Wait here..</h3>
                <p>
                    Please stay online and you'll hear an alert sound, after that you'll see property dashboard. 
                </p>
                <p>
                    Checking for new alerts <i class="fa fa-spinner fa-pulse fa-fw"></i>
                </p>

                <br>

                <div class="small multiscreen-disabled-info" <?= $multiscreenMode ? 'style="display: none;"' : '' ?> >
                    Note: If you have open alerts you'll not recieve new ones. <a href="#" class="multiscreen-btn">Enable multiple property watch <i class="fa fa-spinner fa-pulse fa-fw" style="display:none;"></i></a> to work with multiple screens.
                </div>

                <div class="small multiscreen-enabled-info" <?= $multiscreenMode ? '' : 'style="display: none;"' ?> >
                    Note: You will recieve alerts in each tab/screen. <a href="#" class="multiscreen-btn">Disable multiple property watch <i class="fa fa-spinner fa-pulse fa-fw" style="display:none;"></i></a> to work with single screen.
                </div>

            </div>
        </div>

        <div class="hidden text-center" id="activation-info">
            <h3>Attention</h3>
            <p>
                It seems that you haven't accepted last alert we sent you. You will not longer recieve an alert until confirm that you are ready to work. 
            </p>
            <p>
                <button class="btn btn-warning mark-ready-to-work"><i class="fa fa-thumbs-up"></i> I'm ready to work</button>
            </p>
        </div>

    </section>
<?php else: ?>

    <section class="content">

        <div class="watch-wait-page" id="wait-info">
            <div class="text-center"><h3>You are an admin.. You are not to receive an alert</h3></div>
        </div>

    </section>

<?php endif; ?>
