{extends 'layouts/home.layout.tpl'}


{block name=content}


<div class="row">


    <div>
      <ul class="nav nav-tabs" role="tablist">
        <li role="presentation"><a href="/infocenter/message"><h4>消息管理</h4></a></li>
        <li role="presentation" class="active"><a href="/infocenter/MessageGroup"><h4>消息分组</h4></a></li>
      </ul>
    </div>

<br/>


<form class="search_data" method="GET" action="/MessageGroup">

    <table class="table table-hover">
        <tr >
            <th>No.</th>
            <th><b>分组名称</b>
            </th>

            <th>起止时间</th>

            <th>通知方式</th>

            <th>
            状态
<!--             <select name="status">
                <option value="0" {if $_get_status=='0'}selected="selected"{/if} >全部</option>
                <option value="1" {if $_get_status=='1'}selected="selected"{/if} >
                未发布
                </option>
                <option value="2" {if $_get_status=='2'}selected="selected"{/if} >
                已发布</option>
            </select>
 -->            </th>

            <th>操作</th>
        </tr>
{foreach key=key from=$data item=row}
    <tr class="list_tr" data-id="{$row.id}">
        <td>{$row.id}</td>
        <td>
<!--         <a target="_blank" href="/infocenter/MessageGroup/msglist?id={$row.id}">
 -->        {$row.name}
        <!-- </a> --></td>
        <td>{$row.start} ~ {$row.end} </td>
        <td>
{if $row.notification == '1'}
    <span class="label label-success">Notification</span>
{/if}
{if $row.popup == '1'}
    <span class="label label-success">Pop</span>
{/if}
        </td>
        <td>

{if $row.status == '0' }
    <span class="label label-default">Draft</span>
{elseif $row.status == '1'}
    <span class="label label-success">Publish</span>
{else}
--
{/if}
        </td>
        <td>
        <a class="" data-id="{$row.id}" href="/infocenter/MessageGroup/add?id={$row.id}">编辑</a>&nbsp;&nbsp;
        <a class="exec_del" data-id="{$row.id}" href="###">删除</a>
        </td>
    </tr>
{/foreach}
    </table>



</form>

<div class="pull-right">
    <a  class="btn btn-primary btn-lg"  target="_blank" href="/infocenter/MessageGroup/add">新建分组</a>&nbsp;&nbsp;

</div>


<div class="row" style="text-align: center;">
    <div class="pagination">
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
</div>


</div>
{/block}



{block name=javascript append}
{literal}
<script src="https://cdn.bootcss.com/twbs-pagination/1.3.1/jquery.twbsPagination.min.js" integrity="sha384-SmCTkd8lzy56mVx+gf0snPtZiQoOWbYDwgTAlM2C5SnLXuis8VBWsoJKeFh++L38" crossorigin="anonymous"></script>
<script type="text/javascript">


$(".exec_del").click(function(){
    var id = $(this).attr('data-id');
    bootbox.confirm({
        message: '确认删除该分组吗？',
        buttons: {
            confirm: {
                label: '确认',
                className: 'btn-primary'
            },
            cancel: {
                label: '取消',
                className: 'btn-default'
            }
        },

        callback: function(res) {
            if(res) {
                exec_del(id)
            }
        }
    });
})

function exec_del(id) {
    $.ajax({
        type: 'GET',
        url: '/infocenter/MessageGroup/delGroup?id=' + id,
        success: function(res) {
            if(res['status']=='error'){
                alert('删除失败~');
                return false;
            }
            window.location.reload();
        }
    });

}

{/literal}

$('#pagination').twbsPagination({
    totalPages: {$max_page},
    visiblePages: 10,
    startPage:{$current_page},
    initiateStartPageClick:true,
    href: '?page=%pagenumber%&search=',
    hrefVariable:'%pagenumber%',
    first: 'First',
    prev: 'Previous',
    next: 'Next',
    last:'Last',loop:true,
    initiateStartPageClick:true,
    onPageClick: function (event, page) {
    }
});

</script>
{/block}


