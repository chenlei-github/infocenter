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
.message_image img{
width:auto;
height:auto;
max-width:50px;
max-height:100%;
}

.message_icon img{
width:auto;
height:auto;
max-width:50px;
max-height:100%;
}
.no_data_div {
    margin-right: 0;
    margin-left: 0;
}

</style>
{/block}
{block name=content}

<form >
    <input type="hidden" name="max_page" id="max_page" value="{$max_page}">
    <input type="hidden" name="page" id="page" value="{$page}">
    <input type="hidden" name="sort" id="sort" value="{$sort}">
    <input type="hidden" name="appid" id="appid" value="{if isset($appid)}{$appid}{/if}">
</form>


<div>
  <!-- Nav tabs -->
  <ul class="nav nav-tabs" role="tablist">
    <li role="presentation" class="active"><a href="/infocenter/message"><h4>消息管理</h4></a></li>
    <li role="presentation"><a href="/infocenter/MessageGroup"><h4>消息分组</h4></a></li>

  </ul>
</div>

<br/>








<div class="row">

    <form class="form-inline" action="/infocenter/message" method="GET">
      <div class="form-group">
        <label for="app_id">APP Id：</label>
            <select class="form-control" name="appid" onchange="submit();">
                <option value="0">ALL</option>
                {foreach from=$app_list item=app}
                    <option {if $appid eq $app['appid']}selected="selected"{/if} value="{$app.appid}">{$app.appname}</option>
                {/foreach}
            </select>
      </div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
      <div class="form-group">
        <label for="tag_name">Tag Name：</label>
        <input type="text" class="form-control" name="flag" id="tag_name" placeholder="" value="{$_flag}">
      </div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
      <button type="submit" class="btn btn-default">Search</button>
    </form>


    <div class="pull-right">
        <button type="button" onClick="location.href='/infocenter/message/add'" class="btn btn-primary">Add New Message</button>
        <button type="button" onClick="" class="btn btn-danger">Delete Selected</button>
    </div>

</div>
<br>
<div class="table-responsive">
    <table id="message_table" class="table table-hover table-striped">

        <thead>
            <tr>
                <th class="message_select_all"><input type="checkbox" id="select_all"></th>

                {foreach from=$table_head item=head}

                {if $head.has_sorted  }

                <th class="message_{$head.name}">
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
            {foreach from=$messages item=message}
            <tr class="message_row" id="message_{$message.id}">
                <td class="message_select "><input type="checkbox" name="select"></td>
                <td class="message_id col-md-1">{$message.id}</td>
                <td class="">{$message.flag}</td>
                <td class="message_title " data-toggle="tooltip" title="{$message.description}">{$message.title|truncate:20}</td>

                {if $message.status == 0 }
                    <td class="message_status "><span class="label label-default">Draft</span></td>
                {elseif $message.status == 1}
                    <td class="message_status "><span class="label label-success">Publish</span></td>
                {elseif $message.status == 2}
                    <td class="message_status "><span class="label label-default">In_Group</span></td>
                {else}

                {/if}


<!--                 <td class="message_image "><img src="{$message.image}" class="img-thumbnail" data-toggle="tooltip" title="{$message.image}"></td>
 -->                <td class="message_notification ">
                    {if $message.notification == 1}
                        <span class="label label-success">Notice</span>
                    {else}
                        <span class="label label-default">Notice</span>
                    {/if}
                    {if $message.popup == 1}
                        <span class="label label-success">Pop</span>
                    {else}
                        <span class="label label-default">Pop</span>
                    {/if}
                </td>

                <td class="message_icon "><img src="{$message.icon}" class="img-thumbnail"></td>
                <!-- <td class="message_time">{$message.time}</td> -->
                <td class="message_start " data-toggle="tooltip" title="{$message.start}">
                {if $message.status neq '2'}
                    {$message.start}
                {else}
                    --
                {/if}
                </td>
                <td class="message_end " data-toggle="tooltip" title="{$message.end}">
                {if $message.status neq '2'}
                    {$message.start}
                {else}
                    --
                {/if}
                </td>

                <td class="message_update_at " data-toggle="tooltip" title="{$message.updated_at}">{$message.updated_at|truncate:20}</td>


                <td class="message_op ">
                    <a href="/infocenter/message/edit?id={$message.id}" target="_blank" class="btn btn-primary">edit</a>
                    {* <form action="/infocenter/ajax/message/delete?id={$message.id}" method="POST" style="display: inline;"> *}
                    {* <input type="hidden" name="_method" value="DELETE"> *}
                    <input type="hidden" name="csrf_token" id="csrf_token" value="{$csrf_token}">
                    <button type="button" class="btn btn-danger btn_delete">delete</button>
                    {* </form> *}
                </td>
            </tr>
            {/foreach}
        </tbody>
    </table>
    {if $total_count < 1 }
        <div class="row no_data_div">
            <label class="col-sm-2 col-sm-push-5">No Data.</label>
        </div>
    {/if}
    <hr>
    {* <div id="page-content"></div> *}
    <nav class="pagination center">
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
    </nav>
</div>
{/block}

{block name=javascript append}
<script src="https://cdn.bootcss.com/twbs-pagination/1.3.1/jquery.twbsPagination.min.js" integrity="sha384-SmCTkd8lzy56mVx+gf0snPtZiQoOWbYDwgTAlM2C5SnLXuis8VBWsoJKeFh++L38" crossorigin="anonymous"></script>
<script>
$('#pagination').twbsPagination({
    totalPages: {$max_page},
    visiblePages: 10,
    startPage:{$current_page},
    initiateStartPageClick:true,
    href: '?page=%pagenumber%' + '&sort=' + $('#sort').val() + '&appid=' + $('#appid').val(),
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

function deleteMessage(id){

    console.log('delete id :');
    console.log(id);

    $.ajax({
        url: '/infocenter/ajax/message/delete',
        type: 'POST',
        async: true,
        // cache: false,
        // contentType:'application/json',
        // dataType: 'json',
        data: {
            "csrf_token":'{$csrf_token}',
            "id":id
        },
    }).done(function(data, txtStatus, xhr) {
        console.log("delete success." + txtStatus);
        console.log(data);
        if (data.status == 'ok') {
            bootbox.alert(data.message);
            $('#message_'+id).hide();
        } else if (data.status == 'error') {
            errors = data.errors.join('<br>');
            bootbox.alert({
                title:data.message,
                message:errors
            });
        }

    }).fail(function(xhr, txtStatus, error) {
        console.log("request error :" + error + '\n status:' +txtStatus);
        console.log(error);
    }).always(function(xhr, txtStatus) {
        console.log("complete. " + txtStatus);
    });
} //end deleteMessage()
$(function(){
    $("button.btn_delete").click(function(){
        var message_id = $(this).parent().prevAll('td.message_id').text();
        var message_title = $(this).parent().prevAll('td.message_title').text();
        var confirmHTML = 'delete message: <strong>id</strong>:['+message_id+']<br><strong>title</strong>:'+message_title;
        confirmHTML = '<div class="confirmhtml"><p>'+confirmHTML+'</p></div>';
        bootbox.confirm(confirmHTML, function(result){
            if (result) {
                deleteMessage(message_id);
            }
        });
    }); //end btn_delete
});

    function SortedBy(sort){
        page= $('#page').val();
        appid=$('#appid').val();
        window.location.href = "/infocenter/message/?page=" + page + "&sort=" + sort + '&appid=' + appid;
    }
</script>
{/block}
