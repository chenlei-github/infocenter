{extends 'layouts/home.layout.tpl'}
{block name=stylesheet append}
<style>
.center {
width: auto;
display: table;
margin-left: auto;
margin-right: auto;
}
.text-center {
text-align: center;
}
.message_image img{
width:auto;
height:auto;
max-width: 80%;
max-height: 100%;
}
.datepicker i {
    /* height: 16px; */
    /* width: 16px; */
    position: absolute;
    right: 8%;
    top: 25%;
    cursor: pointer;
    /* vertical-align: bottom; */
    font-size: 100%;
    z-index: 999;
}
.star_mark{
    color:red;
}
</style>
<link rel="stylesheet" href="https://cdn.bootcss.com/bootstrap-daterangepicker/2.1.24/daterangepicker.min.css" integrity="sha384-2P5djm4uaAmneB+fXVYmpYEz0Ic97wQtF4v3ilTNGTxXkhHdWi7QxboqH3djtfJw" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdn.bootcss.com/blueimp-file-upload/9.12.5/css/jquery.fileupload.min.css" integrity="sha384-/cEZgEA00SiCs/3Xr4k0NQ9Ah+0JV4Erxn3BiUOTd54a+3lUvM1GUESDouZY3rbe" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdn.bootcss.com/bootstrap-treeview/1.2.0/bootstrap-treeview.min.css" integrity="sha384-jWWSlMQEtmAWRLiCoxRF8r3hUyxpq1Z17FTzuTaAF3R/cGOsR8R1+2REtk5limYA" crossorigin="anonymous">
{/block}

{block name=content}
<div class="page-header">
  <h2>{$title}</h2>
