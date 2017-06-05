{extends 'layouts/home.layout.tpl'}

{block name=stylesheet append}
<style>
    .star_mark{
         color:red;
    }
</style>

<link rel="stylesheet" href="https://cdn.bootcss.com/blueimp-file-upload/9.12.5/css/jquery.fileupload.min.css" integrity="sha384-/cEZgEA00SiCs/3Xr4k0NQ9Ah+0JV4Erxn3BiUOTd54a+3lUvM1GUESDouZY3rbe" crossorigin="anonymous">


{/block}

{block name=content}

<div class="row">
    <div class="col-sm-10">
    <ul class="nav nav-tabs">
      <li role="presentation"><a href="/infocenter/article/">Article List</a></li>
      <li role="presentation" class="active"><a href="#">Add Article</a></li>
      <!-- <li role="presentation" hidden="true" class="disable"><a href="#">Edit Article</a></li> -->
    </ul>
    </div>
    <div class="col-sm-2 col-sm-push-1">
        <button  onClick="doSaveArticle()" class="btn btn-info">Save</button>
    </div>
</div>
<form class="form">
    <input type="hidden" name="csrf_token" id="csrf_token" value="{$csrf_token}">
</form>
<hr>
<div class="form row">
<form class="form-horizontal" action="#" method="POST" >
    <div class="form-group">
        <div class="col-sm-10 col-md-1- col-sm-push-2 bg-info">
        <span class="text text-left text-default">The field marked by <span class="star_mark">'*'</span> should be filled in, others are optinal.</span>
        </div>
    </div>
    <div class="form-group">
        <label for="article_status" class="col-sm-2 control-label">Status<span class="star_mark">*</span></label>
        <div class="col-sm-10">
            <div class="input-group">
              <input type="text" class="form-control" id="article_status_str" name="article_status_str" placeholder="All" value="{$article_status_enum['0']}" readonly>
              <input type="hidden" class="form-control" id="article_status_val" name="article_status_val" placeholder="All" value="0">
              <div class="input-group-btn">
                <button type="button" id="article_status_btn" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Status <span class="caret"></span></button>
                <ul class="dropdown-menu dropdown-menu-right">
                 {foreach from=$article_status_enum key=k item=v }
                     <li><a href="#" onClick="doSelStatus('{$k}','{$v}')" >{$v}</a></li>
                 {/foreach}
                </ul>
              </div><!-- /btn-group -->
            </div><!-- /input-group -->
        </div>
    </div>
    <div class="form-group">
        <label for="article_title" class="col-sm-2 control-label">Category<span class="star_mark">*</span></label>
        <div class="col-sm-10">
            <div class="input-group">
              <input type="text" class="form-control" id="article_cid_str" name="article_cid_str" placeholder="All" value="{$article_cid_enum['0']}" readonly>
              <input type="hidden" class="form-control" id="article_cid_val" name="article_cid_val" placeholder="All" value="0">
              <div class="input-group-btn">
                <button type="button" id="article_cid_btn" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Type <span class="caret"></span></button>
                <ul class="dropdown-menu dropdown-menu-right">
                 {foreach from=$article_cid_enum key=k item=v }
                     <li><a href="#" onClick="doSelCategory('{$k}','{$v}')" >{$v}</a></li>
                 {/foreach}
                </ul>
              </div><!-- /btn-group -->
            </div><!-- /input-group -->
        </div>
    </div>

    <div class="form-group">
        <label for="article_title" class="col-sm-2 control-label">Weight<span class="star_mark">*</span></label>
        <div class="col-sm-10">
          <input type="text" class="form-control" id="article_weight" name="article_weight" placeholder="" value="0">
        </div>
    </div>

    <div class="form-group">
        <label for="article_title" class="col-sm-2 control-label">Title<span class="star_mark">*</span></label>
        <div class="col-sm-10">
          <input type="text" class="form-control" id="article_title" name="article_title" placeholder="">
        </div>
    </div>
    <div class="form-group">
        <label for="article_link" class="col-sm-2 control-label">Link<span class="star_mark">*</span></label>
        <div class="col-sm-10">
          <input type="text" class="form-control" id="article_link" value="{$article_link}" name="article_link" placeholder="">
        </div>
    </div>
    <div class="form-group">
        <label for="article_language" class="col-sm-2 control-label">Language</label>
        <div class="col-sm-2">
          <input type="text"  class="form-control" id="article_language" name="article_language" placeholder="en_US" value="en_US">
          
        </div>
        <div class="col-sm-8">
            <span class="star_mark">Only Edit Page Support  Mulit Language!</span>
        </div>
    </div>
    <div class="form-group">
        <label for="article_image" class="col-sm-2 control-label">Image</label>
        <img class="img-thumbnail col-sm-1" id="article_image_img" width="50" height="50" value="" />
        <div class="col-sm-7">
              <input type="text" class="form-control" id="article_image" name="article_image" placeholder="">
        </div>
        <div class="col-sm-2 col-md-2 ">
            <span class="btn btn-success fileinput-button">
                <i class="glyphicon glyphicon-plus"></i>
                <span>Add files...</span>
                <input id="image_fileinput" class="fileinput" type="file" name="images[]" >
            </span>
        </div>
    </div>
    <div class="form-group">
        <div class="col-sm-10 col-md-10 col-sm-push-2">
            <div id="image_progress" class="progress">
                <div id="image_progress_bar" class="progress-bar progress-bar-success progress-bar-striped active"></div>
            </div>
        </div>
    </div>
    <div class="form-group">
        <label for="article_author" class="col-sm-2 control-label">Author</label>
        <div class="col-sm-10">
          <input type="text" class="form-control" id="article_author" name="article_author" placeholder="">
        </div>
    </div>
    <div class="form-group">
        <label for="article_author_link" class="col-sm-2 control-label">Author Link</label>
        <div class="col-sm-10">
          <input type="text" class="form-control" id="article_author_link" name="article_author_link" placeholder="">
        </div>
    </div>
    <div class="form-group">
        <label for="article_editor" class="col-sm-2 control-label">Editor</label>
        <div class="col-sm-10">
          <input type="text" class="form-control" id="article_editor" name="article_editor" placeholder="">
        </div>
    </div>
    <div class="form-group">
        <label for="article_content" class="col-sm-2 control-label">Content<span class="star_mark">*</span></label>
        <div class="col-sm-10 col-md-10">
            <span onClick="doSaveArticle()" class="btn btn-info">Save</span>
        </div>
        <div class="col-sm-10 col-sm-push-2">
          <textarea  class="form-control " rows=20 id="article_content" name="article_content" placeholder="" value="please finish content">please finish content</textarea>
        </div>
        <span class="btn btn-success fileinput-button hidden " >
        <input id="image_browser" name="image" type="file" >
        <input id="upload_image_url" class="hidden" type="text" >
        <input id="upload_image_progress" class="hidden" type="text" >
        </span>
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
<!-- Modal -->
<div id="JumpModal" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4  ><label id="JumpModalTitle" class="modal-title" >Success</label></h4>
      </div>
      <div class="modal-body">
        <p><label   id="JumpModalMsg"  class="text-cent">Save Message Success<br/>What will you do next?</label></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" onClick="goArticleList()" data-dismiss="modal">Go Article List</button>
        <button type="button" class="btn btn-default" onClick="addNextArticle()" data-dismiss="modal">Add Next Article</button>
        <button type="button" class="btn btn-default" onClick="editThisArticle()" data-dismiss="modal">Edit This Article</button>
      </div>
    </div>
  </div>
