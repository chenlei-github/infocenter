{extends 'layouts/home.layout.tpl'}


{block name=content}

<div class="row">
    <table class="table">
        <tr>
            <th>No.</th>
            <th>Time</th>
            <th>File</th>
        </tr>
        {$i = 0 }
        {foreach $file_list item=row}
            <tr>
                <td><span>{$i}</span></td>
                <td><span>{date('Y-m-d H:i:s', $row.ftime + 28800)}</span></td>
                <td><a href="/opdata/ReviewDesc/downfile?f={$row.fname}">{$row.fname}</td>
            </tr>
            {$i = $i + 1 }
        {/foreach}
    </table>
</div>
{/block}