</div>
<div class="row">
    <form action="/infocenter/ajax/message/update?id={$message.id}" method="POST" role="form" class="form-horizontal">
        <input type="hidden" name="_method" value="POST">
        <input type="hidden" name="csrf_token" id="csrf_token" value="{$csrf_token}">
        <div class="form-group">
            <div class="col-sm-10 col-md-1- col-sm-push-2 bg-info">
            <span class="text text-left text-default">The field marked by <span class="star_mark">'*'</span> should be filled in, others are optinal.</span>
            </div>
        </div>
        <div class="form-group">
            <label for="message_title" class="col-sm-2 control-label">Status<span class="star_mark">*</span></label>

            <div class="col-sm-3">
            {if $can_change_status }
                <div class="input-group">
                  <input type="text" class="form-control" id="message_status_str" name="message_status_str" placeholder="All" value="{$message_status_str}" readonly="true">
                  <input type="hidden" class="form-control" id="message_status" name="message_status" placeholder="All" value="{$message.status}">
                  <div class="input-group-btn">
                    <button type="button" id="message_status_btn" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Status<span class="caret"></span></button>
                    <ul class="dropdown-menu dropdown-menu-right">
                     {foreach from=$message_status_enum key=k item=v }
                        <li><a href="#" onClick="doSelStatus('{$k}','{$v}')" >{$v}</a></li>
                     {/foreach}
                    </ul>
                  </div><!-- /btn-group -->
                </div><!-- /input-group -->
              {else}
                    (处于分组中的消息无法更改状态)
              {/if}
            </div>
        </div>

        {* <legend>Edit Message</legend> *}
        <div class="form-group group_field">
            <label for="message_appid" class="col-sm-2 col-md-2 control-label">APP ID<span class="star_mark">*</span>:</label>
            <div class="col-sm-3 col-md-3">
                <select id="message_appid" class="form-control" required="required">
                    <option value="">Please select one...</option>
                    {foreach from=$app_list item=app}
                        {if $message.appid == $app.appid}
                            <option value="{$app.appid}" selected>{$app.appname}</option>
                        {else}
                            <option value="{$app.appid}">{$app.appname}</option>
                        {/if}
                    {/foreach}
                </select>
            </div>
            <div class="col-sm-5 col-md-5">
                <label for="message_appver_min" class="inline">min app version</label>
                <input type="text" class="form-control inline" id="message_appver_min" style="width:80px;display:inline;" placeholder="min version code" value="{$message.appver_min}">
                <label for="message_appver_max" class="inline">max app version</label>
                <input type="text" class="form-control inline" id="message_appver_max" style="width:80px;display:inline;" placeholder="max version code" value="{$message.appver_max}">
            </div>
        </div>

        <div class="form-group">
            <label for="message_language" class="col-sm-2 col-md-2 control-label">Tag Name：</label>
            <div class="col-sm-4 col-md-4">
                <input type="text" class="form-control" id="tag_name" name="tag_name" value="{$message.flag}">
            </div>
        </div>

        <hr>
        <div class="form-group">
            <label for="message_language" class="col-sm-2 col-md-2 control-label">Language</label>
            <div class="col-sm-4 col-md-4">
                <select id="message_language_unadd" class="form-control">
                    <option class='default' value="" selected>Please select language...</option>
                     {foreach from=$languages key=key item=val}
                     {if $key!='en_US'}
                        <option id="option_language_{$key}"  value="{$key}">{$val}</option>
                     {/if}
                    {/foreach}
                </select>
            </div>
            <span class="btn btn-danger" id="deleteLanguage">delete</span>
        </div>

        <div class="form-group" >
            <input type="hidden" id="current_language" value="en_US">
            <div class="col-sm-10 col-md-10 col-sm-push-2" id="lang_group">
                <span class="btn btn-success button_language" id="button_language_en_US">en_US</span>
            </div>
        </div>

        <div class="form-group">
            <label for="message_title" class="col-sm-2 col-md-2 control-label">title<span class="star_mark">*</span></label>
            <div class="col-sm-8 col-md-8">
                <input type="text" class="form-control" id="message_title" placeholder="title" value="{$message.title}" required>
            </div>
        </div>
        <div class="form-group">
            <label for="message_description" class="col-sm-2 col-md-2 control-label">description<span class="star_mark">*</span></label>
            <div class="col-sm-8 col-md-8">
                <textarea id="message_description" class="form-control" rows="3" placeholder="message description" required>{$message.description}</textarea>
            </div>
        </div>
        <div class="form-group">
            <label for="message_link" class="col-sm-2 col-md-2 control-label">link</label>
            <div class="col-sm-8 col-md-8">
                <input type="text" class="form-control" id="message_link" placeholder="link/url (http://...)" value="{$message.link}">
            </div>
        </div>
        <div class="form-group">
            <label for="message_call_to_action" class="col-sm-2 col-md-2 control-label">call_to_action</label>
            <div class="col-sm-8 col-md-8">
                <input type="text" class="form-control" id="message_call_to_action" placeholder="" value="{$message.call_to_action}">
            </div>
        </div>
        <div class="form-group">
            <label for="message_icon" class="col-sm-2 col-md-2 control-label">icon</label>
            <div class="col-sm-8 col-md-8">
                <input type="text" class="form-control hidden" id="message_icon" placeholder="icon url">
                <span class="btn btn-success fileinput-button btn_message_icon">
                    <i class="glyphicon glyphicon-plus"></i>
                    <span>Upload Icon...</span>
                    <!-- The file input field used as target for the file upload widget -->
                    <input id="message_icon" class="fileinput" type="file" name="images[]" value="{$message.icon}">
                </span>
                <br><br>
                <div id="progress_message_icon" class="progress">
                    <div class="progress-bar progress-bar-success progress-bar-striped active"></div>
                </div>
                <div id="images_message_icon" class="images files">
                    <img src="{$message.icon}" class="message_icon img-responsive img-thumbnail" alt="icon">
                </div>
            </div>
        </div>
        <div class="form-group">
            <label for="message_image" class="col-sm-2 col-md-2 control-label">image</label>
            <div class="col-sm-8 col-md-8">
                <!-- The fileinput-button span is used to style the file input field as button -->
                <span class="btn btn-success fileinput-button btn_message_image">
                    <i class="glyphicon glyphicon-plus"></i>
                    <span>Upload Image...</span>
                    <!-- The file input field used as target for the file upload widget -->
                    <input id="message_image" class="fileinput" type="file" name="images[]" value="{$message.image}">
                </span>
                <br><br>
                <!-- The global progress bar -->
                <div id="progress_message_image" class="progress">
                    <div class="progress-bar progress-bar-success progress-bar-striped active"></div>
                </div>
                <!-- The container for the uploaded files -->
                <div id="images_message_image" class="images files">
                    <img src="{$message.image}" class="message_image img-responsive img-thumbnail" alt="Image">
                </div>

            </div>
        </div>

        <div class="form-group group_field">
            <label for="" class="col-sm-2 col-md-2 control-label">推送方式(可多选)<span class="star_mark">*</span></label>
            <div class="col-sm-8 col-md-8">
                <label class="checkbox-inline">
                    <input type="checkbox" id="message_notification" value="{$message.notification}"
                    {if $message.notification}checked{/if}>notification(通知消息)
                </label>
                <label class="checkbox-inline">
                    <input type="checkbox" id="message_popup" value="{$message.popup}" {if $message.popup}checked{/if}>popup(应用内弹出)
                </label>
                <label class="checkbox-inline">
                    <input type="checkbox" id="message_redpoint" value="1" checked disabled>应用内小红点(必选)
                </label>
            </div>
        </div>

        <div class="form-group group_field">
            <label for="message_region" class="col-sm-2 col-md-2 control-label">Target(region)<span class="star_mark">*</span></label>
            <div class="col-sm-8 col-md-8">
                <div class="col-sm-4 col-md-4">
                    <label class="checkbox-inline">
                        <input type="checkbox" id="message_region" value="{$message.region}"
                        {if $message.region}checked{/if}>All Region
                    </label>
                </div>

                <input type="hidden" class="form-control" id="message_AS" value="{$message.AS}">
                <input type="hidden" class="form-control" id="message_EU" value="{$message.EU}">
                <input type="hidden" class="form-control" id="message_EE" value="{$message.EE}">
                <input type="hidden" class="form-control" id="message_NA" value="{$message.NA}">
                <input type="hidden" class="form-control" id="message_LA" value="{$message.LA}">
                <input type="hidden" class="form-control" id="message_OC" value="{$message.OC}">
                <input type="hidden" class="form-control" id="message_AF" value="{$message.AF}">
                <input type="hidden" class="form-control" id="message_UN" value="{$message.UN}">
                <div id="region_tree" class="col-sm-6 col-md-6"></div>
            </div>
        </div>

        <div class="form-group group_field">
            <label for="" class="col-sm-2 col-md-2 control-label">validity period<span class="star_mark">*</span></label>
            <div class="col-xs-5 col-sm-3 col-md-3">
                <div class="input-group datepicker start_end_time" style="min-width:240px;">
                    <input type="text" class="form-control" name="daterangepicker" id="start_end_time" >
                    <i class="glyphicon glyphicon-calendar fa fa-canlender input-group-btn"></i>
                    <input type="hidden" id="message_start" value="{$message.start}">
                    <input type="hidden" id="message_end" value="{$message.end}">
                </div>
            </div>
            <label for="message_time" class="col-sm-2 col-md-2 control-label">Time Clock:</label>
            <div class="col-sm-2 col-md-2">
                <input type="text" class="form-control" id="message_time" placeholder="(example:18:01)" value="{$message.time}">
            </div>
        </div>

        <div class="form-group">
            <div class="col-sm-offset-2 col-md-offset-2 col-sm-8 col-md-8">
                <input type="hidden" id="message_id" value="{$message.id}">
                <button type="button" class="btn btn-primary" id="btn_save">Save</button>
                <button type="button" class="btn btn-default" id="btn_cancel">Cancel</button>
            </div>
        </div>

    </form>
