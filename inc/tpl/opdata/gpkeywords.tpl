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

<link href="https://cdn.bootcss.com/bootstrap-select/1.12.2/css/bootstrap-select.min.css" rel="stylesheet">
<link href="//cdn.bootcss.com/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css" rel="stylesheet">

{/block}

{block name=content}
<div class="page-header">
  <h2>{$title}</h2>
</div>
<div class="row">
    <form action="" method="POST" role="form" class="form-horizontal">
        <input type="hidden" name="_method" value="POST">
        <input type="hidden" name="csrf_token" id="csrf_token" value="{$csrf_token}">

        <div class="form-group task_input">
            <label for="task_name" class="col-sm-2 col-md-2 control-label ">Task Name</label>
            <div class="col-sm-8 col-md-8">
                <input type="text" class="form-control field" id="task_name" placeholder="" value="" required>
            </div>
        </div>

        <div class="form-group task_input">
            <label for="country" class="col-sm-2 col-md-2 control-label ">Country</label>
            <div class="col-sm-8 col-md-8">
                <input type="text" class="form-control field" id="country" placeholder="" value="" required>
            </div>
        </div>

        <div class="form-group task_input">
            <label for="lang" class="col-sm-2 col-md-2 control-label require_mark">Language</label>
            <div class="col-sm-8 col-md-8">
                <input type="text" class="form-control field" id="lang" placeholder="" value="" required>
            </div>
        </div>

        <div class="form-group task_input">
            <label for="lang" class="col-sm-2 col-md-2 control-label ">获取下载量</label>
            <div class="checkbox ">
                &nbsp;&nbsp;
                <label><input type="checkbox" name="top_download" id="top_download" value="true">Yes</label>
            </div>
        </div>


        <div class="form-group task_input">
            <label for="lang" class="col-sm-2 col-md-2 control-label ">如何搜索联想词</label>
            <div class="btn-group col-sm-4">
              <select id="keywords_method" class="selectpicker">
                  <option value="suffix">后缀模式</option>
                  <option value="iteration">迭代模式</option>
                  <option data-divider="true"></option>
                  <option value="">跳过搜索联想词</option>
                </select>
            </div>
        </div>


<!-- suffix_method -->

        <hr class="suffix_method task_input">

        <div class="form-group suffix_method task_input">
            <label for="lang" class="col-sm-2 col-md-2 control-label ">从右向左书写:</label>
            <div class="checkbox ">
                &nbsp;&nbsp;
                <label><input type="checkbox" name="top_download" id="top_download" value="">Yes</label>
            </div>
        </div>

        <div class="form-group suffix_method task_input">
            <label for="suffix_1st_list" class="col-sm-2 col-md-2 control-label ">第1个后缀</label>
            <div class="col-sm-8 col-md-8">
                <input type="text" class="form-control field" id="suffix_1st_list" placeholder="" value="" required>
            </div>
        </div>

        <div class="form-group suffix_method  task_input">
            <label for="suffix_2nd_list" class="col-sm-2 col-md-2 control-label ">第2个后缀</label>
            <div class="col-sm-8 col-md-8">
                <input type="text" class="form-control field" id="suffix_2nd_list" placeholder="" value="" required>
            </div>
        </div>

        <div class="form-group suffix_method task_input">
            <label for="suffix_3rd_list" class="col-sm-2 col-md-2 control-label ">第3个后缀</label>
            <div class="col-sm-8 col-md-8">
                <input type="text" class="form-control field" id="suffix_3rd_list" placeholder="" value="" required>
            </div>
        </div>

<!-- iteration method -->

        <hr class="iteration_method task_input">

        <div class="form-group iteration_method">
            <label for="level" class="col-sm-2 col-md-2 control-label require_mark">迭代次数</label>
            <div class="col-sm-2 col-md-2">
                <input type="number" class="form-control field" id="level" placeholder="" value="" required>
            </div>
        </div>

        <hr>

        <div class="form-group task_input">
            <label for="word_list" class="col-sm-2 col-md-2 control-label require_mark">关键词(每行一个关键词)</label>
            <div class="col-sm-8 col-md-8">
                <textarea id="word_list" class="form-control field" rows="5" placeholder="" required></textarea>
            </div>
        </div>

        <hr>

        <div class="form-group task_input">
            <label for="mail_list" class="col-sm-2 col-md-2 control-label require_mark">任务完成后发邮件给：</label>
