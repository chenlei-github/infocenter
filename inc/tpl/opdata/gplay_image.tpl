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
<link rel="stylesheet" href="https://cdn.bootcss.com/blueimp-file-upload/9.12.5/css/jquery.fileupload.min.css" integrity="sha384-/cEZgEA00SiCs/3Xr4k0NQ9Ah+0JV4Erxn3BiUOTd54a+3lUvM1GUESDouZY3rbe" crossorigin="anonymous">
{/block}

{block name=content}
<div class="page-header">
  <h2>{$title}</h2>
</div>
<div class="row">
    <form class="form-horizontal">
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

        <legend>Update Play Image </legend>

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
                        <li><a href="/opdata/Aso/updateGooglePlayImage?type={$type_id}">{$type_name}</a></li>
                    {/foreach}
                </ul>
            </div>
        </div>

    {if $has_type}

        <!-- play account -->
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

        <!-- package name -->
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

        <!-- language -->
        <div class="form-group">
            <label for="languages" class="col-sm-2 col-md-2 control-label require_mark">Language</label>
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
            <input type="hidden" id="current_language" value="en_US">
            <div class="col-sm-10 col-md-10 col-sm-push-2" id="lang_group">
                <!-- <span class="btn btn-success button_language" id="button_language_en_US">en_US</span> -->
            </div>
        </div>

        <!-- select imageType -->
        <div class="form-group">
            <label for="image_types" class="col-sm-2 col-md-2 control-label require_mark">Image Type</label>
            <div class="col-sm-6 col-md-6">
                <select id="image_types" class="form-control" required="required">
                    <!-- <option value="">Please select package_names</option> -->
                    {foreach from=$image_types item=types}
                        <option value="{$types}">{$types}</option>
                    {/foreach}
                </select>
            </div>
            <div class="col-sm-2 col-md-2 checkbox" id="keep_first_image_checkbox">
                <input type="checkbox" id="keep_first_image" value="0">
                <span class="text-info">Keep First Image</span>
            </div>
        </div>

        <!-- upload image  -->
        <div class="form-group">
            <label for="upload_image" class="col-sm-2 control-label require_mark">Image</label>
<!--             <div class="col-sm-7">
                  <input type="text" class="form-control" id="upload_image" name="upload_image" value="" placeholder="">
            </div> -->
            <div class="col-sm-2 col-md-2 ">
                <span class="btn btn-success fileinput-button">
                    <i class="glyphicon glyphicon-plus"></i>
                    <span>Add files...</span>
                    <input id="image_fileinput" class="fileinput" type="file" name="images[]" >
                </span>
            </div>
            <div class="col-sm-3 col-md-3 col-sm-push-2">
                <span class="btn btn-danger" id='del_all_img'> Delete All Image </span>
            </div>
        </div>
        <div class="form-group hidden">
            <div class="col-sm-10 col-md-10 col-sm-push-2">
                <div id="image_progress" class="progress">
                    <div id="image_progress_bar" class="progress-bar progress-bar-success progress-bar-striped active" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>
        </div>

        <!-- image preview -->
        <div class="row image_list">
<!--             <div class="row form-group">
                <div class="col-sm-10">
                    <img src="" class="img-responsive" alt="Responsive image">
                </div>
                <div class="col-sm-2">
                    <button class="btn btn-danger">Delete This</button>
                </div>
            </div> -->
        </div>

        <!-- submit -->
        <div class="form-group">
            <div class="col-sm-offset-2 col-md-offset-2 col-sm-8 col-md-8">
                <button type="button" class="btn btn-primary" id="btn_save">Publish</button>
                <button type="button" class="btn btn-danger" id="btn_stop">Cancel</button>
            </div>
        </div>

    {/if}

        <hr>
        <div class="form-group">
            <label for="file_list" class="col-sm-2 col-md-2 control-label">进度:</label>
            <div class="col-sm-10">
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

<!-- Modal -->
<div id="Modal" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4  ><label id="ModalTitle" class="modal-title" ></label></h4>
      </div>
      <div class="modal-body">
        <p><label   id="ModalMsg"  class="text-cent"></label></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