</div>
{/block}

{block name=javascript append}
<script src="https://cdn.bootcss.com/moment.js/2.14.1/moment.min.js" integrity="sha384-ohw6o2vy/chIavW0iVsUoWaIPmAnF9VKjEMSusADB79PrE3CdeJhvR84BjcynjVl" crossorigin="anonymous"></script>
<script src="https://cdn.bootcss.com/moment.js/2.14.1/locale/zh-cn.js" integrity="sha384-5lNG4bA5eui2bxYDZYKzEmo/iLo2k38n5VcCjyXmkPFyCcztSzLoEQmcynU1rzW7" crossorigin="anonymous"></script>
{* <script src="https://cdn.bootcss.com/bootstrap-daterangepicker/2.1.24/moment.min.js" integrity="sha384-MV8AwEgYXLMw5ZPj4763CSPk+tYGoUZGdwr/+EfkAZ1Dl2rGHxOMpQ1IW7VtyUPn" crossorigin="anonymous"></script> *}
<script src="https://cdn.bootcss.com/bootstrap-daterangepicker/2.1.24/daterangepicker.min.js" integrity="sha384-OUryzIv6N1W29AxQaHZ2imPUzjV5vSi2bdvJhR10CUwz0avr57YKm/vQeflPnHdM" crossorigin="anonymous"></script>

<script src="//cdn.bootcss.com/blueimp-file-upload/9.12.5/js/vendor/jquery.ui.widget.min.js" integrity="sha384-nGh8khjBjCD80rGfjnQZ+72Y3wYz0HHym3jC0kf9g1xVni73kthZ9fxtSIwjZ6zQ" crossorigin="anonymous"></script>
<script src="https://blueimp.github.io/JavaScript-Load-Image/js/load-image.all.min.js" ></script>
<script src="https://blueimp.github.io/JavaScript-Canvas-to-Blob/js/canvas-to-blob.min.js" ></script>

<script src="//cdn.bootcss.com/blueimp-file-upload/9.12.5/js/jquery.iframe-transport.min.js" integrity="sha384-L1OhlVcFQPXKloYQlEFOQQpRFoMgM8kecftiV5yLQV0Cze6jK4myjnX2HHHw1yD+" crossorigin="anonymous"></script>
<script src="//cdn.bootcss.com/blueimp-file-upload/9.12.5/js/jquery.fileupload.min.js" integrity="sha384-4fwO+NFkS0gdgfgIPTDvbaaD9n15lf3aA15hdn20LwTDN/QoEccsz5Tic+ncA8N9" crossorigin="anonymous"></script>
<script src="//cdn.bootcss.com/blueimp-file-upload/9.12.5/js/jquery.fileupload-process.min.js" integrity="sha384-wfPQHTx+v7NrjQ0cU/M5aZyd5A7SYTMNNRTtRWtuGcbJ+DEgtfKfD4Yi4J0h5NU5" crossorigin="anonymous"></script>

<script src="//cdn.bootcss.com/blueimp-file-upload/9.12.5/js/jquery.fileupload-image.min.js" integrity="sha384-5jnqQ1fz4Go1dWmEciZRgRte357rUBTqgpOiTufbt3A9RlBssNXmYV9ebiGKejs7" crossorigin="anonymous"></script>
<script src="//cdn.bootcss.com/blueimp-file-upload/9.12.5/js/jquery.fileupload-validate.min.js" integrity="sha384-N1ZqdkCijnsLxYtlvOpZ4eVbFu4p5ME6UrPnTxKHoHVXJHLY5C5VXvj3edi4U0eX" crossorigin="anonymous"></script>

<script src="https://cdn.bootcss.com/bootstrap-treeview/1.2.0/bootstrap-treeview.min.js" integrity="sha384-56/tJ56bRazNVqgKlGpvfpUD6sGn5SJ676iVy8A5SS44jDp9SMykidEAdu2e4u34" crossorigin="anonymous"></script>

