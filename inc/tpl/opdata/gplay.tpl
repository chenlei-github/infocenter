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
.star_mark{
    color:red;
}
.require_mark:after{
    color:red;
    content:'(*)';
}
</style>
<link href="//cdn.bootcss.com/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css" rel="stylesheet">
{/block}

{block name=content}
<div class="page-header">
  <h2>{$title}</h2>
</div>
<div class="row">
    <form action="/opdata/ajax/aso/updateGooglePlay" method="POST" role="form" class="form-horizontal">
        <input type="hidden" name="_method" value="POST">
        <input type="hidden" name="csrf_token" id="csrf_token" value="{$csrf_token}">

        {if $has_type}
        <input type="hidden" name="theme_type_id" id="theme_type_id" value="{$theme_type_id}">
        {else}
        <input type="hidden" name="theme_type_id" id="theme_type_id" value="">
        {/if}

        <div class="form-group">
            <div class="col-sm-10 col-md-1- col-sm-push-2 bg-info">
            <span class="text text-left text-default">The field marked by <span class="star_mark">'(*)'</span> should be filled in, others are optinal.</span>
            </div>
        </div>

        <legend>Edit Play Description</legend>

        <div class="form-group">
            <label for="theme_types" class="col-sm-2 col-md-2 control-label require_mark">Type:</label>
            {if $has_type}
                <div class="col-sm-4 col-md-4">Current:&nbsp;
                <span class="btn btn-info">{$theme_type_name}</span>
                <span>Or</span>
                </div>
            {/if}
            <div class="col-sm-4 col-md-4 dropdown">
                <button class="btn btn-default dropdown-toggle" type="button" id="theme_types" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                    Please select a Type
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu">
                    {foreach from=$theme_types  key=type_id  item=type_name}
                        <li><a href="/opdata/Aso/updateGooglePlay?type={$type_id}">{$type_name}</a></li>
                    {/foreach}
                </ul>
            </div>
        </div>

    {if $has_type}

        <div class="form-group">
            <label for="play_account" class="col-sm-2 col-md-2 control-label require_mark">Play Account</label>
            <div class="col-sm-3 col-md-3">
                <select id="play_account" class="form-control" required="required">
                    <option value="">Please select account</option>
                    {foreach from=$play_account_list key=account_name item=account}
                        <option value="{$account_name}">{$account_name}</option>
                    {/foreach}
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="package_names" class="col-sm-2 col-md-2 control-label require_mark">Packages</label>
            <div class="col-sm-6 col-md-6">
                <select id="package_names" class="form-control" multiple data-actions-box="true">
                    <!-- <option value="">Please select package_names</option> -->
                    {foreach from=$packages item=package}
                        <option value="{$package['package_name']}">{$package['package_name']}  |  {$package['name']}</option>
                    {/foreach}
                </select>
            </div>
        </div>
        <hr>
        <div class="form-group">
            <label class="col-sm-2 col-md-2 control-label">Ready Text</label>
            <div class="col-sm-8 col-md-8">
                <span class="btn btn-info" id="btn_selectText">Select one</span>
                <label for="current_text">current text name:</label>
                <input type="text" id="current_text" class="" value="new">
                <input type="hidden" id="current_text_id" value="0">
                <span class="btn btn-primary" id="btn_saveText">Save current text</span>
                <span class="btn btn-danger" id="btn_deleteText">Delete Text</span>
            </div>
        </div>
        <div class="form-group">
            <label for="languages" class="col-sm-2 col-md-2 control-label">Language</label>
            <div class="col-sm-4 col-md-4">
                <select id="language_unadd" class="form-control">
                    <option class='default' value="" selected>Please select language...</option>
                    {foreach from=$languages key=lang_code item=lang_name}
                        <option id="option_language_{$lang_code}"  class="opt_language" value="{$lang_code}">{$lang_name}({$lang_code})</option>
                    {/foreach}
                </select>
            </div>
            <span class="btn btn-danger" id="deleteLanguage">delete</span>
        </div>

        <div class="form-group" >
            <input type="hidden" id="current_language" value="en-US">
            <div class="col-sm-10 col-md-10 col-sm-push-2" id="lang_group">
                <!-- <span class="btn btn-success button_language" id="button_language_en_US">en_US</span> -->
            </div>
        </div>

        <div class="form-group">
            <label for="res_title" class="col-sm-2 col-md-2 control-label">Title</label>
            <div class="col-sm-8 col-md-8">
                <input type="text" class="form-control field" id="res_title" placeholder="title" value="" required>
            </div>
        </div>

        <div class="form-group">
            <label for="shortDescription" class="col-sm-2 col-md-2 control-label">Short Description</label>
            <div class="col-sm-8 col-md-8">
                <textarea id="shortDescription" class="form-control field" rows="2" placeholder="shortDescription" required></textarea>
            </div>
        </div>

        <div class="form-group">
            <label for="fullDescription" class="col-sm-2 col-md-2 control-label">Full Description</label>
            <div class="col-sm-8 col-md-8">
                <textarea id="fullDescription" class="form-control field" rows="20" placeholder="fullDescription" required></textarea>
            </div>
        </div>

        <div class="form-group">
            <div class="col-sm-offset-2 col-md-offset-2 col-sm-8 col-md-8">
                <button type="button" class="btn btn-primary" id="btn_save">Publish</button>
                <button type="button" class="btn btn-danger" id="btn_stop">Cancel</button>
            </div>
        </div>

    {/if}

        <hr>

        <div class="form-group">
            <label for="level" class="col-sm-2 col-md-2 control-label">进度:</label>
            <div class="col-sm-10">
                <!-- <a href="/opdata/AsoKeywords/results"><span class="btn btn-info">ViewResults</span></a> -->
                <br>
                <ul id='file_list' class="ul">
                </ul>
            </div>
        </div>

        <div class="form-group">
            <label for="log" class="col-sm-2 col-md-2 control-label">Infomation:</label>
            <div class="col-sm-8 col-md-8">
                <textarea id="log" class="form-control field" rows="20" placeholder="" required></textarea>
            </div>
        </div>

        <div class="form-group results_div">
            <label for="log" class="col-sm-2 col-md-2 control-label">Results:</label>
            <div class="col-sm-8 col-md-8">
                <span class="btn btn-default" id="view_all">All</span>
                <span class="btn btn-success" id="view_success">Success</span>
                <span class="btn btn-danger" id="view_error">Error</span>
                <span class="btn btn-warning" id="view_warning">Warning</span>
            </div>
            <br>
            <br>
            <div class="col-sm-8 col-md-8 col-sm-push-2 col-md-push-2" id="results_table">
