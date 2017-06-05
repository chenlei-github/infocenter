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
        {foreach $file_list key=mtime item=entry}
            <tr>
                <td><span>{$i}</span></td>
                <td><span>{$mtime}</span></td>
                <td><a href="{$entry.url}">{$entry.name}</td>
            </tr>
            {$i = $i + 1 }
        {/foreach}
    </table>
</div>
{/block}

