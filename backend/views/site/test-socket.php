<?php
/* @var $this yii\web\View */

$this->registerJsFile('http://autobahn.s3.amazonaws.com/js/autobahn.min.js');
$socketJS = <<<SCRIPT
        var conn;
        ab.connect('ws://patroleum.loc:8080',
        function(session) {
                session.subscribe('kittensCategory', function(topic, message) {
                    if(typeof(message) === "string")
                        data = JSON.parse(message);
                    else
                        data = message;
                    $('.console').append("<div>" + data.title + "</div>");
                });
                window.conn = session;
            },
            function() {
                $('.console').append("<div>WebSocket connection closed</div>");
            },
            {'skipSubprotocolCheck': true}
        );
        
        $('#send-btn').click(function(){
            var message = {'category': 'kittensCategory',
                'title': $('#send-input').val(),
                'article': 'Article',
                'when': 1412124};
        
            window.conn.publish('kittensCategory', message, false);
            window.conn.call('kittensCategory', message );
            //conn.send($('#send-input').val());
        });
SCRIPT;
$this->registerJs($socketJS, \yii\web\View::POS_READY);
?>

<input id="send-input" value="">
<button id="send-btn">Chat</button>
<div class="console" style="width: 100%;background-color: #fff;min-width: 100px;"></div>