<!--                 <table>

                </table> -->
            </div>
        </div>
    </form>
</div>
{/block}

{block name=javascript append}
<script src="//cdn.bootcss.com/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.min.js"></script>
<script type="text/javascript">
$(document).ready(function() {
    $('#package_names').multiselect({
        enableFiltering: true,
        includeSelectAllOption: true,
        selectAllJustVisible: true,
        maxHeight: 500,
    });
});
</script>

<script>

function ajax_save(data) {
    $('#btn_save').html('Publishing...');

    $.ajax({
        url: '/opdata/ajax/aso/startGPlayTask',
        type: 'POST',
        async: true,
        // cache: false,
        // contentType:'application/json',
        // dataType: 'json',
        data: {
            "csrf_token":'{$csrf_token}',
            "task_type": 'gplay_text',
            "data":JSON.stringify(data)
        },
    }).done(function(data, txtStatus, xhr) {
        $('#btn_save').html('Publish');
        console.log(data);
        if (data.status == 'ok') {
            console.log("save success." + txtStatus);
            var messages = " " + data.messages.join('<br>');
            var errors = " " + data.errors.join('<br>');
            messages += "<br>errors:<br>" + errors;
            bootbox.alert({
                title:'success',
                message: messages
            });
        } else if (data.status == 'error') {
            var messages = " " + data.messages.join('<br>');
            var errors = " " + data.errors.join('<br>');
            errors += "<br> messages:<br>" + messages;
            console.log("errors:"+errors);
            bootbox.alert({
                title:data.message,
                message:errors
            });
        }
    }).fail(function(xhr, txtStatus, error) {
        console.log("request error :" + txtStatus);
        console.log(error);
    }).always(function(xhr, txtStatus) {
        console.log("complete." + txtStatus);
    });
}