{/block}

{block name=javascript append}
<script src="//cdn.bootcss.com/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.min.js"></script>
<script src="//cdn.bootcss.com/blueimp-file-upload/9.12.5/js/vendor/jquery.ui.widget.min.js" integrity="sha384-nGh8khjBjCD80rGfjnQZ+72Y3wYz0HHym3jC0kf9g1xVni73kthZ9fxtSIwjZ6zQ" crossorigin="anonymous"></script>
<script src="https://blueimp.github.io/JavaScript-Load-Image/js/load-image.all.min.js" ></script>
<script src="https://blueimp.github.io/JavaScript-Canvas-to-Blob/js/canvas-to-blob.min.js" ></script>

<script src="//cdn.bootcss.com/blueimp-file-upload/9.12.5/js/jquery.iframe-transport.min.js" integrity="sha384-L1OhlVcFQPXKloYQlEFOQQpRFoMgM8kecftiV5yLQV0Cze6jK4myjnX2HHHw1yD+" crossorigin="anonymous"></script>
<script src="//cdn.bootcss.com/blueimp-file-upload/9.12.5/js/jquery.fileupload.min.js" integrity="sha384-4fwO+NFkS0gdgfgIPTDvbaaD9n15lf3aA15hdn20LwTDN/QoEccsz5Tic+ncA8N9" crossorigin="anonymous"></script>
<script src="//cdn.bootcss.com/blueimp-file-upload/9.12.5/js/jquery.fileupload-process.min.js" integrity="sha384-wfPQHTx+v7NrjQ0cU/M5aZyd5A7SYTMNNRTtRWtuGcbJ+DEgtfKfD4Yi4J0h5NU5" crossorigin="anonymous"></script>

