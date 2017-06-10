<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use kartik\grid\GridView;
use miloschuman\highcharts\Highcharts;

/* @var $this yii\web\View */
/* @var $camera common\models\Camera */

$twilioClienJs = <<<SCRIPT
       
SCRIPT;
$twilioFunctionsJs = <<<SCRIPT
     
        
/**
 * Twilio Client configuration for the browser-calls-django
 * example application.
 */

// Store some selectors for elements we'll reuse
var callStatus = $("#call-status");
var answerButton = $(".answer-button");
var hangUpButton = $(".hangup-button");
var callCustomerButtons = $(".call-customer-button");
        
/* Helper function to update the call status bar */
function updateCallStatus(status) {
    callStatus.text(status);
}

/* Get a Twilio Client token with an AJAX request */
$(document).ready(function() {
    $.post("/call/token", {forPage: window.location.pathname}, function(data) {
        // Set up the Twilio Client Device with the token
        Twilio.Device.setup(data.token);
    });
});

/* Callback to let us know Twilio Client is ready */
Twilio.Device.ready(function (device) {
    updateCallStatus("Ready");
});

/* Report any errors to the call status display */
Twilio.Device.error(function (error) {
    updateCallStatus("ERROR: " + error.message);
});

/* Callback to determine if "support_agent" is available or not */
Twilio.Device.presence(function(presenceEvent) {
    if (presenceEvent.from === 'support_agent') {
        
    }
});

/* Callback for when Twilio Client initiates a new connection */
Twilio.Device.connect(function (connection) {
    // Enable the hang up button and disable the call buttons
    hangUpButton.prop("disabled", false);
    callCustomerButtons.prop("disabled", true);
    answerButton.prop("disabled", true);

    // If phoneNumber is part of the connection, this is a call from a
    // support agent to a customer's phone
    if ("phoneNumber" in connection.message) {
        updateCallStatus("In call with " + connection.message.phoneNumber);
    } else {
        // This is a call from a website user to a support agent
        updateCallStatus("In call with support");
    }
});

/* Callback for when a call ends */
Twilio.Device.disconnect(function(connection) {
    // Disable the hangup button and enable the call buttons
    hangUpButton.prop("disabled", true);
    callCustomerButtons.prop("disabled", false);
    
    updateCallStatus("Ready");
});

/* Callback for when Twilio Client receives a new incoming call */
Twilio.Device.incoming(function(connection) {
    updateCallStatus("Incoming support call");

    // Set a callback to be executed when the connection is accepted
    connection.accept(function() {
        updateCallStatus("In call with customer");
    });

    // Set a callback on the answer button and enable it
    answerButton.click(function() {
        connection.accept();
    });
    answerButton.prop("disabled", false);
});

/* Call a customer from a support ticket */
function callCustomer(phoneNumber) {
    updateCallStatus("Calling " + phoneNumber + "...");

    var params = {"phoneNumber": phoneNumber};
    Twilio.Device.connect(params);
}

/* Call the support_agent from the home page */
function callSupport() {
    updateCallStatus("Calling support...");

    // Our backend will assume that no params means a call to support_agent
    Twilio.Device.connect();
}

/* End a call */
function hangUp() {
    Twilio.Device.disconnectAll();
}   
        
SCRIPT;
$this->registerJs($twilioClienJs, \yii\web\View::POS_READY);
$this->registerJs($twilioFunctionsJs, \yii\web\View::POS_END);
$this->registerJsFile('//static.twilio.com/libs/twiliojs/1.2/twilio.min.js');
?>

<?php if($camera && $camera->property->phone_police): ?>

<div class="box box-danger client-controls">
    <div class="box-heading">
        <h3 class="box-title text-center">Make a call</h3>
    </div>
    <div class="box-body">
        
        <div class="well well-sm" id="call-status">
            Connecting to the server...
        </div>

        <button onclick="callCustomer('<?= $camera->property->phone_police ?>')" type="button" class="btn btn-primary btn-lg call-customer-button">
            <span class="glyphicon glyphicon-earphone" aria-hidden="true"></span>
            Call emergency
        </button>
        <button class="btn btn-lg btn-success answer-button hidden" disabled>Answer call</button>
        <button class="btn btn-lg btn-danger hangup-button" disabled onclick="hangUp()">
            <i class="fa fa-stop"></i>
            Hang up
        </button>
    </div>
</div>

<?php else: ?>
    
<div class="box box-danger client-controls">
    <div class="box-heading">
        <h3 class="box-title text-center">Make a call</h3>
    </div>
    <div class="box-body">
       There is no police number specified for this property.
    </div>
</div>

<?php endif; ?>