$("#btn_save").click(function(){
    // changeLanguage('en_US');
    if (!$('#play_account').val()) {
        bootbox.alert('Please select a Play Account!');
        return false;
    }

    if (!$('#package_names').val()) {
        bootbox.alert('Please select packages!');
        return false;
    }

    if (!Trans || Object.keys(Trans).length < 1) {
        bootbox.alert('Please fill data');
        return false;
    }
    var res = {
        "play_account"  : $('#play_account').val(),
        "package_names" : $('#package_names').val(),
        "languages"     : Object.keys(Trans),
        "res"           : Trans,
        "type"          : $('#theme_type_id').val(),
    };
    var confirmHTML = '';
    console.log(res);
    for (k in res) {
        if (res.hasOwnProperty.call(res, k)) {
            if (typeof res[k] == 'String' || typeof res[k] == 'Number') {
                confirmHTML += '<p class="row"><span class="col-sm-2"><strong>' + k + '</strong></span><span class="col-sm-6 col-sm-push-2">' + res[k] + '</span></p>';
            } else if (res[k] instanceof Array) {
                confirmHTML += '<p class="row"><span class="col-sm-2"><strong>' + k + ' count:</strong></span><span class="col-sm-6 col-sm-push-2">' + res[k].length + '</span></p>';
            } else if (k == 'res') {
                confirmHTML += '<p class="row"><span class="col-sm-2"><strong>' + k + ' count:</strong></span><span class="col-sm-6 col-sm-push-2">' + Object.keys(res[k]).length + '</span></p>';
            } else {
                confirmHTML += '<p class="row"><span class="col-sm-2"><strong>' + k + ' count:</strong></span><span class="col-sm-6 col-sm-push-2">' + res[k] + '</span></p>';
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
                label: "Publish",
                className: "btn-primary",
                callback: function() { ajax_save(res); }
            },
            cancel: {
                label: "Cancel",
                className: "btn-default",
                callback: function() {}
            },
        }
    }); // end dialog
}); //end save


{literal}

var Trans = {};

