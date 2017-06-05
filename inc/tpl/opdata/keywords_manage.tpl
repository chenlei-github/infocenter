{extends 'layouts/home.layout.tpl'}


{block name=content}


<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">新增关键词</h4>
      </div>
      <div class="modal-body">

        <form class="form_data" method="POST" action="/opdata/Keyword/add_keyword">
          <div class="form-group">
            <label for="country">国家</label>
            <select name="country" class="form-control">
                {foreach from=$all_country item=country}
                    <option value="{$country}">{$country}</option>
                {/foreach}
            </select>
            <label for="lang">语言</label>
            <select name="lang" class="form-control">
                {foreach from=$all_lang item=lang}
                    <option value="{$lang}">{$lang}</option>
                {/foreach}
            </select>
            <label for="lang">词类</label>
            <select name="type" class="form-control">
                {foreach from=$all_type item=type}
                    <option value="{$type}">{$type}</option>
                {/foreach}
            </select>
          </div>
          <div class="form-group">
            <label for="">关键词(每行一个关键词)</label>
            <textarea class="form-control" rows="10" name="keyword"></textarea>
          </div>
<!--           <div class="checkbox">
            <label>
              <input name="update_now" type="checkbox" checked="checked"> 立即执行关键词爬取
            </label>
          </div> -->
        </form>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-defsault" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="save_data">Save changes</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" tabindex="-1" role="dialog" id="confirm_modal">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-body">
        <p class="content-text"></p>
      </div>
      <div class="modal-footer">
        <button type="button" onclick="window.location.reload()" class="btn btn-default" data-dismiss="modal">close</button>
        <button type="button" class="btn btn-primary " id="exec_crawler">确&nbsp;认</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->


<div class="row">
<form class="search_data" method="GET" action="/opdata/keyword">
<input type="hidden" name="is_exec_crawler" id="_is_exec_crawler">
<input type="hidden" name="confirm_exec" id="_confirm_exec">
    <table class="table">
        <tr>
            <th>No.</th>
            <th><b>关键词</b>
            <input id="search_val" style="width: 120px;" type="text" name="search" class="search" value="{$_get_search}">
            <a href="###" class="button" id="remove_search">清空关键词</a>
            </th>
            <th>词类
            <select name="type">
                    <option value="">全部</option>
                {foreach from=$all_type item=type}
                    <option {if $_get_type==$type}selected="selected"{/if} value="{$type}">{$type}</option>
                {/foreach}
            </select>
            </th>
            <th>语言
            <select name="lang">
                    <option value="">全部</option>
                {foreach from=$all_lang item=lang}
                    <option {if $_get_lang==$lang}selected="selected"{/if} value="{$lang}">{$lang}</option>
                {/foreach}
            </select>
            </th>
            <th>国家
            <select name="country">
                    <option value="">全部</option>
                {foreach from=$all_country item=country}
                    <option {if $_get_country==$country}selected="selected"{/if} value="{$country}">{$country}</option>
                {/foreach}
            </select>
            </th>
            <th>
            抓取状态
            <select name="status">
                <option value="0" {if $_get_status=='0'}selected="selected"{/if} >全部</option>
                <option value="1" {if $_get_status=='1'}selected="selected"{/if} >
                待爬取
                </option>
                <option value="2" {if $_get_status=='2'}selected="selected"{/if} >
                已完成</option>
                <option value="3" {if $_get_status=='3'}selected="selected"{/if} >
                失败</option>
            </select>

            </th>
            <th>最近爬取数据时间<br/>(BJ-time)</th>
            <th>操作</th>
        </tr>
{foreach from=$data item=row}
    <tr>
        <td>{$row.id}</td>
        <td>{$row.search}</td>
        <td>{$row.type}</td>
        <td>{$row.lang}</td>
        <td>{$row.country}</td>
        <td>
        {if $row['status']=='0'}
            <font color="#ccc">待爬取</font>
        {elseif $row['status']=='1'}
            <b style="color: green">已完成</b>
        {elseif $row['status']=='2'}
            <font color="red">失败</font>
        {else}
            --
        {/if}
        </td>
        <td>
        {if $row['recent_data_time']!="" }
        {date('Y-m-d H:i:s', strtotime($row['recent_data_time'])+28800)}
        {else}
            --
        {/if}
        </td>
        <td><a class="exec_del" data-id="{$row.id}" href="###">删除</a></td>
    </tr>
{/foreach}
    </table>
</form>



