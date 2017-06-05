{extends 'layouts/home.layout.tpl'}

{block name=stylesheet append}
<style>
.star_mark{
	color:red;
}
</style>
<link href="https://cdnjs.cloudflare.com/ajax/libs/jsoneditor/5.5.6/jsoneditor.min.css" rel="stylesheet" type="text/css">
{/block}

{block name=content}
<div class="row">
	<div class="col-sm-10">
	<ul class="nav nav-tabs">
	  <li role="presentation"><a href="/infocenter/AppConfig/">AppConfig List</a></li>
	  <li role="presentation" class="active"><a href="#">Add AppConfig</a></li>
	  <!-- <li role="presentation" hidden="true" class="disable"><a href="#">Edit AppConfig</a></li> -->
	</ul>
	</div>
	<div class="col-sm-2 col-sm-push-1">
		<button  onClick="doSaveAppConfig()" class="btn btn-info">Save</button>
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
	    <label for="appconfig_appid" class="col-sm-2 control-label">AppId<span class="star_mark">*</span></label>
		<div class="col-sm-10">
		    <div class="input-group">
		      <input type="text" class="form-control" id="appconfig_appid"  name="appconfig_appid"  value="{$appid}" placeholder="{$appid}"
		      		readonly  required>
		      <div class="input-group-btn">
		        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">AppID <span class="caret"></span></button>
		        <ul class="dropdown-menu dropdown-menu-right">
		          {foreach from=$app_list item=app}
		            {if $appid!=$app.appid}
		              <li><a href="#" onClick="$('#appconfig_appid').val('{$app.appid}')">{$app.appname}</a></li>
		            {/if}
		          {/foreach}
		        </ul>
		      </div><!-- /btn-group -->
		    </div><!-- /input-group -->
		  </div><!-- /.col-lg-6 -->
	</div>
	<div class="form-group">
	    <label for="appconfig_configure" class="col-sm-2 control-label">Configure<span class="star_mark">*</span></label>
	   	<div class="col-sm-10" id="appconfig_configure" style="height: 400px;"></div>
	</div>
	<hr>
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
      	<button type="button" class="btn btn-default" onClick="goAppConfigList()" data-dismiss="modal">Go AppConfig List</button>
        <button type="button" class="btn btn-default" onClick="addNextAppConfig()" data-dismiss="modal">Add Next AppConfig</button>
        <button type="button" class="btn btn-default" onClick="editThisAppConfig()" data-dismiss="modal">Edit This AppConfig</button>
      </div>
    </div>
  </div>
</div>
<hr>
{/block}

{block name=javascript append}
<script src="https://cdnjs.cloudflare.com/ajax/libs/jsoneditor/5.5.6/jsoneditor.min.js" type="text/javascript" charset="utf-8" ></script>
<script>
    //全局变量
	var NEWID=null;

	var container = document.getElementById('appconfig_configure');
	var options = {
		mode:'code',
	    modes: ['code','text','tree','view','form']
	};
	var jsonEditor = new JSONEditor(container, options);
	jsonEditor.setText("");

	$('#appconfig_image').on('input',function(e){
		var u = $('#appconfig_image').val();
		if(u != null){
			$('#appconfig_image_img').attr('src', u);
		}
	});


	function doSaveAppConfig(){
		var appconfig_appid=$('#appconfig_appid').val();
		var appconfig_type=$('#appconfig_type').val();
		// var appconfig_configure=$('#appconfig_configure').val();
		var appconfig_configure=jsonEditor.getText();
		if(!checkJson(appconfig_configure)){
			showMsg("Bad Json","Bad Json,please check it!");
			return;
		}

		var csrf_token=$('#csrf_token').val();

		// console.log('token='+token);

		if(appconfig_appid == null|| appconfig_appid == ""){
			showMsg('Miss Feild','AppId Is Empty');
			return false;
		}

		if(appconfig_configure == null || appconfig_configure == "" ){
			showMsg('Miss Feild','Configure Is Empty');
			return;
		}

		$.ajax({
		        method: "POST",
		        url: "/infocenter/ajax/AppConfig/add",
		        data: {
		        	appconfig_appid:appconfig_appid,
		            appconfig_type: appconfig_type,
		            appconfig_configure: appconfig_configure,
		            csrf_token:csrf_token,
		        }
		    })
		    .done(function (msg) {
		    	// console.log(msg);
		    	if(msg.status=='ok'){
		    		// $('#newid').val(msg.newid);
		    		NEWID=msg.newid;
		    		showSuccessDialog();
		    		// showMsg('Success','Save AppConfig Success !');

		    	}else{
		    		 showMsg('Fail','Save AppConfig Fail !');
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

	function goAppConfigList(){
		window.location.href = "/infocenter/AppConfig/";
	}

	function addNextAppConfig(){
		window.location.href = "/infocenter/AppConfig/add";
	}

	function editThisAppConfig(){
		// var newid=$('newid').val();
		window.location.href = "/infocenter/AppConfig/edit?appconfig_id="+NEWID;
	}
	function  checkJson(json_string){
		var isGood = false;
		try{
			JSON.parse(json_string);
			isGood = true;
		}catch(Exception){
			isGood = false;
		}
		return isGood;
	}

</script>
{/block}
