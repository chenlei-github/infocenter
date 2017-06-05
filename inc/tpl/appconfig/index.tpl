{extends 'layouts/home.layout.tpl'}

{block name=content}
<div class="row">
    <span>
    <div class="col-sm-5">
        <ul class="nav nav-tabs">
          <li role="presentation" class="active"><a href="/infocenter/AppConfig/">AppConfigure List</a></li>
          <li role="presentation" ><a href="/infocenter/AppConfig/add">Add AppConfigure</a></li>
          <!-- <li role="presentation" class="disable"><a href="#">Edit AppConfigure</a></li> -->
        </ul>
    </div>
    <div class="col-sm-2">
      <div class="dropdown">
        <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          {if $appid}{$appname|truncate:20}{else}All App{/if}
          <span class="caret"></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu">
          {foreach from=$app_list item=app}
            {if $appid!=$app.appid}
              <li><a href="/infocenter/AppConfig/?appid={$app.appid}">{$app.appname}</a></li>
            {/if}
          {/foreach}
        </ul>
      </div>
    </div>
    <div class="col-sm-2 col-sm-push-1">
        <button class="btn btn-danger"  onClick="doDelectSelected()">Delete Selected</button>
    </div>
    <div class="col-sm-2 col-sm-push-1">
        <div class="input-group">
            <input type="number" class="form-control" id="jump_page" placeholder="Jump N">
            <span class="input-group-btn">
            <button class="btn btn-info" onClick="Jump()" type="button">Jump!</button>
            </span>
        </div><!-- /input-group -->
    </div><!-- /.col-lg-6 -->

    </span>
</div>
<hr>
<form >
    <input type="hidden" name="csrf_token" id="csrf_token" value="{$csrf_token}">
    <input type="hidden" name="max_page" id="max_page" value="{$max_page}">
    <input type="hidden" name="current_page" id="current_page" value="{$page}">
    <!-- <input type="hidden" name="appid_hidden" id="appid_hidden" value="{$appid}"> -->

</form>
<div class="table-responsive">
    <table id="appconfig_table" class="table table-hover">
        <thead>
            <tr>
                <th class="appconfig_select_all"><input type="checkbox" id="select_all"></th>
                <th class="appconfig_id">id</th>
                <th class="appconfig_appid">AppID</th>
                <th class="appconfig_type">Type</th>
                <th class="appconfig_configure">Configure</th>
               </tr>
        </thead>
        <tbody>
            {foreach from=$appconfigs item=appconfig}
            <tr  id="appconfig_tr_{$appconfig.id}">
                <td class="appconfig_select col-sm-1"><input type="checkbox" name="select_{$appconfig.id}"  class="appconfig_checkbox" ></td>
                <td class="appconfig_id col-sm-1">{$appconfig.id|truncate:30}</td>
                <td class="appconfig_appid col-sm-2">{$appconfig.appid|truncate:50}</td>
                <td class="appconfig_type  col-sm-2">{$appconfig.type|truncate:50}</td>
                <td class="appconfig_configure  col-sm-4">{$appconfig.configure|truncate:100}</td>
                <td class="appconfig_op col-sm-3">
                    <button class="btn btn-info" onClick="doEdit('{$appconfig.id}')">edit</button>
                    <button class="btn btn-danger" onClick="doDelete('{$appconfig.id}')">delete</button>
                </td>
            </tr>
            {/foreach}
        </tbody>
    </table>
</div>
<!-- Modal -->
<div id="Modal" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal value-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4  ><label id="ModalTitle" class="modal-title" >Info</label></h4>
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
<div id="Dialog" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4  ><label id="DialogTitle" class="modal-title" >Info</label></h4>
      </div>
      <div class="modal-body">
        <p><label   id="DialogMsg"  class="text-cent"></label></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-default" onclick="doRealDelete()" data-dismiss="modal">Ok</button>
      </div>
    </div>
  </div>
</div>
<hr>
<div class="row">
    <nav class="pagination col-sm-4 col-sm-push-5">
        <ul class="pagination  pagination-sm" id="pagination">
            <li><a href="/infocenter/AppConfig/?page={$pre_page}&appid={$appid}" aria-label="Previous">&laquo;</a></li>
            <li class="active" page="{$page}"><a href="#">{$page}/{$max_page}</a></li>
            <!-- <li class="disabled" ><a href="#">({$max_page})</a></li> -->
            <li><a href="/infocenter/AppConfig/?page={$next_page}&appid={$appid}" aria-label="Next">&raquo;</a></li>
        </ul>
   </nav>