<script src="//cdn.bootcss.com/blueimp-file-upload/9.12.5/js/jquery.fileupload-image.min.js" integrity="sha384-5jnqQ1fz4Go1dWmEciZRgRte357rUBTqgpOiTufbt3A9RlBssNXmYV9ebiGKejs7" crossorigin="anonymous"></script>
<script src="//cdn.bootcss.com/blueimp-file-upload/9.12.5/js/jquery.fileupload-validate.min.js" integrity="sha384-N1ZqdkCijnsLxYtlvOpZ4eVbFu4p5ME6UrPnTxKHoHVXJHLY5C5VXvj3edi4U0eX" crossorigin="anonymous"></script>
<!-- <script type="text/javascript" src="https://rawgit.com/dankogai/js-base64/master/base64.min.js"></script> -->

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
    $('#btn_save').html('Update...');

    $.ajax({
        url: '/opdata/ajax/aso/startGPlayTask',
        type: 'POST',
        async: true,
        // cache: false,
        // contentType:'application/json',
        // dataType: 'json',
        data: {
            "csrf_token":'{$csrf_token}',
            "task_type": 'gplay_image',
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
            window.results_list = null;
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

$(function(){
    $('#keep_first_image_checkbox').hide();
});

$('#image_types').change(function(event) {
    var val = $(this).val();
    if (val === 'phoneScreenshots') {
        $('#keep_first_image_checkbox').show();
    } else {
        $('#keep_first_image_checkbox').hide();
    }
});


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

    var languages = [];
    $('.button_language').each(function() {
        var lang = $(this).text();
        if (lang !== '') {
            languages.push(lang);
        }
    });

    if (languages.length<1) {
        bootbox.alert('Please select a language!');
        return false;
    }

    var image_types = $('#image_types').val();

    var images = [];
    $('.image_list img').each(function() {
        var url = $(this).attr('src');
        images.push(url);
    });
    if (images.length<1) {
        bootbox.alert('Please Upload at lest one image!');
        return false;
    }

    var res = {
        "play_account"  : $('#play_account').val(),
        "package_names" : $('#package_names').val(),
        "languages"     : languages,
        "image_type"    : image_types,
        "images"        : images,
        "type"          : $('#theme_type_id').val(),
        "keep_first_image": $('#keep_first_image').is(":checked"),
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

// @begin language

current_language = '';

function addLangButton(lang) {
    var btn = $('<span id="button_language_'+lang+'">'+lang+'</span>')
        .addClass('btn btn-default button_language')
        .click(function(){
            console.log("button : you click:"+lang);
            changeLang(lang);
        });
    if (current_language === '') {
        btn.addClass('btn-success');
        current_language = lang;
    }
    $('#option_language_'+lang).hide();
    $('#lang_group').append(btn);
}

function delLangButton(lang) {
    $('#button_language_' + lang).remove();
    $('#option_language_' + lang).show();
    var lang = $('#lang_group > .button_language').last().text();
    changeLang(lang);
}

function changeLang(lang) {
    if (current_language !== '') {
        $('#button_language_' + current_language).removeClass('btn-success');
    }
    $('#button_language_' + lang).addClass('btn-success');
    current_language = lang;
}


$(function() {
    $('#deleteLanguage').click(function() {
        if (current_language !== '') {
            delLangButton(current_language);
        }
    });
    $('#language_unadd').change(function() {
        var lang = $(this).val();
        addLangButton(lang);
    })
});



// @end  language

// @begin upload image

{/literal}
$('#image_fileinput').fileupload({
    url: '/common/ajax/upload/image',
    formData: {
            csrf_token: '{$csrf_token}',
            image_type: 'article_content'
            },
    paramName: 'images',
    singleFileUploads: false,
    dataType: 'json',
    autoUpload: true,
    acceptFileTypes: /(\.|\/)(jpe?g|png)$/i,
    maxFileSize: 9990000,
    // Enable image resizing, except for Android and Opera,
    // which actually support image resizing, but fail to
    // send Blob objects via XHR requests:
    disableImageResize: /Android(?!.*Chrome)|Opera/.test(window.navigator.userAgent),
    previewMaxWidth: 800,
    previewMaxHeight: 600,
    previewCrop: false
}).on('fileuploadadd', function (e, data) {
    $('#image_progress_bar').attr('width', '0%');
    $('#image_progress_bar').text(0)
}).on('fileuploadprogressall', function (e, data) {
     var progress = parseInt(data.loaded / data.total * 100, 10);
    $('#image_progress_bar').attr('width', progress + "%");
    $('#image_progress_bar').text(progress)
}).on('fileuploadfail', function (e, data) {
    showMsg('Upload Image',"upload image fail.");
}).on('fileuploaddone', function (e, data) {
    // console.log("fileuploaddone:=");
    // console.log(data);
    $.each(data.result.images, function (index, file) {
        if (file.url_s3) {
            console.log("url_s3="+file.url_s3);
            var url = file.url_s3.replace('http://', 'https://');
            addImagePreview(url);
        }
    });
});

function addImagePreview(url) {
    var t_tag = "" + Date.now();
    var div_id = 'image_preview_' + t_tag ;
    var btn_id = 'btn_delete_' + t_tag ;
    var div = $(
        '<div class="row image_preview" id="' + div_id  + '" > ' +
            '<div class="col-sm-8 col-sm-push-2">' +
               ' <img src="' + url + '" class="img-responsive" alt="Responsive image">' +
            '</div>' +
            '<div class="col-sm-2 col-sm-push-2">' +
                '<span class="btn btn-danger" id="' + btn_id  + '"  >Delete This</span>' +
            '</div>' +
        '<hr></div>'
        );
    $('.image_list').append(div);
    $('#' + btn_id).click(function() {
        $('#' + div_id).remove();
    });
}

$(function(){
    $('#del_all_img').click(function(){
        $('.image_preview').remove();
    });
});

{literal}
// @end  upload image
{/literal}

$('#btn_stop').click(function() {

    $.ajax({
        url: '/opdata/ajax/aso/stopGplayTask',
        type: 'POST',
        async: true,
        data: {
            "csrf_token":'{$csrf_token}',
            "task_type": 'gplay_image',
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
            "task_type": 'gplay_image',
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
            "task_type": 'gplay_image',
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
