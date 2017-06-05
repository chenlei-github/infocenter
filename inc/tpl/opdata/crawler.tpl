{extends 'layouts/home.layout.tpl'}

{block name=stylesheet append}
<link rel="stylesheet" href="https://cdn.bootcss.com/blueimp-file-upload/9.12.5/css/jquery.fileupload.min.css" integrity="sha384-/cEZgEA00SiCs/3Xr4k0NQ9Ah+0JV4Erxn3BiUOTd54a+3lUvM1GUESDouZY3rbe" crossorigin="anonymous">
{/block}
{block name=content}

<hr>


<!--  PingStart Play Crawler-->

<div class="panel panel-primary">
    <div class="panel-heading">PingStart Data status</div>
    <div class="panel-body">
    <p id="pingstart_msg">
    {foreach from=$pingstart_status_text key=k item=v }
         {$v}<br>
    {/foreach}

    </p>

    <div class="form-group">
        <div class="col-sm-8">
              <input type="text" class="form-control" id="pingstart" name="pingstart" >
        </div>
        <div class="col-sm-4">
            <span class="btn btn-success fileinput-button">
                <span>Import</span>
                <input id="pingstart_fileinput" class="fileinput" type="file" name="import_file" >
            </span>
            <span id="pingstart_status" class="" ></span>
        </div>
    </div>

    </div>
</div>


<hr>
<!--  Baidu Crawler-->

<div class="panel panel-primary">
    <div class="panel-heading">Baidu Data status</div>
    <div class="panel-body">
    <p id="baidu_msg">
    {foreach from=$baidu_status_text key=k item=v }
         {$v}<br>
    {/foreach}

    </p>

    <div class="form-group">
        <div class="col-sm-8">
              <input type="text" class="form-control" id="baidu" name="baidu" >
        </div>
        <div class="col-sm-4">
            <span class="btn btn-success fileinput-button">
                <span>Import</span>
                <input id="baidu_fileinput" class="fileinput" type="file" name="import_file" >
            </span>
            <span id="baidu_status" class="" ></span>
        </div>
    </div>

    </div>
</div>
<hr>

<!--  Facebook Ads Crawler-->

<div class="panel panel-primary">
    <div class="panel-heading">Facebook Data status</div>
    <div class="panel-body">
     {foreach from=$facebook_status_text key=k item=v }
         {$v}<br>
     {/foreach}
    </div>
</div>

<hr>

<!--  Admob Ads Crawler-->

<div class="panel panel-primary">
    <div class="panel-heading">Admob Data status</div>
    <div class="panel-body">
    {foreach from=$admob_status_text key=k item=v }
         {$v}<br>
     {/foreach}
    </div>
</div>

<hr>
<!--  Google Play Crawler-->

<div class="panel panel-primary">
    <div class="panel-heading">Google Play Data status</div>
    <div class="panel-body">
    {foreach from=$googleplay_status_text key=k item=v }
         {$v}<br>
     {/foreach}
    </div>
</div>



<hr>
<form >
    <input type="hidden" name="csrf_token" id="csrf_token" value="{$csrf_token}">
</form>
{/block}

{block name=javascript append}

<script src="//cdn.bootcss.com/blueimp-file-upload/9.12.5/js/vendor/jquery.ui.widget.min.js" integrity="sha384-nGh8khjBjCD80rGfjnQZ+72Y3wYz0HHym3jC0kf9g1xVni73kthZ9fxtSIwjZ6zQ" crossorigin="anonymous"></script>
<script src="https://blueimp.github.io/JavaScript-Load-Image/js/load-image.all.min.js" ></script>
<script src="https://blueimp.github.io/JavaScript-Canvas-to-Blob/js/canvas-to-blob.min.js" ></script>