</div>
<div id="ModalProgress" class="modal" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4  ><label id="ModalTitle" class="modal-title" ></label></h4>
      </div>
      <div class="modal-body">
        <p><label id="ModalProgressMsg"  class="text-cent"></label></p>
        <p><div class="progress" id="image_upload_progress">
          <div class="progress-bar progress-bar-striped active" id="image_upload_progress_bar" role="progressbar" aria-valuenow="45" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
            <span class="sr-only">0</span>
          </div> </p>
        </div>
        </p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<hr>
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
    tinymce.init({
      selector: '#article_content' ,
      selector: 'textarea',
      theme: 'modern',
      plugins: [
        'advlist autolink lists link image imagetools charmap print preview hr anchor pagebreak',
        'searchreplace wordcount visualblocks visualchars code fullscreen',
        'insertdatetime media nonbreaking save table contextmenu directionality',
        'emoticons template paste textcolor colorpicker textpattern '
      ],
      toolbar:'insertfile undo redo | fontselect  fontsizeselect | bold italic | forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | fullscreen ',
      fontsize_formats: '6pt 7pt 8pt 9pt 10pt 11pt  12pt 14pt 16pt 18pt 20pt 24pt 28pt 32pt 36pt',
      font_formats:  'Andale Mono=andale mono,times;'+ 'Arial=arial,helvetica,sans-serif;'+ 'Arial Black=arial black,avant garde;'+ 'Book Antiqua=book antiqua,palatino;'+ 'Comic Sans MS=comic sans ms,sans-serif;'+ 'Courier New=courier new,courier;'+ 'Georgia=georgia,palatino;'+ 'Helvetica=helvetica;'+ 'Impact=impact,chicago;'+ 'Symbol=symbol;'+ 'Tahoma=tahoma,arial,helvetica,sans-serif;'+ 'Terminal=terminal,monaco;'+ 'Times New Roman=times new roman,times;'+ 'Trebuchet MS=trebuchet ms,geneva;'+ 'Verdana=verdana,geneva;'+ 'Webdings=webdings;'+ 'Wingdings=wingdings,zapf dingbats',
      image_advtab: true,
      imagetools_toolbar: "rotateleft rotateright | flipv fliph | editimage imageoptions",
      // imagetools_cors_hosts: ['s3.amazonaws.com', 'www.tinymce.com', 'codepen.io'],
      // images_upload_url: '/common/ajax/upload/image',
      file_browser_callback: function(field_name, url, type, win) {
        console.log("file_browser_callback");
        $('#upload_image_url').unbind('change');
        $('#upload_image_url').on('change' ,function(){
            var url_s3 = $(this).val();
            console.log("file_browser_callback:url_s3="+url_s3);
            win.document.getElementById(field_name).value = url_s3;
        });
        $('#image_browser').trigger('click');
      },
    });

    $('#image_browser').fileupload({
        url: '/common/ajax/upload/image',
        formData: { csrf_token: '{$csrf_token}',
                    image_type: 'article_content'
                },
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
        $('#upload_image_url').val("  0%");
        $('#upload_image_url').trigger('change');
    }).on('fileuploadprogressall', function (e, data) {
         var progress = parseInt(data.loaded / data.total * 100, 10);
        $('#upload_image_url').val( "  "+progress + "%");
        $('#upload_image_url').trigger('change');
    }).on('fileuploadfail', function (e, data) {
        showMsg('Upload Image',"upload image fail.");
    }).on('fileuploaddone', function (e, data) {
        console.log("fileuploaddone:=");
        console.log(data);
        $.each(data.result.images, function (index, file) {
            if (file.url_s3) {
                console.log("url_s3="+file.url_s3);
                $('#upload_image_url').val(file.url_s3);
                $('#upload_image_url').trigger('change');
            }
        });
    });

