{extends 'layouts/home.layout.tpl'}

{block name=content}

<div class="row">
    <span>
    <div class="col-sm-8">
        <ul class="nav nav-tabs">
          <li role="presentation" class="active"><a href="/infocenter/article/">Article List</a></li>
          <li role="presentation" ><a href="/infocenter/article/add">Add Article</a></li>
          <!-- <li role="presentation" class="disable"><a href="#">Edit Article</a></li> -->
        </ul>
    </div>
    <div class="col-sm-2">
        <button class="btn btn-danger"  onClick="doDelectSelected()">Delete Selected</button>
    </div>
    <div class="col-sm-2">
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
    <input type="hidden" name="page" id="page" value="{$page}">
    <input type="hidden" name="sort" id="sort" value="{$sort}">
</form>

<div class="table-responsive">
    <table id="article_table" class="table table-hover">
        <thead>
            <tr>
                <th class="article_select_all"><input type="checkbox" id="select_all"></th>

                {foreach from=$table_head item=head}

                {if $head.has_sorted  }

                <th class="article_{$head.name}">
                <div class="dropdown">
                {if   $head.des == $sort }
                  <button class="btn btn-info dropdown-toggle" type="button" data-toggle="dropdown">
                    {$head.name}   <span class="caret"></span>
                  </button>
                {elseif $head.asc == $sort }
                  <button class="btn btn-info dropdown-toggle" type="button" data-toggle="dropdown">
                    {$head.name}   <span >^</span>
                  </button>
                {else}
                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown">
                    {$head.name}   <span class="caret"></span>
                 </button>
                {/if}
                  <ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu1">
                    <li role="presentation"><a role="menuitem" tabindex="-1" href="#" onclick="SortedBy('{$head.des}')">Descending</a></li>
                    <li role="presentation"><a role="menuitem" tabindex="-1" href="#" onclick="SortedBy('{$head.asc}')">Ascending</a></li>
                  </ul>
                </div>
                </th>

                {else}
                <th class="article_{$head.name}">{$head.name}</th>
                {/if}
                {/foreach}

               </tr>
        </thead>
        <tbody>
            {foreach from=$articles item=article}
            <tr  id="article_tr_{$article.id}">
                <td class="article_select"><input type="checkbox" name="select_{$article.id}" class="article_checkbox"></td>
                <td class="article_id">{$article.id|truncate:5}</td>

                {if $article.status == 0 }
                    <td class="article_status"><span class="label label-default">Draft</span></td>
                {else}
                    <td class="article_status"><span class="label label-success">Publish</span></td>
                {/if}

                <td class="article_title" data-toggle="tooltip" title="{$article.title}">{$article.title|truncate:20}</td>
                <td class="article_link" data-toggle="tooltip" title="{$article.link}">
                   <a href="http://content.amberweather.com/article/{$article.link}">Preview</a>
                </td>

                <td class="article_category" data-toggle="tooltip" title="{$article.category}">{$article.category|truncate:10}</td>

                <td class="article_image"><img class="img-thumbnail" width="50" height="50"  src="{$article.image}"/></td>
<!--                 <td class="article_language" data-toggle="tooltip" title="{$article.language}">{$article.language|truncate:10}</td> -->
                <td class="article_weight">{$article.weight}</td>
                <td class="article_created_at">{$article.created_at}</td>
                <td class="article_updated_at">{$article.updated_at}</td>
                <td class="article_op">
                    <button class="btn btn-info" onClick="doEdit('{$article.id}')">edit</button>
                    <button class="btn btn-danger" onClick="doDelete('{$article.id}')">delete</button>
                </td>
            </tr>
            {/foreach}
        </tbody>
    </table>
</div>
<!-- Modal -->
<div id="Modal" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
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
            <li><a href="/infocenter/article/?page={$pre_page}&sort={$sort}" aria-label="Previous">&laquo;</a></li>
            <li class="active" page="{$page}"><a href="#">{$page}/{$max_page}</a></li>
            <!-- <li class="disabled" ><a href="#">({$max_page})</a></li> -->
            <li><a href="/infocenter/article/?page={$next_page}&sort={$sort}" aria-label="Next">&raquo;</a></li>
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
        document.getElementById('DialogMsg').innerHTML="确定删除ID=" + aid + "的Article吗?";
        $('#Dialog').modal();
        // alert("确定删除ID=" + aid + "的Article吗?");
    }

    function doRealDelete(){
        // alert("确定删除ID=" + TO_DEL_ID + "的Article吗?");
        aUrl=null;
        if(which_to_del == DEL_ONE ){
            aUrl="/infocenter/ajax/article/delete";
        }else{
            aUrl="/infocenter/ajax/article/delete_many";
        }

        csrf_token=$('#csrf_token').val();
        $.ajax({
                method: "POST",
                url: aUrl,
                data: {
                    article_id: TO_DEL_ID,
                    del_list:TO_DEL_ID_LIST,
                    csrf_token:csrf_token
                }
            })
            .done(function (msg) {
                console.log(msg);
                if(msg.status=='ok'){
                    if(which_to_del == DEL_ONE ){
                        showMsg('Success','Delete Article Success !');
                        var del_id = '#article_tr_' + msg.id ;
                        $(del_id).remove();
                    }else{
                        if(msg.success_count>0){
                            showMsg('Success','Delete Article Success !');
                            console.log("TO_DEL_ID_LIST="+TO_DEL_ID_LIST);
                            var myJson=atob(TO_DEL_ID_LIST);
                            console.log("myJson:"+myJson);
                            var ids=JSON.parse(myJson);
                            for(var i=0;i<ids.length;i++){
                                var del_id = '#article_tr_' + ids[i] ;
                                $(del_id).remove();
                            }
                        }else{
                            showMsg('Fail','Delete Article Fail !');
                        }
                    }
                }else{
                    showMsg('Fail','Delete Article Fail !');
                }
            })
            .fail(function () {
                showMsg('Fail','Plead Try later!');
            });
    }
    function Jump(){
       var page=$('#jump_page').val();
       var max_page=$('#max_page').val();
       if(page>max_page) { page=max_page; }
       if(page<1) { page=1 ;  }
       sort=('#sort').val();
       window.location.href = "/infocenter/article/?page="+page+"&sort="+sort;
    }
    function doEdit(aid){
        window.location.href = "/infocenter/article/edit?article_id="+aid;
    }


    function showMsg(title,msg){
        document.getElementById('ModalTitle').innerHTML=title;
        document.getElementById('ModalMsg').innerHTML=msg;
        $('#Modal').modal();
    }

    function doDelectSelected(){
        var arr = $('.article_checkbox:checked');
        console.log("arr.length="+arr.length);
        var del_ids = [];
        var msgString='';
        if(arr == null || arr.length < 1 ){ return; }
        for(var i = 0; i < arr.length; i++){
            var id=arr[i].getAttribute("name").substr('select_'.length);
            console.log('id='+id);
            del_ids.push(id);
            msgString+=','+id;
        }
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
            $('.article_checkbox').prop('checked', true );
        } else {
            $('.article_checkbox').prop('checked', false);
        }
    });

    // 字段的排序

    function SortedBy(sort){
        page= $('#page').val();
        window.location.href = "/infocenter/article/?page="+page+"&sort="+sort;
    }


</script>
{/block}