</div>
<hr>
{/block}

{block name=javascript append}
<script>

    const DEL_ONE  = 1;
    const DEL_MANY = 2;
    var which_to_del=0;
    var TO_DEL_ID=null;
    var TO_DEL_ID_LIST=null;

    function doDelete(aid){
        which_to_del=DEL_ONE;
        TO_DEL_ID=aid;
        document.getElementById('DialogMsg').innerHTML="确定删除ID=" + aid + "的Configure吗?";
        $('#Dialog').modal();
        // alert("确定删除ID=" + aid + "的Article吗?");
    }

    function doRealDelete(){
        // alert("确定删除ID=" + TO_DEL_ID + "的Article吗?");
        aUrl=null;
        if(which_to_del == DEL_ONE ){
            aUrl="/infocenter/ajax/AppConfig/delete";
        }else{
            aUrl="/infocenter/ajax/AppConfig/delete_many";
        }

        csrf_token=$('#csrf_token').val();
        $.ajax({
                method: "POST",
                url: aUrl,
                data: {
                    appconfig_id: TO_DEL_ID,
                    del_list:TO_DEL_ID_LIST,
                    csrf_token:csrf_token
                }
            })
            .done(function (msg) {
                console.log(msg);
                if(msg.status=='ok'){
                  if(which_to_del == DEL_ONE ){
                      showMsg('Success','Delete Configure Success !');
                      var del_id = '#appconfig_tr_' + msg.id ;
                      $(del_id).remove();
                  }else{
                        if(msg.success_count>0){
                            showMsg('Success','Delete Configure Success !');
                            var myJson=atob(TO_DEL_ID_LIST);
                            var ids=JSON.parse(myJson);
                            for(var i=0;i<ids.length;i++){
                                var del_id = '#appconfig_tr_' + ids[i] ;
                                $(del_id).remove();
                            }
                        }else{
                            showMsg('Fail','Delete Configure Fail !');
                        }
                  }
                }else{
                    showMsg('Fail','Delete Configure Fail !');
                }
            })
            .fail(function () {
                showMsg('Fail','Plead Try later!');
            });
    }
    function Jump(){
       var page=$('#jump_page').val();
       if( page === undefined || page==null|| page==''){
            page=$('#current_page').val();
       }
       var max_page=$('#max_page').val();
       if(page>max_page) { page=max_page; }
       if(page<1) { page=1 ;  }
       var appid=$('#filter_appid').val();
       if(page !== undefined && page!=null  &&  appid!=''){
        window.location.href = "/infocenter/AppConfig/?page=" + page + "&appid=" + appid;
      }else{
        window.location.href = "/infocenter/AppConfig/?page=" + page;
      }
    }
    function doEdit(aid){
        window.location.href = "/infocenter/AppConfig/edit?appconfig_id="+aid;
    }


    function showMsg(name,msg){
        document.getElementById('ModalTitle').innerHTML=name;
        document.getElementById('ModalMsg').innerHTML=msg;
        $('#Modal').modal();
    }

    function doDelectSelected(){
        var arr = $('.appconfig_checkbox:checked');
        console.log("arr.length="+arr.length);
        var del_ids = [];
        var msgString='';
        if( arr === undefined || arr == null || arr.length < 1 ){ return; }
        for(var i = 0; i < arr.length; i++){
            var id=arr[i].getAttribute("name").substr('select_'.length);
            console.log('id='+id);
            del_ids.push(id);
            msgString+=','+id;
        }
        msgString=msgString.substr(1);
        console.log("msgString:"+msgString);
        var js=JSON.stringify(del_ids);
        var b64=btoa(js);
        console.log("b64="+b64);
        which_to_del=DEL_MANY;
        TO_DEL_ID_LIST = b64;
        document.getElementById('DialogMsg').innerHTML="确定删除ID=[" + msgString + "]的Article吗?";
        $('#Dialog').modal();
    }

        // 添加checkbox全选功能
    $('#select_all').click(function() {
        var $this = $(this);
        if ($this.is(':checked')) {
            $('.appconfig_checkbox').prop('checked', true );
        } else {
            $('.appconfig_checkbox').prop('checked', false);
        }
    });


</script>
{/block}
