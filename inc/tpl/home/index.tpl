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
</style>
{/block}
{block name=content}
<div class="row">
  <div class="col-md-10 col-md-offset-1">
    <div class="panel panel-default">
      <div class="panel-heading">Dashboard</div>
      <div class="panel-body">
        <p>Welcome to our homepage.</p>
        <br>
        <p>Hello, <strong>{$user.name}</strong>&nbsp;!&nbsp; You are logged in with email {$user.email}</p>
        <br>
        <p>Links:<br>
            <a href="https://exmail.qq.com/login" target="_blank">Our Office Email (exmail.qq.com/login)</a><br>
            <a href="//git.amberweather.com" target="_blank">Our Gitlab (git.amberweather.com)</a><br>
            <a href="//www.amberweather.com" target="_blank">Our Product Website (www.amberweather.com)</a><br>
        </p>
      </div>
    </div>
    <div class="panel panel-default">
      <div class="panel-heading">Our systems:</div>
      <div class="panel-body">
        <!-- <div class="list-group"> -->
        <!-- <a href="/message" class="list-group-item">message</a>
        <a href="/admin/common_api" class="list-group-item">common_api</a>
        <a href="/admin/content" class="list-group-item">content</a>
        <br>
        <a href="https://store.amberweather.com" class="list-group-item">store</a>
        <a href="https://daisy.amberweather.com" class="list-group-item">daisy</a> -->
        <!-- </div> -->
        <div class="list">
          <ul class="nav nav-list">
            <li class="nav-header">Infocenter:</li>
            <li><a href="/infocenter/message">message</a></li>
            <li><a href="/infocenter/AppConfig">App Config</a></li>
            <li><a href="/infocenter/article">article</a></li>
            <li class="nav-header">Opdata:</li>
            <li><a href="/opdata/report">Report</a></li>
            <li><a href="/opdata/report/revenue">Revenue report</a></li>
            <li class="nav-header">other:</li>
            <li><a href="https://store.amberweather.com" class="">store</a></li>
            <li><a href="http://store.amberweather.com/home/ThemePage" class="">Theme</a></li>
            <li><a href="http://store.amberweather.com/ezweather/get_code.php" class="">get widget code</a></li>
            <li><a href="http://store.amberweather.com/ezweather/set_version.php" class="">set version</a></li>
            <li><a href="http://store.amberweather.com/activity/LotteryPage" class="">Lottery</a></li>
            <li><a href="http://store.amberweather.com/gallery/GalleryPage" class="">Gallery</a></li>
            <li><a href="http://daisy.amberweather.com" class="">daisy</a></li>
          </ul>
        </div>
        <!-- list end -->
      </div>
    </div>
    <!-- panel-default end -->
  </div>
</div>
{/block}
{block name=javascript append}
{/block}