<div class="row">



    <div class="pagination pull-left">
        <ul class="pagination" id="pagination">
        <li>
            <a href="#" aria-label="Previous">
                <span aria-hidden="true">&laquo;</span>
            </a>
        </li>
            {* {for $i=1 to $max_page}
                {if $i eq $current_page}
                <li class="active" page="{$i}"><a href="#" >{$i}</a></li>
                {else}
                <li class="" id="{$i}"><a href="#">{$i}</a></li>
                {/if}
            {/for} *}
            {if $max_page < 1}
                <li class="active" page="0"><a href="#" >0</a></li>
            {/if}
        <li>
            <a href="#" aria-label="Next">
                <span aria-hidden="true">&raquo;</span>
            </a>
        </li>
        </ul>

    </div>

        <div class="pull-right">
            <button type="button" class="btn btn-primary btn-lg" data-toggle="modal" data-target="#myModal">
            新增
            </button>&nbsp;&nbsp;

            <button type="button" class="btn btn-success btn-lg confirm_exec" data-toggle="modal" data-target="#confirm_exec">
            &nbsp;运&nbsp;行&nbsp;
            </button>
        </div>


</div>


<div class="form-group">
    <label for="log" class="col-sm-2 col-md-2 control-label">Infomation:</label>
    <div class="col-sm-8 col-md-8">
        <textarea id="log" class="form-control field" rows="20" placeholder="" required></textarea>
    </div>
</div>



</div>
{/block}



{block name=javascript append}
{literal}
<script src="https://cdn.bootcss.com/twbs-pagination/1.3.1/jquery.twbsPagination.min.js" integrity="sha384-SmCTkd8lzy56mVx+gf0snPtZiQoOWbYDwgTAlM2C5SnLXuis8VBWsoJKeFh++L38" crossorigin="anonymous"></script>
<script type="text/javascript">


$(function() {
    refresh_status();
    var it = setInterval(refresh_status, 5000);
});

function refresh_status(){
    $.ajax({
        url: '/opdata/keyword/status',
        type: 'GET',
        async: true,
    }).done(function(data) {
        $('#log').val(data.log);
    }).fail(function() {
        console.log("refresh_status:error");
    });
}




$("#confirm_modal").on('hidden.bs.modal', function (e) {
  window.location.reload();
})


$("#save_data").click(function(){
    $(".form_data").find("")
    $.ajax({
        type: 'POST',
        url: '/opdata/Keyword/add_keyword',
        data: $(".form_data").serialize(),
        success: function(res) {
            var msg = '保存成功！';
            if(res['status']=='error'){
                msg = '保存失败！';
            }
            $("#confirm_modal").find(".content-text").html(msg);
            $("#myModal").modal('hide');
            $("#confirm_modal").modal('show');
            $("#exec_crawler").hide();
        }
    });

})

$(".exec_del").click(function(){
    $.ajax({
        type: 'GET',
        url: '/opdata/keyword/del_keyword?id=' + $(this).attr('data-id'),
        success: function(res) {
            var msg = '删除成功！';
            if(res['status']=='error'){
                msg = '删除失败~';
            }
            $("#confirm_modal").find(".content-text").html(msg);
            $("#myModal").modal('hide');
            $("#confirm_modal").modal('show');
            $("#exec_crawler").hide();
        }
    });

})

$(".search_data").find("select").change(function(){
    $(".search_data").submit();
})

$("#remove_search").click(function(){
    $("#search_val").val("")
    $(".search_data").submit();
})

$(".confirm_exec").click(function(){
    $("#exec_crawler").show();
    $("#confirm_modal").find(".content-text").text("");
    $("#_confirm_exec").val('1');
    $("#_is_exec_crawler").val('0');
    $.ajax({
        type: 'GET',
        url: '/opdata/keyword',
        data: $(".search_data").serialize(),
        success: function(res) {
            var con = $("#confirm_modal").find(".content-text");
            if(res['total']>0){
                con.html('对当前<b>'+ res['total'] +'</b>条关键词执行爬取？');
            }else{
                con.html('记录查询为空，无法执行！');
            }
            $("#confirm_modal").modal('show');
        }
    });
})

$("#exec_crawler").click(function(){
    $("#confirm_modal").find(".content-text").text("");
    $("#_is_exec_crawler").val('1');
    $("#_confirm_exec").val('0');
    $.ajax({
        type: 'GET',
        url: '/opdata/keyword',
        data: $(".search_data").serialize(),
        success: function(res) {
            var msg = '执行成功！';
            if(res['status']=='0'){
                msg = '系统繁忙，请等待上个任务完成~'
            }
            var con = $("#confirm_modal").find(".content-text");
            con.html(msg);
            $("#exec_crawler").hide();
            $("#confirm_modal").modal('show');
        }
    });
});



{/literal}

$('#pagination').twbsPagination({
    totalPages: {$max_page},
    visiblePages: 10,
    startPage:{$current_page},
    initiateStartPageClick:true,
    href: '?page=%pagenumber%&search={$_get_search}&type={$_get_type}&lang={$_get_lang}&country={$_get_country}&status={$_get_status}',
    hrefVariable:'%pagenumber%',
    first: 'First',
    prev: 'Previous',
    next: 'Next',
    last:'Last',loop:true,
    // paginationClass:
    initiateStartPageClick:true,
    onPageClick: function (event, page) {
        // $('#page-content').text('Page ' + page);
    }
}); // end pagination

</script>
{/block}


