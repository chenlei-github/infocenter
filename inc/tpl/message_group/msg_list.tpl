{extends 'layouts/home.layout.tpl'}

{block name=content}

<h2>分组【{$data[0]['name']}】下的消息</h2>

<form id="prob_list_form">

    <div class="row">
        <table class="table tab_msg_list">

            <tr>

                <th>Id</th>
                <th>Title</th>
                <th>Tag
                <input type="text" class="form-control" name="flag" placeholder="search tag" style="width: 90px;">
                </th>

                <th>Probability</th>

            </tr>

            {foreach from=$data item=row}
            <tr data-id="{$row.id}">
                <td>{$row.id}</td>
                <td>{$row.title}</td>
                <td>{$row.flag}</td>
                <td>
                  <div class="form-group prob" style="width: 90px;">
                    <div class="input-group">
                      <input readonly="readonly" type="text" class="form-control msg_prob" name="prob_list[]" placeholder="" value="{$row.prob}">
                      <div class="input-group-addon">%</div>
                    </div>
                  </div>
                </td>

            </tr>
            {/foreach}
        </table>
    </div>

</form>


{/block}



{block name=javascript append}
<script type="text/javascript"></script>
{/block}