<!--             <div class="col-sm-8 col-md-8">
                <input type="text" class="form-control field" id="mail_list" placeholder="who@who.com" value="" required>
            </div> -->
            <div class="col-sm-8 col-md-8">
                <select id="mail_list" class="form-control" multiple data-actions-box="true">
                    {foreach from=$mail_list item=mail}
                        <option value="{$mail}">{$mail}</option>
                    {/foreach}
                </select>
            </div>
        </div>



        <div class="form-group">
            <div class="col-sm-offset-2 col-md-offset-2 col-sm-8 col-md-8">
                <a href="/opdata/AsoKeywords/viewTask"><span class="btn btn-info">View</span></a>
                <button type="button" class="btn btn-primary" id="btn_submit">Submit</button>
                <button type="button" class="btn btn-primary" id="btn_start">Start</button>
                <button type="button" class="btn btn-danger" id="btn_stop">Stop</button>
                <button type="button" class="btn btn-danger" id="btn_clear">Delete All</button>
            </div>
        </div>


        <hr>

        <div class="form-group">
            <label for="level" class="col-sm-2 col-md-2 control-label">可以下载的文件:</label>
            <div class="col-sm-10">
                <a href="/opdata/AsoKeywords/results"><span class="btn btn-info">ViewResults</span></a>
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

    </form>
</div>
{/block}

{block name=javascript append}
<script src="https://cdn.bootcss.com/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.min.js"></script>
<script src="https://cdn.bootcss.com/bootstrap-select/1.12.2/js/bootstrap-select.min.js"></script>
<script src="https://cdn.bootcss.com/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.min.js"></script>
<script type="text/javascript">
$(document).ready(function() {
    $('#mail_list').multiselect({
        enableFiltering: true,
        includeSelectAllOption: true,
        selectAllJustVisible: true,
        maxHeight: 500,
    });
});
</script>

<script>

$(function(){

    refresh_status();

    $('.iteration_method').hide();
    $('.suffix_method').show();

    $('#keywords_method').on('changed.bs.select', function(event) {
        event.preventDefault();
        refresh_keywords_method_layout();
    });
});

function refresh_keywords_method_layout() {
    var method = $('#keywords_method').val();
    if (method == 'suffix') {
        $('.iteration_method').hide();
        $('.suffix_method').show();
    } else if (method == 'iteration') {
        $('.iteration_method').show();
        $('.suffix_method').hide();
    } else {
        $('.iteration_method').hide();
        $('.suffix_method').hide();
    }
}


$(function() {
    refresh_status();
    var it = setInterval(refresh_status, 5000);
});