<script>
if ($('#message_region').is(':checked')) {
    $('#region_tree').hide();
} else {
    $('#region_tree').show();
}
$('#start_end_time').daterangepicker({
    "showDropdowns": true,
    // "showWeekNumbers": true,
    // "showISOWeekNumbers": true,
    // "timePicker": true,
    // "timePicker24Hour": true,
    // "timePickerIncrement": 5,
    "autoApply": true,
    // "dateLimit": {
        // "days": 7
    // },
    "ranges": {
        "Today": [
            "{$smarty.now|date_format:'%Y-%m-%d %H:%M:%S'}",
            "{$smarty.now|date_format:'%Y-%m-%d %H:%M:%S'}"
        ],
        "Tomorrow": [
            "{$smarty.now|date_format:'%Y-%m-%d %H:%M:%S'}",
            "{'tomorrow'|date_format:'%Y-%m-%d %H:%M:%S'}"
        ],
        "3 Days": [
            "{$smarty.now|date_format:'%Y-%m-%d %H:%M:%S'}",
            "{'+ 3 days'|date_format:'%Y-%m-%d %H:%M:%S'}"
        ],
        "7 Days": [
            "{$smarty.now|date_format:'%Y-%m-%d %H:%M:%S'}",
            "{'+ 7 days'|date_format:'%Y-%m-%d %H:%M:%S'}"
        ],
        "14 Days": [
            "{$smarty.now|date_format:'%Y-%m-%d %H:%M:%S'}",
            "{'+ 14 days'|date_format:'%Y-%m-%d %H:%M:%S'}"
        ],
        "1 Month": [
            "{$smarty.now|date_format:'%Y-%m-%d %H:%M:%S'}",
            "{'+ 1 months'|date_format:'%Y-%m-%d %H:%M:%S'}"
        ],
        "1 year": [
            "{$smarty.now|date_format:'%Y-%m-%d %H:%M:%S'}",
            "{'next year'|date_format:'%Y-%m-%d %H:%M:%S'}"
        ],
        "10 year": [
            "{$smarty.now|date_format:'%Y-%m-%d %H:%M:%S'}",
            "{'+10 years'|date_format:'%Y-%m-%d %H:%M:%S'}"
        ],

    },
    "locale": {
        "format": "YYYY-MM-DD",
        "separator": "  to  ",
        "applyLabel": "Apply",
        "cancelLabel": "Cancel",
        "fromLabel": "From",
        "toLabel": "To",
        "customRangeLabel": "Custom",
        // "weekLabel": "W",
        "daysOfWeek": [
            "Sun",
            "Mon",
            "Tue",
            "Wed",
            "Thu",
            "Fri",
            "Sat"
        ],
        "monthNames": [
            "January",
            "February",
            "March",
            "April",
            "May",
            "June",
            "July",
            "August",
            "September",
            "October",
            "November",
            "December"
        ],
        "firstDay": 1
    },
    // "alwaysShowCalendars": true,
    "startDate": "{$message.start|date_format:'%Y-%m-%d %H:%M:%S'}",
    "endDate": "{$message.end|date_format:'%Y-%m-%d %H:%M:%S'}",
    "opens": "right",
    "drops": "up",
    "buttonClasses": "btn btn-xs btn-sm btn-md btn-lg"
}, function(start, end, label) {
    $('#message_start').val(start.format('YYYY-MM-DD'));
    $('#message_end').val(end.format('YYYY-MM-DD'));
    console.log('New date range selected: ' + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD') + ' (predefined range: ' + label + ')');
});

$('#message_region').click(function (){
    $this = $('#message_region');
    // var region = $this.checked;
    if ($this.is(':checked')) {
        $this.val('1');
        $('#region_tree').hide();
    } else {
        $this.val('');
        $('#region_tree').show();
    }
});
function getRegionTree() {
    var tree = [
    {foreach $region_tree as $continentCode=>$v }
        {
            text: "{$v.name}",
            type: "continent",
            code: '{$continentCode}',
            // icon: "glyphicon glyphicon-stop",
            // selectedIcon: "glyphicon glyphicon-stop",
            // expandIcon: "glyphicon glyphicon-plus",
            color: "#000000",
            backColor: "#FFFFFF",
            href: "#node-1",
            selectable: false,
            state: {
                checked: {$v.checked},
                // disabled: true,
                expanded: false,
                selected: false
            },
            tags: ['available'],
            nodes: [
            {foreach $v.countries as $cc=>$country}
            {
                text: '{$country.name}',
                type: 'country',
                code: '{$cc}',
                mask: {$country.mask},
                selectable: false,
                state:{
                    checked: {$country.checked},
                }
            },
            {/foreach}
            ],
            liSelected:[{foreach $v.liSelected as $cc}"{$cc}",{foreachelse} {/foreach}]
        },
    {/foreach}
    ];
    return tree;
}

function ajax_save(message) {
    $.ajax({
        url: '/infocenter/ajax/message/update?id={$message.id}',
        type: 'POST',
        async: true,
        // cache: false,
        // contentType:'application/json',
        // dataType: 'json',
        data: {
            "csrf_token":'{$csrf_token}',
            "message":JSON.stringify(message)
        },
    }).done(function(data, txtStatus, xhr) {
        console.log(data);
        if (data.status == 'ok') {
            console.log("save success." + txtStatus);
            saveTranslation($('#message_id').val());
            // bootbox.alert('save success!');
        } else if (data.status == 'error') {
            var errors = " " + data.errors.join('<br>');
            console.log("errors:"+errors);
            bootbox.alert({
                title:data.message,
                message:'1. <span class="text-danger">FAIL</span> to save basic message!:' + errors
            });
        }
    }).fail(function(xhr, txtStatus, error) {
        bootbox.alert({
            title:'Net Error!',
            message:'<p class="text-danger">Cannot Connect The Server,Please check your Network!</p>' + txtStatus
        });
        console.log("request error :" + txtStatus);
        console.log(error);
    }).always(function(xhr, txtStatus) {
        console.log("complete." + txtStatus);
    });
}

$("#btn_save").click(function(){
    changeLanguage('en_US');

    var support_language = getSupportLanguage(),
    msg_status = $('#message_status').val(),
    flag = $('#tag_name').val();

    var message = {
        "id"          : $('#message_id').val(),
        "flag"        : flag,
        "status"      : msg_status,
        "title"       : Transtlation['en_US']['title'],
        "description" : Transtlation['en_US']['description'],
        "call_to_action" : Transtlation['en_US']['call_to_action'],
        "link"        : $('#message_link').val(),
        "icon"        : $('img.message_icon').attr('src'),
        "image"       : $('img.message_image').attr('src'),
        "notification": $('#message_notification').is(':checked'),
        "popup"       : $('#message_popup').is(':checked'),
        "language"    : support_language,
        "region"      : $('#message_region').val(),
        "AS"          : $('#message_AS').val(),
        "EU"          : $('#message_EU').val(),
        "EE"          : $('#message_EE').val(),
        "NA"          : $('#message_NA').val(),
        "LA"          : $('#message_LA').val(),
        "OC"          : $('#message_OC').val(),
        "AF"          : $('#message_AF').val(),
        "UN"          : $('#message_UN').val(),
        "appid"       : $('#message_appid').val(),
        "appver_min"  : $('#message_appver_min').val(),
        "appver_max"  : $('#message_appver_max').val(),
        "time"        : $('#message_time').val(),
        "start"       : $('#message_start').val(),
        "end"         : $('#message_end').val(),
    };

    var target_pass = false;
    var error_message = [];
    $.each(message,function(key,val) {

        if(key == 'appid' && (!val || val=='') && msg_status!='2'){
            error_message.push('"APP ID" Cannot Empty!');
        }

        if(key == 'title' && (!val || val=='')){
            error_message.push('"title" Cannot Empty!');
        }

        if(key == 'description' && (!val || val=='')){
            error_message.push('"description" Cannot Empty!');
        }

        if (( (key=='region' || key=='AS' || key=='EU' || key=='EE' || key=='NA' || key=='LA' || key=='OC' || key=='AF' || key=='UN')
            && val && val !='' && val != '0') || msg_status == '2') {
            target_pass = true;
        }


    });

    if(!target_pass){
        error_message.push('"Target" must select one!');
    }
    if (error_message.length > 0) {
        error_message.unshift('Please Check your input!');
        bootbox.alert({
            title: 'Check Error!',
            message: error_message.join('<br>'),
        });
        return false;
    }


    var confirmHTML = '',
    group_field = ['notification', 'popup', 'region', 'AS', 'EU', 'EE', 'NA', 'LA', 'OC', 'AF', 'UN','appid', 'appver_min', 'appver_max', 'time','start', 'end'];
    for (k in message) {
        if (message.hasOwnProperty.call(message, k)) {
            if(msg_status == '2'){
                if($.inArray(k, group_field) == -1){
                    confirmHTML += '<p class="row"><span class="col-sm-2"><strong>' + k + '</strong></span><span class="col-sm-6">' + message[k] + '</span></p>';
                }
            }else{
                confirmHTML += '<p class="row"><span class="col-sm-2"><strong>' + k + '</strong></span><span class="col-sm-6">' + message[k] + '</span></p>';
            }

        }
    }
    confirmHTML = '<div class="confirmhtml">'+confirmHTML+'</div>';

    bootbox.dialog({
        message: confirmHTML,
        title: "please confirm...",

        show: true,
        backdrop: true,
        closeButton: true,
        animate: true,
        className: "my-modal",

        buttons: {
            save: {
                label: "Save",
                className: "btn-primary",
                callback: function() { ajax_save(message); }
            },
            cancel: {
                label: "Cancel",
                className: "btn-default",
                callback: function() {}
            },
        }
    }); // end dialog
}); //end save
$(function () {
    // 'use strict';
    // var uploadButton = $('<button/>')
    //     .addClass('btn btn-primary')
    //     .prop('disabled', true)
    //     .text('Processing...')
    //     .on('click', function () {
    //         var $this = $(this),
    //             data = $this.data();
    //         $this.off('click').text('Abort').on('click', function () {
    //                 $this.remove();
    //                 data.abort();
    //             });
    //         data.submit().always(function () {
    //             $this.remove();
    //         });
    //     });
    $('.progress').hide();
    var img_btn_del = $('<button/>')
                        .addClass('btn btn-danger btn-image')
                        .prop('disabled', true)
                        .append('<i class="glyphicon glyphicon-trash"></i>')
                        .append('<span>Delete</span>')
                        .on('click',function(){
                            var $this = $(this),
                                data = $this.data();
                                node = data.node;
                                btn_upload = data.btn_upload;
                            console.log('delete button data:');
                            console.log(data);
                            $.ajax({
                                url : data.deleteUrl,
                                type: data.deleteType,
                                data: { csrf_token: '{$csrf_token}',
                                    images:[data.name]
                                },
                            }).done(function(result,txtStatus,error) {
                                $('#'+node).remove();
                                $('#btn_'+btn_upload).show();
                                console.log("delete image success");
                            }).fail(function() {
                                console.log("delete image error");
                            }).always(function() {
                                console.log("delete image complete");
                            });
                            return false;
                        });
    $('.fileinput').fileupload({
        url: '/common/ajax/upload/image',
        formData: { csrf_token: '{$csrf_token}' },
        paramName: 'images',
        singleFileUploads: false,
        dataType: 'json',
        autoUpload: true,
        acceptFileTypes: /(\.|\/)(gif|jpe?g|png|ico|icon)$/i,
        maxFileSize: 999000,
        // Enable image resizing, except for Android and Opera,
        // which actually support image resizing, but fail to
        // send Blob objects via XHR requests:
        disableImageResize: /Android(?!.*Chrome)|Opera/.test(window.navigator.userAgent),
        previewMaxWidth: 800,
        previewMaxHeight: 600,
        previewCrop: false
    }).on('fileuploadadd', function (e, data) {
        var thisid = $(this).attr('id');
        data.context = $('<div/>').appendTo('#images_'+thisid);
        $('#progress_'+ thisid +' .progress-bar').css('width', '0%').attr('aria-valuenow',0).text('0%').show();
        $('#progress_'+ thisid).show();

        $.each(data.files, function (index, file) {
            var node = $('<p/>').append($('<span/>').text(file.name));
            // if (!index) {
                // node.append('<br>').append(img_btn_del.clone(true).data(data));
            // }
            node.appendTo(data.context);
        });
    }).on('fileuploadprocessalways', function (e, data) {
        var index = data.index,
            file = data.files[index],
            node = $(data.context.children()[index]);

        // $.each(data.result.images, function (index, file) {
            // $('<p/>').text(file.name).appendTo('#images');
        // });
        // console.log(data.result);

        // if (file.preview) {
            // node.prepend('<br>').prepend(file.preview);
        // }
        if (file.error) {
            node.append('<br>').append($('<span class="text-danger"/>').text(file.error));
        }
        // if (index + 1 === data.files.length) {
            // data.context.find('button').text('Upload').prop('disabled', !!data.files.error);
        // }
    }).on('fileuploadprogressall', function (e, data) {
        var thisid = $(this).attr('id');

        var progress = parseInt(data.loaded / data.total * 100, 10);
        $('#progress_'+ thisid +' .progress-bar').css('width', progress + '%').attr('aria-valuenow',progress).text(progress+'%').show();
    }).on('fileuploaddone', function (e, data) {
            // console.log(data);
        var thisid = $(this).attr('id');
        $.each(data.result.images, function (index, file) {
            // console.log(file);
            if (file.url_s3) {
                var link = $('<a>').attr('target', '_blank').prop('href', file.url_s3);
                var img = $('<img/>').attr('src', file.url_s3).addClass('image_preview img-responsive img-thumbnail '+thisid);
                link.append(img);
                // $(data.context.children()[index]).wrap(link);
                var nodeID = 'img_preview_' + parseInt(Math.random()* 500000);
                var node = $('<div/>').addClass('image_preview '+nodeID).attr('id', nodeID);
                file.node = nodeID;
                file.btn_upload = thisid;
                node.append(img_btn_del.clone(true).data(file).prop('disabled',false)).append('<br>').append(link);
                $('#images_'+thisid).html('').append(node);
                $('#progress_'+thisid).hide();
                $('.'+thisid).val(file.url_s3);
                $('#btn_'+thisid).hide();
            } else if (file.error) {
                var error = $('<span class="text-danger"/>').text(file.error);
                $(data.context.children()[index]).append('<br>').append(error);
            }
        });
    }).on('fileuploadfail', function (e, data) {
        $.each(data.files, function (index) {
            var error = $('<span class="text-danger"/>').text('File upload failed.');
            $(data.context.children()[index]).append('<br>').append(error);
        });
    }).prop('disabled', !$.support.fileInput)
        .parent().addClass($.support.fileInput ? undefined : 'disabled');

}); //end image upload

$(function(){

    function addRegion(node) {
        var nodeId = node.nodeId;
        // bootbox.alert(node.text + 'is checked.');
        // console.log(node);
        // $('#checkable-output').prepend('<p>' + node.text + ' was checked</p>');
        if (node.type == 'country') {
            var parrent = $('#region_tree').treeview('getParent', node);
            $('#region_tree').treeview('checkNode', [ parrent.nodeId, { silent: true } ]);
            // regionTreeFunc('selectNode', parrent);
            // $('#message_'+parrent.code).val()
            if (parrent.liSelected.indexOf(node.code) == -1) {
                parrent.liSelected.push(node.code);
                selected = parrent.liSelected.join(',');
                $('#message_'+parrent.code).val(selected);
            } else {
                console.log('y');
            }
            console.log(parrent.liSelected);
        } else if (node.type == 'continent') {
            var childNodes = node.nodes;
            for (var i = 0; i < childNodes.length; i++) {
                var childNode = childNodes[i];
                $('#region_tree').treeview('checkNode', [ childNode.nodeId, { silent: false } ]);
                // regionTreeFunc('selectNode', childNode);
                if (node.liSelected.indexOf(childNode.code) == -1) {
                    node.liSelected.push(childNode.code);
                } else {
                    console.log('y');
                }
            }
            selected = node.liSelected.join(',');
            $('#message_'+node.code).val(selected);
            console.log(node.liSelected);
        }
    }

    function delRegion(node) {
        var nodeId = node.nodeId;
        // console.log(node);
        if (node.type == 'country') {
            var parrent = $('#region_tree').treeview('getParent', node);
            console.log(parrent.liSelected);

            if (parrent.liSelected.indexOf(node.code) != -1) {
                parrent.liSelected.splice(parrent.liSelected.indexOf(node.code), 1);
                selected = parrent.liSelected.join(',');
                $('#message_'+parrent.code).val(selected);
            } else {
                console.log('n');
            }

            if (parrent.liSelected.length == 0) {
                $('#region_tree').treeview('uncheckNode', [ parrent.nodeId, { silent: true } ]);
                // regionTreeFunc('unselectNode', parrent);
            }

            console.log(parrent.liSelected);
        } else if (node.type == 'continent') {
            var childNodes = node.nodes;
            for (var i = 0; i < childNodes.length; i++) {
                var childNode = childNodes[i];
                $('#region_tree').treeview('uncheckNode', [ childNode.nodeId, { silent: false } ]);
                // regionTreeFunc('unselectNode', childNode);
            }
            node.liSelected=[];
            $('#message_'+node.code).val('');
            console.log(node.liSelected);
        }
    }

    function regionTreeFunc(event, node) {
        $('#region_tree').treeview(event, [ node.nodeId, { silent: true } ]);
        // $('#region_tree').treeview(event, [ node.nodeId, { silent: false } ]);
    }
    var $regionTree = $('#region_tree').treeview({
        data: getRegionTree(),
        levels: 2,
        multiSelect: true,
        highlightSelected: false,
        // showIcon: true,
        showCheckbox: true,
        // selectedIcon: "glyphicon glyphicon-stop",
        checkedIcon: "glyphicon glyphicon-check",
        uncheckedIcon: "glyphicon glyphicon-unchecked",
        collapseIcon: "glyphicon glyphicon-minus",
        onNodeExpanded: function(event, node) {
            // console.log(event);
            // console.log(node);
            // var nodeId = node.nodeId;
            // if (node.type == 'country') {
            //     regionTreeFunc('checkNode', node);
            //     // checkNode(event, node);
            // } else if (node.type == 'continent') {
            //     regionTreeFunc('selectNode', node);
            // }
        },
        onNodeCollapsed: function(event, node) {
            // console.log(event);
            // console.log(node);
            // var nodeId = node.nodeId;
            // if (node.type == 'country') {
            //     regionTreeFunc('uncheckNode', node);
            //     // uncheckNode(event, node);
            // } else if (node.type == 'continent') {
            //     regionTreeFunc('unselectNode', node);
            // }
        },
        onNodeSelected: function(event, node) {
            // console.log(event);
            // console.log(node);
            // var nodeId = node.nodeId;
            // if (node.type == 'country') {
            //     regionTreeFunc('checkNode', node);
            //     // checkNode(event, node);
            // } else if (node.type == 'continent') {
            //     // regionTreeFunc('expandNode', node);
            //     regionTreeFunc('checkNode', node);
            //     // checkNode(event, node);
            // }
        },
        onNodeUnselected: function(event, node) {
            // console.log(event);
            // console.log(node);
            // var nodeId = node.nodeId;
            // if (node.type == 'country') {
            //     regionTreeFunc('uncheckNode', node);
            //     // uncheckNode(event, node);
            // } else if (node.type == 'continent') {
            //     regionTreeFunc('uncheckNode', node);
            //     // regionTreeFunc('collapseNode', node);
            //     // uncheckNode(event, node);
            // }
        },
        onNodeChecked: function(event, node) {
            // console.log(event);
            // console.log(node);
            addRegion(node);
            // alert(node.text + ' is checked!');
        },
        onNodeUnchecked: function (event, node) {
            // console.log(event);
            // console.log(node);
            delRegion(node);
            // alert(node.text + ' is unchecked!');
        }
    });
}); //end region check tree

{literal}

var Transtlation=null;

function getSupportLanguage() {
    var language_array = [];
    $('.button_language').each(function() {
        var lang =  $(this).text();
        language_array.push(lang);
    });
    var lang = language_array.join(',')
    console.log('getSupportLanguage:' + lang);
    return lang;
}



function changeLanguage(lang){

    if (Transtlation == null) {
        Transtlation = {};
    }

    console.log("changeLanguage:lang=" + lang);

     //保存旧值
    var cur_lang = $('#current_language').val();
    console.log("cur_lang=" + cur_lang);
    if (cur_lang != null) {
        var title = $('#message_title').val();
        var description = $('#message_description').val();
        var call_to_action = $('#message_call_to_action').val();
        if (Transtlation[cur_lang] == null){
            Transtlation[cur_lang] = {};
        }
        Transtlation[cur_lang]['title'] = title;
        Transtlation[cur_lang]['description'] = description;
        Transtlation[cur_lang]['call_to_action'] = call_to_action;
    }
    //切换旧值
    $('#message_title').val('');
    $('#message_description').val('');
    $('#message_call_to_action').val('');
    console.log("Transtlation="+JSON.stringify(Transtlation));
    console.log("message_title="+$('#message_title').val());
    console.log("message_description="+$('#message_description').val());
    console.log("message_call_to_action="+$('#message_call_to_action').val());
    if (Transtlation[lang] != null){
        title=Transtlation[lang]['title'];
        description=Transtlation[lang]['description'];
        call_to_action=Transtlation[lang]['call_to_action'];
        if (title != null) {
            $('#message_title').val(title);
        }
        if (description != null) {
            $('#message_description').val(description);
        }
        if (call_to_action != null) {
            $('#message_call_to_action').val(call_to_action);
        }
    }
    $('#current_language').val(lang);
    var a = $('.button_language');
    a.removeClass('btn-success');
    a.addClass('btn-default');
    console.log("id=button_language_"+lang);
    var n=$('#button_language_'+lang);
    n.removeClass('btn-default');
    n.addClass('btn-success');
    console.log($('#button_language_'+lang).css('class'));
    return true;
}

//删除

$('#deleteLanguage').click(function(){
    var cur_lang = $('#current_language').val();
    console.log("$deleteLanguage:cur_lang"+cur_lang);
    if (cur_lang == "") {
        return false;
    }

    if(cur_lang=='en_US' || cur_lang == 'en'){
        return false;
    }
    $('#button_language_'+cur_lang).remove();

    $('#option_language_'+cur_lang).show();

    // 选择下一个button
    var arr = $('.button_language');
    var next = arr[arr.length-1].id.substr('button_language_'.length);
    return changeLanguage(next);
});


$('#message_language_unadd').change(function(){
    var lang = $(this).val();
    $('#option_language_'+lang).hide();

    btn = $('<span id="button_language_'+lang+'">'+lang+'</span>')
            .addClass('btn btn-success button_language')
            .click(function(){
                return changeLanguage(lang);
            });
    $('#lang_group').append(btn);

    changeLanguage(lang);
});

$('#button_language_en_US').click(function(){
    return changeLanguage('en_US');
});

initLanguage();

function saveTranslation(message_id){
    if (Transtlation == null) {
        return false;
    }
    console.log("Transtlation:"+Transtlation);
    csrf_token=$('#csrf_token').val();
    $.ajax({
        url: '/infocenter/ajax/Message/setTranslation',
        type: 'POST',
        async: true,
        // cache: false,
        // contentType:'application/json',
        // dataType: 'json',
        data: {
            "csrf_token":csrf_token,
            "message_id":message_id,
            "translation":JSON.stringify(Transtlation)
        },
    }).done(function(data, txtStatus, xhr) {
        console.log(data);
        if (data.status == 'ok') {
            bootbox.alert({
                title: 'Success',
                message: '1. <span class="text-success">success</span> to save basic message!<br>' +
                         '2. <span class="text-success">success</span> to save translation!'
            });
        } else if (data.status == 'error') {
            errors = data.errors;
            bootbox.alert({
                title: 'Has Error!',
                message: '1. <span class="text-success">success</span> to save basic message success!<br>' +
                         '2. <span class="text-danger">FAIL</span> to save translation:' + errors
            });
        }
    }).fail(function(xhr, txtStatus, error) {
        bootbox.alert({
            title: 'Has Error!',
            message: '1. <span class="text-success">success</span> to save basic message!<br>' +
                     '2. <span class="text-danger">FAIL</span> to save translation, because the Network broken!'
        });
        console.log("save request error :" + txtStatus);
        console.log(error);
    }).always(function(xhr, txtStatus) {
        console.log("save complete." + txtStatus);
    });
}


function addButton(lang){
    btn = $('<span id="button_language_'+lang+'">'+lang+'</span>')
    .addClass('btn btn-default button_language')
    .click(function(){
        console.log("button : your click:"+lang);
        return changeLanguage(lang);
    });
    $('#lang_group').append(btn);
    $('#option_language_'+lang).hide();
}

function addButtons(){
    console.log("init:Transtlation="+JSON.stringify(Transtlation));
    if (Transtlation == null) {
        return false;
    }
    var languageList = Object.keys(Transtlation);
    for (var lang of languageList) {
        console.log("addButtons:lang"+lang);
        addButton(lang);
    }
    // changeLanguage('en_US');
}


function initLanguage(){
    var message_id=$('#message_id').val();
    var csrf_token=$('#csrf_token').val();
    $.ajax({
        url: '/infocenter/ajax/Message/getTranslation',
        type: 'POST',
        async: true,
        // cache: false,
        // contentType:'application/json',
        // dataType: 'json',
        data: {
            "csrf_token":csrf_token,
            "message_id":message_id,
        },
    }).done(function(data, txtStatus, xhr) {
        console.log(data);
        if (data.status == 'ok' && data.translation != '') {
            Transtlation=JSON.parse(data.translation);
            addButtons();
            // bootbox.alert('Get Translation success!');
        } else {
            // bootbox.alert('Get Translation failed!');
        }
    }).fail(function(xhr, txtStatus, error) {
        console.log("save request error :" + txtStatus);
        console.log(error);
    }).always(function(xhr, txtStatus) {
        console.log("save complete." + txtStatus);
        changeLanguage('en_US');
    });
}

function doSelStatus(k,v){
    $("#message_status").val(k); //0
    $("#message_status_str").val(v); // ALL
    if(k == '2'){
        $('.group_field').hide();
    }else{
        $('.group_field').show();
    }
}

{/literal}

if(2 == {$message['status']}){
    $('.group_field').hide();
}else{
    $('.group_field').show();
}


</script>
{/block}
