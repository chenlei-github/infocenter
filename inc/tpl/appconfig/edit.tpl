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
	<span>
	<div class="col-sm-10">
		<ul class="nav nav-tabs">
		  <li role="presentation"><a href="/infocenter/AppConfig/">AppConfig List</a></li>
		  <li role="presentation"><a href="/infocenter/AppConfig/add">Add AppConfig</a></li>
		  <li role="presentation" class="active"><a href="#">Edit AppConfig</a></li>
		</ul>
	</div>
	<div class="col-sm-2 col-sm-push-1">
		<button  onClick="doSaveAppConfig()" class="btn btn-info">Save</button>
	</div>
	</span>
</div>

<hr>
<div class="row">
<form class="form-horizontal" action="#" method="POST" >
	<div class="form-group">
		<div class="col-sm-10 col-md-1- col-sm-push-2 bg-info">
		<span class="text text-left text-default">The field marked by <span class="star_mark">'*'</span> should be filled in, others are optinal.</span>
		</div>
	</div>
	<div class="form-group">
	    <label for="appconfig_appid" class="col-sm-2 control-label">AppID<span class="star_mark">*</span></label>
	    <div class="col-sm-10">
	        <input type="text" class="form-control" id="appconfig_appid" readonly name="appconfig_appid" value="{$appconfig.appid}">
		    <input type="hidden" name="appconfig_id" id="appconfig_id" value="{$appconfig.id}">
	        <input type="hidden" name="csrf_token" id="csrf_token" value="{$csrf_token}">
	    </div>
	</div>
	<div class="form-group">
	    <label for="appconfig_configure" class="col-sm-2 control-label">Configure<span class="star_mark">*</span></label>
	   	<div class="col-sm-10" id="appconfig_configure" style="height: 400px;"></div>
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
<hr>
{/block}

{block name=javascript append}
<script src="https://cdnjs.cloudflare.com/ajax/libs/jsoneditor/5.5.6/jsoneditor.min.js" type="text/javascript" charset="utf-8" ></script>
<script>

	var APPCONFIGS="{$appconfig_configs}";

	console.log("APPCONFIGS="+APPCONFIGS);
	var json=atob(APPCONFIGS);
	console.log("json="+json);
	var container = document.getElementById('appconfig_configure');
	var options = {
		mode:'code',
	    modes: ['code','text','tree','view','form']
	};
	var jsonEditor = new JSONEditor(container, options);
	jsonEditor.setText(json);

	$('#appconfig_tag').on('input',function(e){
		var u=$('#appconfig_tag').val();
		if(u!=null){
			$('#appconfig_tag_img').attr('src',u);
		}
	});

	function doSaveAppConfig(){
		var appconfig_id = $('#appconfig_id').val();
		var appconfig_appid = $('#appconfig_appid').val();
		var appconfig_configure = jsonEditor.getText();

		var csrf_token=$('#csrf_token').val();

		if(appconfig_configure == null || appconfig_configure == "" ){
			showMsg('Wrong Feild','Configure Is Empty');
			return;
		}
		console.log("config:="+btoa(JSON.stringify(JSON.parse(appconfig_configure))));
		console.log("APPCONFIGS:="+APPCONFIGS);
		if (btoa(JSON.stringify(JSON.parse(appconfig_configure)))==APPCONFIGS) {
			showMsg("Notice" , "Configure does not changed, does not need to save!");
			return false;
		}
		if (!checkJson(appconfig_configure)) {
			showMsg("Bad Json","Bad Json,please check it!");
			return false;
		}

		$.ajax({
		        method: "POST",
		        url: "/infocenter/ajax/AppConfig/edit",
		        data: {
		            appconfig_id: appconfig_id,
		            appconfig_appid: appconfig_appid,
		            appconfig_configure: appconfig_configure,
		            csrf_token:csrf_token
		        }
		})
		.done(function (msg) {
		        // console.log(msg);
		    	if(msg.status=='ok'){
		    		showMsg('Success','Save AppConfig Success !');
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

	function  checkJson(json_string){
		var isGood=false;
		try{
			JSON.parse(json_string);
			isGood=true;
		}catch(Exception){
			isGood=false;
		}
		return isGood;
	}

</script>
{/block}


