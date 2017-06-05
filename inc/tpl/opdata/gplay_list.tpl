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
        <div class="form-group">
            <div class="col-sm-10 col-md-1- col-sm-push-2 bg-info">
            <span class="text text-left text-default">The field marked by <span class="star_mark">'(*)'</span> should be filled in, others are optinal.</span>
            </div>
        </div>

        <legend>Query Google Play Description</legend>
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
            <label for="languages" class="col-sm-2 col-md-2 control-label">Language</label>
            <div class="col-sm-4 col-md-4">
                <select id="language_unadd" class="form-control">
                    <option class='default' value="" selected>Please select language...</option>
                    {foreach from=$languages key=lang_code item=lang_name}
                        <option id="option_language_{$lang_code}"  value="{$lang_code}">{$lang_name}({$lang_code})</option>
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

        <!-- <div class="form-group">
            <label for="res_title" class="col-sm-2 col-md-2 control-label require_mark">Title</label>
            <div class="col-sm-8 col-md-8">
                <input type="text" class="form-control field" id="res_title" placeholder="title" value="" required>
            </div>
        </div>

        <div class="form-group">
            <label for="shortDescription" class="col-sm-2 col-md-2 control-label require_mark">Short Description</label>
            <div class="col-sm-8 col-md-8">
                <textarea id="shortDescription" class="form-control field" rows="2" placeholder="shortDescription" required></textarea>
            </div>
        </div>

        <div class="form-group">
            <label for="fullDescription" class="col-sm-2 col-md-2 control-label require_mark">Full Description</label>
            <div class="col-sm-8 col-md-8">
                <textarea id="fullDescription" class="form-control field" rows="20" placeholder="fullDescription" required></textarea>
            </div>
        </div> -->

        <div class="form-group">
            <div class="col-sm-offset-2 col-md-offset-2 col-sm-8 col-md-8">
                <button type="button" class="btn btn-primary" id="btn_get">Get</button>
                <button type="button" class="btn btn-default" id="btn_cancel">Cancel</button>
            </div>
        </div>

        <div id="listings"></div>

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

function ajax_getList(data) {
    $('#btn_get').html('waiting...');

    $.ajax({
        url: '/opdata/ajax/aso/getGooglePlayDescriptionList',
        type: 'POST',
        async: true,
        // cache: false,
        // contentType:'application/json',
        // dataType: 'json',
        data: {
            "csrf_token":'{$csrf_token}',
            "data":JSON.stringify(data)
        },
    }).done(function(data, txtStatus, xhr) {
        $('#btn_get').html('Get');
        console.log(data);
        console.log("get done. " + txtStatus);
        var messages = " " + data.messages.join('<br>');
        var errors = " " + data.errors.join('<br>');
        messages += "<br>errors:<br>" + errors;
        bootbox.alert({
            title:'Get done.',
            message: messages
        });

        var listings = '';
        if (data.list) {
            for (var package in data.list) {
                var l = '<hr><div class="listing"><h5>'+package+'</h5><hr>';
                for (var lang in data.list[package]) {
                    var listing = data.list[package][lang];
                    var p = "<p><b>package:</b>";
                    p += package;
                    p += '<br><b>language:</b>';
                    p += listing['language'];
                    p += '<br><b>title:</b>';
                    p += listing['title'];
                    p += '<br><b>shortDescription:</b>';
                    p += listing['shortDescription'];
                    p += '<br><b>fullDescription:</b>';
                    p += listing['fullDescription'];
                    p += '<br><b>video:</b>';
                    p += listing['video'];
                    p += '</p>';
                    l += p;
                }
                l += '</div>';
                listings += l;
            }
        }
        $('#listings').html(listings);

    }).fail(function(xhr, txtStatus, error) {
        console.log("request error :" + txtStatus);
        console.log(error);
    }).always(function(xhr, txtStatus) {
        console.log("complete." + txtStatus);
    });
}

$("#btn_get").click(function(){
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
        "res"           : Trans
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
                label: "Get",
                className: "btn-primary",
                callback: function() { ajax_getList(res); }
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
            // bootbox.alert('Title is too long! (max:30)');
            // return false;
        }

        if (shortDescription.length > 80) {
            // bootbox.alert('Short Description is too long! (max:80)');
            // return false;
        }

        if (fullDescription.length > 4000) {
            // bootbox.alert('Full Description is too long! (max:4000)');
            // return false;
        }

        Trans[lang]['title'] = title;
        Trans[lang]['shortDescription'] = shortDescription;
        Trans[lang]['fullDescription'] = fullDescription;
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

    var btn = $('<span id="button_language_'+lang+'">'+lang+'</span>')
            .addClass('btn button_language')
            .click(function(){
                if (updateTrans($('#current_language').val())) {
                    return changeLanguage(lang);
                }
            });
    $('#lang_group').append(btn);

    changeLanguage(lang);
});

$('.field').change(function(){
    var cur_lang = $('#current_language').val();
    if (cur_lang != '' || cur_lang != null || cur_lang != 'default') {
        updateTrans(cur_lang);
    }
});

$('#button_language_en_US').click(function(){
    if (updateTrans($('#current_language').val())) {
        return changeLanguage('en_US');
    }
});

$('#button_language_en_US').click();

// initLanguage();

function addButton(lang){
    var btn = $('<span id="button_language_'+lang+'">'+lang+'</span>')
        .addClass('btn btn-default button_language')
        .click(function(){
            console.log("button : your click:"+lang);
            return changeLanguage(lang);
        });
    $('#lang_group').append(btn);
    $('#option_language_'+lang).hide();
}

function addButtons(){
    console.log("init:Transtlation="+JSON.stringify(Trans));
    if (Trans == null) {
        return false;
    }
    var languageList = Object.keys(Trans);
    for (var lang of languageList) {
        console.log("addButtons:lang"+lang);
        addButton(lang);
    }
    // changeLanguage('en_US');
}
addButtons();

{/literal}

</script>
{/block}
