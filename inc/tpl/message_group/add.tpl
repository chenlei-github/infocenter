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
.search_tag{
    cursor:pointer;
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
    <div action="" method="POST" role="form" class="row form-horizontal">

    <div class="col-md-8">
        <input type="hidden" name="_method" value="POST">
        <input type="hidden" name="csrf_token" id="csrf_token" value="{$csrf_token}">
        <div class="form-group">
            <div class="col-sm-10 col-md-1- col-sm-push-2 bg-info">
<!--             <span class="text text-left text-default">The field marked by <span class="star_mark">'*'</span> should be filled in, others are optinal.</span> -->
            </div>
        </div>

        <input type="hidden" id="group_id" value="{$group_id}">
        <div class="form-group">
            <label for="message_status" class="col-sm-2 control-label"> Name <span class="star_mark">*</span>:</label>
            <div class="col-sm-4">
            <input type="text" name="name" class="form-control" id="name" value="{if $group_data neq ''}{$group_data.name}{/if}">
            </div>
        </div>


        <div class="form-group">
            <label for="message_status" class="col-sm-2 control-label">Status<span class="star_mark">*</span>:</label>
            <div class="col-sm-4">
                <div class="input-group">
                  <input type="text" class="form-control" id="message_status_str" name="message_status_str" placeholder="Draft" value="{if $group_data neq ''}{if $group_data['status'] eq '1' }Publish{else}Draft{/if}{/if}" readonly>
                  <input type="hidden" class="form-control" id="message_status" name="message_status" placeholder="Draft" value="{if $group_data neq ''}{$group_data.status}{else}0{/if}">
                  <div class="input-group-btn">
                    <button type="button" id="message_status_btn" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Status <span class="caret"></span></button>
                    <ul class="dropdown-menu dropdown-menu-right">
                     {foreach from=$message_status_enum key=k item=v }
                         <li><a href="#" onClick="doSelStatus('{$k}','{$v}')" >{$v}</a></li>
                     {/foreach}
                    </ul>
                  </div><!-- /btn-group -->
                </div><!-- /input-group -->
            </div>
        </div>


        <div class="form-group">

            <label for="message_appid" class="col-sm-2 col-md-2 control-label">APP ID<span class="star_mark">*</span>:</label>

            <div class="col-sm-3 col-md-4">
                <select id="message_appid" class="form-control" required="required">
                    <option value="" selected>Please select one...</option>
                    {foreach from=$app_list item=app}
                        <option {if $group_data neq ''}{if $group_data['appid'] eq $app['appid']}selected="selected"{/if}{/if} value="{$app.appid}">{$app.appname}</option>
                    {/foreach}
                </select>
            </div>
        </div>

        <div class="form-group">

            <label for="message_appid" class="col-sm-2 col-md-2 control-label"> App version :</label>

            <div class="col-sm-3 col-md-8">
                <label for="message_appver_min" class="inline">Min version</label>
                <input type="text" class="form-control inline" id="message_appver_min" style="width:100px;display:inline;" value="{if $group_data neq ''}{$group_data['appver_min']}{/if}">~

                <label for="message_appver_max" class="inline">Max version</label>
                <input type="text" class="form-control inline" id="message_appver_max" style="width:100px;display:inline;" value="{if $group_data neq ''}{$group_data['appver_max']}{/if}">
            </div>
        </div>


        <div class="form-group">
            <label for="" class="col-sm-2 col-md-2 control-label">推送方式<span class="star_mark">*</span></label>
            <div class="col-sm-8 col-md-8">
                <label class="checkbox-inline">
                    <input type="checkbox" id="message_notification" value="0" {if $group_data neq ''}{if $group_data['notification'] eq '1'}checked="checked"{/if}{/if}  >通知消息
                </label>
                <label class="checkbox-inline">
                    <input type="checkbox" id="message_popup" value="0" {if $group_data neq ''}{if $group_data['popup'] eq '1'}checked="checked"{/if}{/if} >应用内弹出
                </label>
                <label class="checkbox-inline">
                    <input type="checkbox" id="message_redpoint" value="1" checked disabled>应用内小红点(必选)
                </label>
            </div>
        </div>

        <div class="form-group">
            <label for="message_region" class="col-sm-2 col-md-2 control-label">Target(region)<span class="star_mark">*</span></label>
            <div class="col-sm-8 col-md-8">
                <div class="col-sm-4 col-md-4" style="margin-left: -15px;">
                    <label class="checkbox-inline">
                        <input type="checkbox" id="message_region" value="1" checked>All Region
                    </label>
                </div>

                <input type="hidden" class="form-control" id="message_AS" value="{if $group_data neq ''}{if $group_data['AS'] neq '0'}{$group_data['AS']}{/if}{/if}">
                <input type="hidden" class="form-control" id="message_EU" value="{if $group_data neq ''}{if $group_data['EU'] neq '0'}{$group_data['EU']}{/if}{/if}">
                <input type="hidden" class="form-control" id="message_EE" value="{if $group_data neq ''}{if $group_data['EE'] neq '0'}{$group_data['EE']}{/if}{/if}">
                <input type="hidden" class="form-control" id="message_NA" value="{if $group_data neq ''}{if $group_data['NA'] neq '0'}{$group_data['NA']}{/if}{/if}">
                <input type="hidden" class="form-control" id="message_LA" value="{if $group_data neq ''}{if $group_data['LA'] neq '0'}{$group_data['LA']}{/if}{/if}">
                <input type="hidden" class="form-control" id="message_OC" value="{if $group_data neq ''}{if $group_data['OC'] neq '0'}{$group_data['OC']}{/if}{/if}">
                <input type="hidden" class="form-control" id="message_AF" value="{if $group_data neq ''}{if $group_data['AF'] neq '0'}{$group_data['AF']}{/if}{/if}">
                <input type="hidden" class="form-control" id="message_UN" value="{if $group_data neq ''}{if $group_data['UN'] neq '0'}{$group_data['UN']}{/if}{/if}">
            </div>
            <br/><br/>
            <div id="region_tree" class="col-md-5 col-md-offset-2"></div>
        </div>

        <div class="form-group">
            <label for="" class="col-sm-2 col-md-2 control-label">validity period<span class="star_mark">*</span></label>
            <div class="col-xs-5 col-sm-3 col-md-3">
                <div class="input-group datepicker start_end_time" style="min-width:240px;">
                    <input type="text" readonly="readonly" class="form-control" name="daterangepicker" id="start_end_time" value="{if $group_data neq ''} {$group_data['start']} to {$group_data['end']}{/if}">
                    <i class="glyphicon glyphicon-calendar fa fa-canlender input-group-btn"></i>

                    <input type="hidden" id="message_start" value="{if $group_data neq ''} {$group_data['start']} {else} {$smarty.now|date_format:'%Y-%m-%d 00:00:00'} {/if}">
                    <input type="hidden" id="message_end" value="{if $group_data neq ''} {$group_data['end']} {else} {$smarty.now|date_format:'%Y-%m-%d 23:59:59'} {/if}">
                </div>
            </div>
        </div>


        <div class="form-group">

            <label for="message_time" class="col-sm-2 col-md-2 control-label">Time Clock:</label>
            <div class="col-sm-3 col-md-3">
                <input type="text" class="form-control" id="message_time" placeholder="(example:18:01)" value="{if $group_data neq ''} {$group_data['time']} {/if}">
            </div>

        </div>

    </div>

<form id="prob_list_form">

    <div class="col-md-5" style="margin-left:-180px;">
        <table class="table tab_msg_list">

            <tr>
                <th>选择/取消
                <input type="checkbox" name="" class="checkbox select_all" >
                </th>

                <th>Id</th>

                <th>Title</th>

                <th>Tag
                  <div class="form-group" style="width: 150px;">
                    <div class="input-group">
                      <input type="text" class="form-control tag_val" name="tag_val" placeholder="Search tag" value="{$flag}">
                      <div class="input-group-addon search_tag">&nbsp;<i class="glyphicon glyphicon-search"></i></div>
                    </div>
                  </div>
                </th>

                <th>Probability</th>

            </tr>

            {foreach from=$data item=row}
            <tr data-id="{$row.id}">
                <td><input type="checkbox" {if $group_data neq ''}{if $row['is_checked'] eq '1'}checked="checked"{/if}{/if} name="msg_id_list[]" class="checkbox" value="{$row.id}"></td>
                <td>{$row.id}</td>
                <td><a target="_blank" href="/infocenter/message/edit?id={$row.id}">{$row.title}</a></td>
                <td>{$row.flag}</td>
                <td>

                  <div class="form-group prob" style="width: 90px;{if $group_data eq ''}display: none;{else}{if $row['is_checked'] eq '0'}display: none;{/if}{/if}">
                    <div class="input-group">
                      <input type="text" class="form-control msg_prob" name="prob_list[]" placeholder="" value="{if $group_data neq ''}{if $row['is_checked'] eq '1'}{$row['prob']}{/if}{/if}">
                      <div class="input-group-addon">%</div>
                    </div>
                  </div>

                </td>

            </tr>
            {/foreach}
        </table>
    </div>

</form>

    <div class="col-md-12" style="text-align: center;">
        <div>

            <button type="button" class="btn btn-primary" id="btn_save">　　Save　　</button>
            &nbsp;&nbsp;
            <button type="button" class="btn btn-default" onclick="window.location.href='/infocenter/MessageGroup'">Cancel</button>
        </div>
    </div>



    </div>



<hr>


</div>
{/block}

{block name=javascript append}
<script src="https://cdn.bootcss.com/moment.js/2.14.1/moment.min.js" integrity="sha384-ohw6o2vy/chIavW0iVsUoWaIPmAnF9VKjEMSusADB79PrE3CdeJhvR84BjcynjVl" crossorigin="anonymous"></script>
<script src="https://cdn.bootcss.com/moment.js/2.14.1/locale/zh-cn.js" integrity="sha384-5lNG4bA5eui2bxYDZYKzEmo/iLo2k38n5VcCjyXmkPFyCcztSzLoEQmcynU1rzW7" crossorigin="anonymous"></script>
{* <script src="https://cdn.bootcss.com/bootstrap-daterangepicker/2.1.24/moment.min.js" integrity="sha384-MV8AwEgYXLMw5ZPj4763CSPk+tYGoUZGdwr/+EfkAZ1Dl2rGHxOMpQ1IW7VtyUPn" crossorigin="anonymous"></script> *}
<script src="https://cdn.bootcss.com/bootstrap-daterangepicker/2.1.24/daterangepicker.min.js" integrity="sha384-OUryzIv6N1W29AxQaHZ2imPUzjV5vSi2bdvJhR10CUwz0avr57YKm/vQeflPnHdM" crossorigin="anonymous"></script>
<script src="https://cdn.bootcss.com/bootstrap-treeview/1.2.0/bootstrap-treeview.min.js" integrity="sha384-56/tJ56bRazNVqgKlGpvfpUD6sGn5SJ676iVy8A5SS44jDp9SMykidEAdu2e4u34" crossorigin="anonymous"></script>

<script>

$(function(){

    $('input[name="msg_id_list[]"]','.tab_msg_list').on('change', function(){
        var state =  $(this).prop('checked');
        var obj = $(this).parents('tr').find('.prob');
        var prob_input = $(this).parents('tr').find('.msg_prob');
        if(state){
            obj.show().find('input').focus()
        }else{
            obj.hide();
            prob_input.val("");
        }
    })



    $('.select_all').click(function(){
        var state =  $(this).prop('checked');
        $('input[name="msg_id_list[]"]','.tab_msg_list').each(function(){
            $(this).prop('checked', state);
            var obj = $(this).parents('.checker').find('span');
            !state ? obj.removeClass('checked') : obj.addClass('checked');

            var prob_obj = $(this).parents('tr').find('.prob');
            var prob_input = $(this).parents('tr').find('.msg_prob');
            if(state){
                prob_obj.show()
            }else{
                prob_obj.hide();
                prob_input.val("");
            }
        });
    })


    $('.search_tag').click(function(){
        var val = $(".tag_val").val() || '';
        window.location.href="/infocenter/MessageGroup/add?id={$group_id}&flag="+val;

    })


})





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
    // "startDate": "{$smarty.now|date_format:'%Y-%m-%d %H:%M:%S'}",
    // "endDate": "{$smarty.now|date_format:'%Y-%m-%d %H:%M:%S'}",
    "opens": "right",
    "drops": "up",
    "buttonClasses": "btn btn-xs btn-sm btn-md btn-lg"
}, function(start, end, label) {
    $('#message_start').val(start.format('YYYY-MM-DD'));
    $('#message_end').val(end.format('YYYY-MM-DD'));
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



{if $group_data neq ''}
function getRegionTree() {
    var tree = [
    {foreach $region_tree as $continentCode=>$v }
        {
            text: "{$v.name}",
            type: "continent",
            code: '{$continentCode}',
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

{else}

function getRegionTree() {
    var tree = [
    {foreach $region_tree as $continentCode=>$v }
        {
            text: "{$v.name}",
            type: "continent",
            code: '{$continentCode}',
            color: "#000000",
            backColor: "#FFFFFF",
            href: "#node-1",
            selectable: false,
            state: {
                checked: false,
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
                    checked: false,
                }
            },
            {/foreach}
            ],
            liSelected:[]
        },
    {/foreach}
    ];
    return tree;
}



{/if}





function doSelStatus(k,v){
    console.log("doSelStatus:k="+k+",v="+v);
    $("#message_status").val(k); //0
    $("#message_status_str").val(v); // ALL
}
function ajax_save(group_data, msg_data) {
    $.ajax({
        url: '/infocenter/MessageGroup/update',
        type: 'POST',
        data: {
            "csrf_token" : '{$csrf_token}',
            "msg_data" : msg_data,
            "group_data" : JSON.stringify(group_data),
            "group_id" : $("#group_id").val()
        },
    }).done(function(data, txtStatus, xhr) {
        console.log("save success." + txtStatus);
        console.log(data);
        if (data.status == 'ok') {

            bootbox.alert({
              message: "操作成功！",
              callback: function(){
                window.location.href = '/infocenter/MessageGroup';
              }
            })

        } else if (data.status == 'error') {

            errors = data.errors.join('<br>');
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
        console.log("save request error :" + txtStatus);
        console.log(error);
    }).always(function(xhr, txtStatus) {
        console.log("save complete." + txtStatus);
    });
}

$("#btn_save").click(function(){
    var message_status = $('#message_status').val();
    var message = {
        "status"      : message_status,
        "name"        : $('#name').val(),

        "notification": $('#message_notification').is(':checked'),
        "popup"       : $('#message_popup').is(':checked'),

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

        if(key == 'appid' && (!val || val=='')){
            error_message.push('"APP ID" Cannot Empty!');
        }

        if ((key=='region' || key=='AS' || key=='EU' || key=='EE' || key=='NA' || key=='LA' || key=='OC' || key=='AF' || key=='UN') && val && val !='' && val != '0') {
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

{literal}
    var msg_data = [], error_msg = [], all_prob = 0;
    $('input[name="msg_id_list[]"]:checked','.tab_msg_list').each(function(){
        var msg_id = $(this).val();
        var prob_val = $(this).parents('tr').find('.msg_prob').val();
        if(!prob_val){
            error_msg.push('请填写消息id='+ msg_id + '出现的概率！');
            return false;
        }
        all_prob += Number(prob_val);
        msg_data.push({'id' : msg_id, 'prob' : prob_val});
    });
    if(message_status == '1'){

        if(msg_data.length == 0){
            error_msg.push('请选择要添加入组的消息！');
        }

        if(all_prob!=100){
            error_msg.push('选择添加入组的消息概率总和必须等于100% ！');
        }

        if(error_msg.length > 0){
            bootbox.alert({
                message: error_msg.join('<br>')
            });
            return false;
        }
    }
{/literal}
    ajax_save(message, msg_data);
});

$(function(){

    function addRegion(node) {
        var nodeId = node.nodeId;
        if (node.type == 'country') {
            var parrent = $('#region_tree').treeview('getParent', node);
            $('#region_tree').treeview('checkNode', [ parrent.nodeId, { silent: true } ]);
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
    }

    var $regionTree = $('#region_tree').treeview({
        data: getRegionTree(),
        levels: 2,
        multiSelect: true,
        highlightSelected: false,
        showCheckbox: true,
        checkedIcon: "glyphicon glyphicon-check",
        uncheckedIcon: "glyphicon glyphicon-unchecked",
        collapseIcon: "glyphicon glyphicon-minus",
       onNodeChecked: function(event, node) {
            addRegion(node);
        },
        onNodeUnchecked: function (event, node) {
            delRegion(node);
        }
    });
});


</script>
{/block}
