{extends 'layouts/home.layout.tpl'}
{block name=stylesheet append}
<link href="https://cdn.bootcss.com/jquery.bootstrapvalidator/0.5.3/css/bootstrapValidator.min.css" rel="stylesheet">
{/block}
{block name=content}
<div class="row">
    <div class="col-md-6">
        <form id="form_data" method="POST" action="/opdata/ReviewDesc/add">

          <div class="form-group">
            <label for="lan">语言：</label>
            <input type="lan" name="lan" class="form-control" id="lan" placeholder="">
          </div>

          <div class="form-group">
            <label for="app">包名：</label>
            <textarea  class="form-control" rows="23" name="app" id="app" placeholder="每行请填写一个APP包名..."></textarea>
          </div>
          <div class="form-group" style="text-align: center;">
              <button type="button" class="btn btn-success sub">
              &nbsp;&nbsp;&nbsp;提　　交&nbsp;&nbsp;&nbsp;
              </button>
          </div>
        </form>
    </div>

    <div class="col-md-6">

        <div class="form-group">
            <label for="log">Progress_info:</label>
            <textarea id="log" class="form-control field" rows="27" placeholder="Here will output some results and progress information after submit the data on the left..." readonly="readonly"></textarea>
        </div>

    </div>

</div>

<hr/>

<div class="form-group">
  <h4>数据结果下载：</h4>
  <div>
      <ul class="output_list">
          {foreach from=$file_list item=row}
            <li><a target="_black" href="/opdata/ReviewDesc/downfile?f={$row}">{$row}</a></li>
          {/foreach}
      </ul>
  </div>
  <a target="_black" href="/opdata/ReviewDesc/results">More...</a>
</div>

<hr/>
<br/>


{/block}

{block name=javascript append}{literal}
<script type="text/javascript" src="//cdn.bootcss.com/jquery.bootstrapvalidator/0.5.3/js/bootstrapValidator.min.js"></script>
<script type="text/javascript">

$(function() {
  $('#form_data').bootstrapValidator({
    fields: {
          lan: {
              validators: {
                  notEmpty: {
                      message: 'The lan is required'
                  }
              }
          },
          app: {
              validators: {
                  notEmpty: {
                      message: 'The app is required'
                  }
              }
          },
      },
  });

  $('.sub').click(function(){
    var form = $('#form_data');
    form.bootstrapValidator('validate');
    var valid = form.data("bootstrapValidator").isValid();
    if(!valid) return false;
    $.post(form.attr('action'), form.serialize(), function(result) {
          var msg = result['status']=='ok' ? '任务已开始运行！' : result.msg
          setInterval(refresh_status, 5000);
          bootbox.alert({
              title: result.status,
              message: msg,
              callback: console.log
          })

    }, 'json');
  })


  function refresh_status(){
      $.ajax({
        url: '/opdata/ReviewDesc/status',
        type: 'GET',
        async: false,
      }).done(function(data) {
          var output_list = '';
          data['files'].map(function (file) {
            output_list += '<li><a target="_black" href="/opdata/ReviewDesc/downfile?f='+ file +'">'+ file +'</a></li>';
          });
          $('.output_list').html(output_list);
          $('#log').val(data.log);
      }).fail(function() {
          console.log("refresh_status:error");
      });

  }


});
</script>
{/literal}{/block}