function refresh_status()
{
    $.ajax({
        url: '/opdata/ajax/AsoKeywords/status',
        type: 'POST',
        async: true,
        data: {
            "csrf_token":'{$csrf_token}',
        },
    })
    .done(function(data) {
        console.log("refresh_status:success");
        console.log(data);
        if (data.status == 'ok') {
            var file_list = $('#file_list');
            file_list.empty();
            if (data.running) {
                $('#btn_start').text('running...');
                file_list.append('<li>running</li>');
                file_list.append('<li>PID:' + data.pid + '</li>');
                $('.task_input').hide();

                $('#btn_stop').show();

                $('#btn_submit').hide();
                $('#btn_start').hide();
            } else {
                $('#btn_start').text('start');
                $('#btn_start').prop('disabled',false);
                file_list.append('<li>not running</li>');
                $('.task_input').show();
                refresh_keywords_method_layout();
                $('#btn_stop').hide();

                $('#btn_submit').show();
                if (data.has_submit) {
                    $('#btn_start').show();
                } else {
                    $('#btn_start').hide();
                }
            }
            if (data.files) {
                for (var i in data.files) {
                    var file = data.files[i];
                    if (file == '') {
                        continue;
                    }
                    file_list.append('<li><a href="' + file + '">' + file + '</a></li>');
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



function ajax_save(data) {
    $.ajax({
        url: '/opdata/ajax/AsoKeywords/submit',
        type: 'POST',
        async: true,
        data: {
            "csrf_token":'{$csrf_token}',
            "data":JSON.stringify(data)
        },
    }).done(function(data, txtStatus, xhr) {
        console.log(data);
        if (data.status == 'ok') {
            bootbox.alert("submit success!");
        } else if (data.status == 'error') {
            bootbox.alert(data.message);
        }
    }).fail(function(xhr, txtStatus, error) {
        bootbox.alert("submit error :" + txtStatus);
    }).always(function(xhr, txtStatus) {
        console.log("complete." + txtStatus);
        refresh_status();
    });
}

$("#btn_submit").click(function(){
    if (!$('#lang').val()) {
        bootbox.alert('Please fill languages!');
        return false;
    }

    var keywords_method = $('#keywords_method').val();

    if (keywords_method == 'iteration' && !$('#level').val()) {
        bootbox.alert('Please fill 迭代次数!');
        return false;
    }

    var mail_list = $('#mail_list').val();
    if (!mail_list) {
        bootbox.alert('Please fill mail list!');
        return false;
    }

    mail_list = mail_list.join(',');
    console.log("mail_list:" + mail_list);

    if (!$('#word_list').val()) {
        bootbox.alert('Please fill 关键词!');
        return false;
    }

    var task_name = $('#task_name').val();
    var country = $('#country').val();
    var lang = $('#lang').val();



    var suffix_1st_list = $('#suffix_1st_list').val();
    var suffix_2nd_list = $('#suffix_2nd_list').val();
    var suffix_3rd_list = $('#suffix_3rd_list').val();
    var reversed = $('#reversed').prop('checked');
    if (reversed) {
        reversed = true;
    } else {
        reversed = false;
    }
    var top_download = $('#top_download').prop('checked');
    if (top_download) {
        top_download = true;
    } else {
        top_download = false;
    }
    var level = $('#level').val();
    var number_re = /\d+/;
    if (number_re.test(level)) {
        level = parseInt(level);
    } else {
        level = 0;
    }
    var word_list = $('#word_list').val().split('\n');

    var res = {
        task_name: task_name,
        country: country,
        lang: lang,
        mail_list: mail_list,
        do_top_downloads: top_download,
        keywords_method: keywords_method,
        suffix_1st_list: suffix_1st_list,
        suffix_2nd_list:suffix_2nd_list,
        suffix_3rd_list:suffix_3rd_list,
        reversed: reversed,
        level:level,
        word_list:word_list,
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
                label: "Submit",
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


$('#btn_start').click(function() {

    $.ajax({
        url: '/opdata/ajax/AsoKeywords/start',
        type: 'POST',
        async: true,
        data: {
            "csrf_token":'{$csrf_token}',
        },
    })
    .done(function(data){
        // $('#btn_start').prop('disabled',true);
        if (data.status == 'ok') {
            bootbox.alert('start success!');
        } else {
            bootbox.alert('fail:' + data.message);
        }
        refresh_status();
    })
    .fail(function(data){
        bootbox.alert('NetWork Fail.');
    });
});


$('#btn_stop').click(function() {

    $.ajax({
        url: '/opdata/ajax/AsoKeywords/stop',
        type: 'POST',
        async: true,
        data: {
            "csrf_token":'{$csrf_token}',
        },
    })
    .done(function(data){
        bootbox.alert('success');
        refresh_status();
    })
    .fail(function(data){
        bootbox.alert('NetWork Fail.');
    });
});


$('#btn_clear').click(function() {

    $.ajax({
        url: '/opdata/ajax/AsoKeywords/clear',
        type: 'POST',
        async: true,
        data: {
            "csrf_token":'{$csrf_token}',
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


</script>
{/block}