<script src="//cdn.bootcss.com/blueimp-file-upload/9.12.5/js/jquery.iframe-transport.min.js" integrity="sha384-L1OhlVcFQPXKloYQlEFOQQpRFoMgM8kecftiV5yLQV0Cze6jK4myjnX2HHHw1yD+" crossorigin="anonymous"></script>
<script src="//cdn.bootcss.com/blueimp-file-upload/9.12.5/js/jquery.fileupload.min.js" integrity="sha384-4fwO+NFkS0gdgfgIPTDvbaaD9n15lf3aA15hdn20LwTDN/QoEccsz5Tic+ncA8N9" crossorigin="anonymous"></script>
<script src="//cdn.bootcss.com/blueimp-file-upload/9.12.5/js/jquery.fileupload-process.min.js" integrity="sha384-wfPQHTx+v7NrjQ0cU/M5aZyd5A7SYTMNNRTtRWtuGcbJ+DEgtfKfD4Yi4J0h5NU5" crossorigin="anonymous"></script>

<script src="//cdn.bootcss.com/blueimp-file-upload/9.12.5/js/jquery.fileupload-image.min.js" integrity="sha384-5jnqQ1fz4Go1dWmEciZRgRte357rUBTqgpOiTufbt3A9RlBssNXmYV9ebiGKejs7" crossorigin="anonymous"></script>
<script src="//cdn.bootcss.com/blueimp-file-upload/9.12.5/js/jquery.fileupload-validate.min.js" integrity="sha384-N1ZqdkCijnsLxYtlvOpZ4eVbFu4p5ME6UrPnTxKHoHVXJHLY5C5VXvj3edi4U0eX" crossorigin="anonymous"></script>
<script src="https://cdn.tinymce.com/4/tinymce.min.js"></script>


<script>

$('#pingstart_fileinput').fileupload({
        url: '/opdata/ajax/report/import',
        formData: { csrf_token: '{$csrf_token}',
                    platform:  'pingstart',
                 },
        paramName: 'file',
        singleFileUploads: true,
        dataType: 'json',
        autoUpload: true,
        // acceptFileTypes: /(\.|\/)(gif|jpe?g|png|ico|icon)$/i,
        maxFileSize: 999000,
}).on('fileuploadadd', function (e, data) {
        $('#pingstart_status').text ("uploading:" + 0 + '%') ;
    }).on('fileuploadprogressall', function (e, data) {
         var progress = parseInt(data.loaded / data.total * 100, 10);
         $('#pingstart_status').text ("uploading:"+progress + '%') ;
    }).on('fileuploadfail', function (e, data) {
        $('#pingstart_status').text ("upload fail.") ;
    }).on('fileuploaddone', function (e, data) {
        $('#pingstart_status').text ("upload success.") ;
        console.log(data);
        crawler_msg = data.result.crawler_msg;
        console.log(crawler_msg);
        if(crawler_msg==null){
            $('#pingstart_status').text ("Run Fail.") ;
            return;
        }
        msg = "";
        for (var i=0; i<crawler_msg.length; i++)
        {
            msg += crawler_msg[i] + "<br>";
        }
        $('#pingstart_msg').html(msg);

});

$('#baidu_fileinput').fileupload({
        url: '/opdata/ajax/report/import',
        formData: { csrf_token: '{$csrf_token}',
                    platform:  'baidu',
                 },
        paramName: 'file',
        singleFileUploads: true,
        dataType: 'json',
        autoUpload: true,
        // acceptFileTypes: /(\.|\/)(gif|jpe?g|png|ico|icon)$/i,
        maxFileSize: 999000,
}).on('fileuploadadd', function (e, data) {
        $('#baidu_status').text ("uploading:" + 0 + '%') ;
    }).on('fileuploadprogressall', function (e, data) {
         var progress = parseInt(data.loaded / data.total * 100, 10);
         $('#baidu_status').text ("uploading:"+progress + '%') ;
    }).on('fileuploadfail', function (e, data) {
        $('#baidu_status').text ("upload fail.") ;
    }).on('fileuploaddone', function (e, data) {
        $('#baidu_status').text ("upload success.") ;
        console.log(data);
        crawler_msg = data.result.crawler_msg;
        console.log(crawler_msg);
        if(crawler_msg==null){
            $('#baidu_status').text ("Run Fail.") ;
            return;
        }
        msg = "";
        for (var i=0; i<crawler_msg.length; i++)
        {
            msg += crawler_msg[i] + "<br>";
        }
        $('#baidu_msg').html(msg);

});

</script>
{/block}