function changeLanguage(lang){

    var cur_lang = $('#current_language').val();
    console.log("cur_lang=" + cur_lang);
    console.log("changeLanguage:lang=" + lang);

    //切换旧值
    $('#res_title').val('');
    $('#shortDescription').val('');
    $('#fullDescription').val('');
    console.log("Trans="+JSON.stringify(Trans));
    console.log("res_title="+$('#res_title').val());
    console.log("shortDescription="+$('#shortDescription').val());
    console.log("fullDescription="+$('#fullDescription').val());
    if (Trans[lang] != null){
        var title            = Trans[lang]['title'];
        var shortDescription = Trans[lang]['shortDescription'];
        var fullDescription  = Trans[lang]['fullDescription'];
        if (title) {
            $('#res_title').val(title);
        }
        if (shortDescription) {
            $('#shortDescription').val(shortDescription);
        }
        if (fullDescription) {
            $('#fullDescription').val(fullDescription);
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

    return true;
}

function updateTrans(lang) {
    if (lang != '' || lang != null) {
        var title = $('#res_title').val();
        var shortDescription = $('#shortDescription').val();
        var fullDescription = $('#fullDescription').val();

        if (Trans[lang] == null){
            Trans[lang] = {};
        }

        if (title.length > 30) {
            bootbox.alert('Title is too long! (max:30)');
            return false;
        }

        if (shortDescription.length > 80) {
            bootbox.alert('Short Description is too long! (max:80)');
            return false;
        }

        if (fullDescription.length > 4000) {
            bootbox.alert('Full Description is too long! (max:4000)');
            return false;
        }

        Trans[lang]['title'] = title;
        Trans[lang]['shortDescription'] = shortDescription;
        Trans[lang]['fullDescription'] = fullDescription;

        window.localStorage.setItem('Trans', JSON.stringify(Trans));
        return true;
    }
    return false;
}

//删除

$('#deleteLanguage').click(function(){
    var cur_lang = $('#current_language').val();
    console.log("$deleteLanguage:cur_lang="+cur_lang);
    if (!cur_lang) {
        return false;
    }

    delete(Trans[cur_lang]);

    $('#button_language_'+cur_lang).remove();

    $('#option_language_'+cur_lang).show();

    // 选择下一个button
    var arr = $('.button_language');
    var next = arr[arr.length-1].id.substr('button_language_'.length);
    return changeLanguage(next);
});


$('#language_unadd').change(function(){
    var lang = $(this).val();
    $('#option_language_'+lang).hide();

    addButton(lang);
    // var btn = $('<span id="button_language_'+lang+'">'+lang+'</span>')
    //         .addClass('btn button_language')
    //         .click(function(){
    //             if (updateTrans($('#current_language').val())) {
    //                 return changeLanguage(lang);
    //             }
    //         });
    // $('#lang_group').append(btn);

    changeLanguage(lang);
});

$('.field').change(function(){
    var cur_lang = $('#current_language').val();
    if (cur_lang != '' || cur_lang != null || cur_lang != 'default') {
        updateTrans(cur_lang);
    }
});

// initLanguage();

function addButton(lang){
    var btn = $('<span id="button_language_'+lang+'">'+lang+'</span>')
        .addClass('btn btn-default button_language')
        .click(function(){
            console.log("button : you click:"+lang);
            return changeLanguage(lang);
        });
    $('#lang_group').append(btn);
}

function initTranslation(translation){
    var t;

    if (!translation) {
        t = JSON.parse(window.localStorage.getItem('Trans'));
    } else {
        t = translation;
    }

    console.log("init:Transtlation="+JSON.stringify(t));
    if (!t) {
        return false;
    }
    Trans = t;
    var languageList = Object.keys(Trans);

    $('#lang_group').children().remove();
    $('.opt_language').show();
    for (var lang of languageList) {
        console.log("addButtons:lang"+lang);
        addButton(lang);
        $('#option_language_'+lang).hide();
    }
    // changeLanguage('en_US');
}
initTranslation();

{/literal}

$('#btn_selectText').click(function(){
    ajax_get_gplay_text_list();
});

$('#btn_saveText').click(function(){
    var data = {
        "id"   : $('#current_text_id').val(),
        "name" : $('#current_text').val(),
        "type" : $('#theme_type_id').val(),
        "data" : Trans
    };
    console.log(data);
    ajax_save_text(data);
});

$('#btn_deleteText').click(function(){
    var data = {
        "id"   : $('#current_text_id').val(),
        "name" : $('#current_text').val(),
        "data" : Trans
    };
    console.log(data);
    ajax_delete_text(data.id);
});

function ajax_get_gplay_text_list() {
    $.ajax({
        url: '/opdata/ajax/aso/getGPlayTextList',
        type: 'POST',
        async: true,
        // cache: false,
        // contentType:'application/json',
        // dataType: 'json',
        data: {
            "csrf_token":'{$csrf_token}',
            "type" : $('#theme_type_id').val(),
            // "id"   : data.id,
            // "name" : data.name,
            // "data" : JSON.stringify(data.data)
        },
    }).done(function(data, txtStatus, xhr) {
        // $('#btn_saveText').html('Save Current Text');
        console.log(data);
        if (data.status == 'ok') {
            console.log("get success." + txtStatus);
            show_gplay_text_list(data.data);
        } else if (data.status == 'error') {
            var messages = "";
            if (data.messages) {
                messages += data.messages.join('<br>');
            }
            var errors = "";
            if (data.errors) {
                errors += data.errors.join('<br>');
            }
            errors += "<br> messages:<br>" + messages;
            console.log("errors:"+errors);
            bootbox.alert({
                title:data.message,
                message:errors
            });
        }
    }).fail(function(xhr, txtStatus, error) {
        console.log("request error :" + txtStatus);
        console.log(error);
    }).always(function(xhr, txtStatus) {
        console.log("complete." + txtStatus);
    });
}

function show_gplay_text_list(data) {
    console.log('show gplay text list : ');
    console.log(data);
    readyText = data;
    var $gplayText = $('<div class="table-responsive"></div>');
    $table = $('<table class="table table-hover table-striped"></table>');
    $thead = $('<tr></tr>');
    $thead.append('<th>id</th>');
    $thead.append('<th>name</th>');
    $thead.append('<th>languages:</th>');
    $thead.append('<th>created_at</th>');
    $thead.append('<th>updated_at</th>');
    $table.append($thead);
    for (var i in data) {
        console.log(data[i]);
        // var languages = '';
        // var textItem = JSON.parse(data[i].data);
        var textItem = data[i].data;
        if (textItem) {

            data[i].languages = 'Total:'+ Object.keys(textItem).length + '  list:';
            data[i].languages += Object.keys(textItem).join(',');
            // for (var lang in textItem) {
            //     data[i].languages += lang + ',';
            // }
        } else {
            data[i].languages = 'unknown error';
        }
        var $row = $('<tr class="text_row text_' + data[i].id + '" data-index="'+i+'" data-id="' + data[i].id + '"></tr>');
        $row.append('<td class="text_id" data-id="' + data[i].id +' ">'+data[i].id+'</td>');
        $row.append('<td class="text_name">'+data[i].name+'</td>');
        $row.append('<td>'+data[i].languages+'</td>');
        $row.append('<td>'+data[i].created_at+'</td>');
        $row.append('<td>'+data[i].updated_at+'</td>');
        $table.append($row);

        // $('tr.text_row.text_'+i).click(function() {
        $row.click(function() {
            var i = $(this).data('index');
            // console.log($(this).data('id'));
            console.log('select text: '+ readyText[i].name);
            initTranslation(readyText[i].data);
            $('#current_text').val(readyText[i].name);
            $('#current_text_id').val(readyText[i].id);
            readyTextBox.modal('hide');
        });
    } // end for
    $gplayText.append($table);
    readyTextBox = bootbox.dialog({
        message: $gplayText,
        title: "Please select...",

        size: 'large',

        show: true,
        backdrop: true,
        closeButton: true,
        animate: true,
        className: "my-modal",

        buttons: {
            save: {
                label: "Ok",
                className: "btn-primary",
                callback: function() { console.log('ok!');}
            },
            cancel: {
                label: "Cancel",
                className: "btn-default",
                callback: function() { console.log('cancel!');}
            },
        }
    }); // end dialog
}

function ajax_save_text(data) {
    $('#btn_saveText').html('saving...');

    $.ajax({
        url: '/opdata/ajax/aso/saveGPlayText',
        type: 'POST',
        async: true,
        // cache: false,
        // contentType:'application/json',
        // dataType: 'json',
        data: {
            "csrf_token":'{$csrf_token}',
            "id"   : data.id,
            "name" : data.name,
            "type" : data.type,
            "data" : JSON.stringify(data.data)
        },
    }).done(function(data, txtStatus, xhr) {
        $('#btn_saveText').html('Save Current Text');
        console.log(data);
        if (data.status == 'ok') {
            console.log("save success." + txtStatus);
            var messages = "";
            if (data.messages) {
                messages += data.messages.join('<br>');
            }
            var errors = "";
            if (data.errors) {
                errors += data.errors.join('<br>');
            }
            $('#current_text_id').val(data.id);
            messages += "<br>errors:<br>" + errors;
            bootbox.alert({
                title:'success',
                message: messages
            });
        } else if (data.status == 'error') {
            var messages = "";
            if (data.messages) {
                messages += data.messages.join('<br>');
            }
            var errors = "";
            if (data.errors) {
                errors += data.errors.join('<br>');
            }
            errors += "<br> messages:<br>" + messages;
            console.log("errors:"+errors);
            bootbox.alert({
                title:'Fail',
                message:errors
            });
        }
    }).fail(function(xhr, txtStatus, error) {
        console.log("request error :" + txtStatus);
        console.log(error);
    }).always(function(xhr, txtStatus) {
        console.log("complete." + txtStatus);
    });
}


function ajax_delete_text(id) {
    $('#btn_deleteText').html('deleting...');

    $.ajax({
        url: '/opdata/ajax/aso/deleteGPlayText',
        type: 'POST',
        async: true,
        // cache: false,
        // contentType:'application/json',
        // dataType: 'json',
        data: {
            "csrf_token": '{$csrf_token}',
            "id"        : id
        },
    }).done(function(data, txtStatus, xhr) {
        $('#btn_deleteText').html('Delete Text');
        console.log(data);
        if (data.status == 'ok') {
            console.log("delete success." + txtStatus);
            var messages = "";
            if (data.messages) {
                messages += data.messages.join('<br>');
            }
            var errors = "";
            if (data.errors) {
                errors += data.errors.join('<br>');
            }
            $('#current_text_id').val(0);
            messages += "<br>errors:<br>" + errors;
            bootbox.alert({
                title:'success',
                message: messages
            });
        } else if (data.status == 'error') {
            var messages = "";
            if (data.messages) {
                messages += data.messages.join('<br>');
            }
            var errors = "";
            if (data.errors) {
                errors += data.errors.join('<br>');
            }
            errors += "<br> messages:<br>" + messages;
            console.log("errors:"+errors);
            bootbox.alert({
                title:data.message,
                message:errors
            });
        }
    }).fail(function(xhr, txtStatus, error) {
        console.log("request error :" + txtStatus);
        console.log(error);
    }).always(function(xhr, txtStatus) {
        console.log("complete." + txtStatus);
    });
}

$('#btn_stop').click(function() {

    $.ajax({
        url: '/opdata/ajax/aso/stopGplayTask',
        type: 'POST',
        async: true,
        data: {
            "csrf_token":'{$csrf_token}',
            "task_type": 'gplay_text',
        },
    })
    .done(function(data){
        bootbox.alert(data.message);
        refresh_status();
    })
    .fail(function(data){
        bootbox.alert('NetWork Fail.');
    });
});

$(function() {
    refresh_status();
    var it = setInterval(refresh_status, 5000);
});

function refresh_status()
{
    $.ajax({
        url: '/opdata/ajax/aso/getGplayTaskStatus',
        type: 'POST',
        async: true,
        data: {
            "csrf_token":'{$csrf_token}',
            "task_type": 'gplay_text',
        },
    })
    .done(function(data) {
        console.log("refresh_status:success");
        console.log(data);
        if (data.status == 'ok') {
            var file_list = $('#file_list');
            file_list.empty();
            if (data.running) {
                file_list.append('<li>running</li>');
                file_list.append('<li>PID:' + data.pid + '</li>');
                $('#btn_stop').show();
                $('#btn_save').hide();
                $('#results_div').hide();
            } else {
                file_list.append('<li>not running</li>');
                $('#btn_stop').hide();
                $('#btn_save').show();
                $('#results_div').show();
                if (!window.results_list) {
                    view_results('');
                }
            }
            if (data.log) {
                $('#log').val(data.log);
            }

        }
    })
    .fail(function() {
        console.log("refresh_status:error");
    });
}

function getResultsList(tag) {
    $.ajax({
        url: '/opdata/ajax/aso/getGplayTaskResults',
        type: 'POST',
        async: true,
        data: {
            "csrf_token":'{$csrf_token}',
            "task_type": 'gplay_text',
        },
    }).done(function(data) {
        console.log(data);
        if (data.status == 'ok') {
            window.results_list = data.results;
            view_results_intranal(tag);
        } else {
            window.results_list = null;
            bootbox.alert('Get Results Fail! ' + data.message);
        }
    }).fail(function(data) {
        console.log('getResultsList:FAIL!');
        bootbox.alert("get results list fail.");
    });
}


function view_results(tag) {
    if (!window.results_list) {
        getResultsList(tag);
    } else {
        view_results_intranal(tag);
    }
}

function view_results_intranal(tag) {


    var results_table = $('#results_table');
    results_table.empty();

    var e = '<table class="table">';
    e += '<tr><th>package</th><th>language</th><th>status</th><th>message</th><tr>';
    console.log(window.results_list);
    for (index = 0, len = window.results_list.length; index < len; ++index) {
        var msg = window.results_list[index];
        console.log("msg:");
        console.log(msg);
        console.log('tag:');
        console.log(tag);
        if ( tag !='' && msg.status != tag ) {
                continue;
        }
        e += '<tr><td>' + msg.package + '</td><td>' + msg.lang + '</td><td>' +
            msg.status + '</td><td>' + msg.message + '</td></tr>';
    }
    e += '</table>';
    console.log(e);
    results_table.html(e);
}


$(function(){
    $('#view_all').click(function() {
        view_results('');
    });
    $('#view_error').click(function() {
        view_results('error');
    });
    $('#view_success').click(function() {
        view_results('success');
    });
    $('#view_warning').click(function() {
        view_results('warning');
    });
});


</script>
{/block}