</script>
<script>
    //全局变量
    var NEWID=null;

    $('#article_image').on('input',function(e){
        var u=$('#article_image').val();
        if(u!=null){
            $('#article_image_img').attr('src',u);
        }
    });


    function doSaveArticle(){
        var article_weight=$('#article_weight').val();
        var article_title=$('#article_title').val();
        // var article_content=$('#article_content').val();

        var article_status=$('#article_status_val').val();
        var article_cid=$('#article_cid_val').val();
        var article_link=$('#article_link').val();
        var article_image=$('#article_image').val();
        var article_language=$('#article_language').val();
        var article_author=$('#article_author').val();
        var article_author_link=$('#article_author_link').val();
        var article_editor=$('#article_editor').val();
        var csrf_token=$('#csrf_token').val();

        console.log('article_cid='+article_cid);

        var article_format = 'raw';
        if (article_cid == 999) {
          article_format = 'text';
        }
        var article_content=tinyMCE.activeEditor.getContent({
            format : article_format
        });

        if(article_title==null|| article_title==""){
            showMsg('Miss Feild','Title Is Empty');
            return;
        }
        if(article_content==null || article_content=="" ){
            showMsg('Miss Feild','Content Is Empty');
            return;
        }
        if(article_link==null|| article_link=="" ){
            showMsg('Miss Feild','Link Is Empty');
            return;
        }

        $.ajax({
                method: "POST",
                url: "/infocenter/ajax/article/add",
                data: {
                    article_status: article_status,
                    article_cid: article_cid,
                    article_weight: article_weight,
                    article_title: article_title,
                    article_content: article_content,
                    article_link: article_link,
                    article_image: article_image,
                    article_language: article_language,
                    article_author: article_author,
                    article_author_link: article_author_link,
                    article_editor: article_editor,
                    csrf_token:csrf_token,
                }
            })
            .done(function (msg) {
                // console.log(msg);
                if(msg.status=='ok'){
                    // $('#newid').val(msg.newid);
                    NEWID=msg.newid;
                    showSuccessDialog();
                    // showMsg('Success','Save Article Success !');

                }else{
                     showMsg('Fail','Save Article Fail !');
                }
            })
            .fail(function () {
               showMsg('Fail','Plead Try later!');
            });
        }

    function showMsg(title,msg){
        document.getElementById('ModalTitle').innerHTML=title;
        document.getElementById('ModalMsg').innerHTML=msg;
        $('#Modal').modal();
    }

    function showSuccessDialog(){
        $('#JumpModal').modal();
    }

    function goArticleList(){
        window.location.href = "/infocenter/article/";
    }

    function addNextArticle(){
        window.location.href = "/infocenter/article/add";
    }

    function editThisArticle(){
        // var newid=$('newid').val();
        window.location.href = "/infocenter/article/edit?article_id="+NEWID;
    }

    function doSelCategory(k,v){
        // console.log("k="+k+",v="+v);
        $("#article_cid_val").val(k); //0
        $("#article_cid_str").val(v); // ALL
    }

    function doSelStatus(k,v){
        // console.log("k="+k+",v="+v);
        $("#article_status_val").val(k); //0
        $("#article_status_str").val(v); // ALL
    }

    $('#image_progress').hide();
    $('#image_fileinput').fileupload({
        url: '/common/ajax/upload/image',
        formData: { csrf_token: '{$csrf_token}',
                    image_type: 'article_image'
                },
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
        $('#image_progress_bar').css('width', '0%').attr('aria-valuenow',0).text('0%').show();
        $('#image_progress').show();
    }).on('fileuploadprogressall', function (e, data) {
         var progress = parseInt(data.loaded / data.total * 100, 10);
        $('#image_progress_bar').css('width', progress + '%').attr('aria-valuenow',progress).text(progress+'%').show();
    }).on('fileuploadfail', function (e, data) {
        $.each(data.files, function (index) {
            var error = $('<span class="text-danger"/>').text('File upload failed.');
            $(data.context.children()[index]).append('<br>').append(error);
        });
    }).on('fileuploaddone', function (e, data) {
        console.log("fileuploaddone:=");
        console.log(data);
        $.each(data.result.images, function (index, file) {
            if (file.url_s3) {
                $('#article_image').val(file.url_s3);
                $('#article_image_img').attr('src',file.url_s3);
                console.log("url_s3="+file.url_s3);
            }
        });
        $('#image_progress').hide();
    }).prop('disabled', !$.support.fileInput)
      .parent().addClass($.support.fileInput ? undefined : 'disabled');

</script>
{/block}
