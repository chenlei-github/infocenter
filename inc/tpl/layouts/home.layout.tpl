<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <!-- <meta http-equiv="X-UA-Compatible" content="IE=edge"> -->
    <!--[if IE]><meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"><![endif]-->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{$title} - Amber</title>

    <!-- Fonts -->
    <link rel="stylesheet" href="https://cdn.bootcss.com/font-awesome/4.5.0/css/font-awesome.min.css" integrity="sha384-XdYbMnZ/QjLh6iI4ogqCTaIjrFk87ip+ekIjefZch0Y+PvJ8CDYtEs1ipDmPorQ+" crossorigin="anonymous">
    <!-- <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato:100,300,400,700"> -->
<link href='https://fonts.googleapis.com/css?family=Roboto' rel='stylesheet' type='text/css'>
    <!-- Styles -->
    <link rel="stylesheet" href="https://cdn.bootcss.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
{block name=stylesheet append}
<style>
    body {
        /* font-family: 'Lato'; */
    }

    .fa-btn {
        margin-right: 6px;
    }

    .footer {
        padding-top: 40px;
        padding-bottom: 40px;
        margin-top: 100px;
        color: #767676;
        text-align: center;
        border-top: 1px solid #e5e5e5;
    }
    @media (min-width: 768px){
        .footer p {
            margin-bottom: 0;
        }
    }
</style>
{/block}

</head>
<body id="app-layout">

    <nav class="navbar navbar-default navbar-static-top">
        <div class="container">
            <div class="navbar-header">

                <!-- Collapsed Hamburger -->
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#app-navbar-collapse">
                    <span class="sr-only">Toggle Navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>

                <!-- Branding Image -->
                <a class="navbar-brand" href="/">
                    Amber Home
                </a>
            </div>

            <div class="collapse navbar-collapse" id="app-navbar-collapse">
                <!-- Left Side Of Navbar -->
                <ul class="nav navbar-nav">
                    <li><a href="/">Home</a></li>
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                            Infocenter<span class="caret"></span>
                        </a>

                        <ul class="dropdown-menu" role="menu">
                            <li><a href="/infocenter"><i class="fa fa-btn"></i>Index</a></li>
                            <li><a href="/infocenter/message"><i class="fa fa-btn"></i>Message</a></li>
                            <li><a href="/infocenter/appConfig"><i class="fa fa-btn"></i>App Config</a></li>
                            <li><a href="/infocenter/article"><i class="fa fa-btn"></i>Article</a></li>
                        </ul>
                    </li>
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                            Opdata<span class="caret"></span>
                        </a>

                        <ul class="dropdown-menu" role="menu">
                            <!-- <li><a href="/opdata"><i class="fa fa-btn"></i>opdata</a></li> -->
                            <!-- <li><a href="/opdata/report"><i class="fa fa-btn"></i>Report</a></li> -->

                            <li><a href="/opdata/DataSummary"><i class="fa fa-btn"></i>Store_Push_Summary</a></li>

                            <li><a href="/opdata/report/revenue"><i class="fa fa-btn"></i>Revenue</a></li>
                            <li><a href="/opdata/Aso/updateGooglePlay"><i class="fa fa-btn"></i>Play ASO</a></li>
                            <li><a href="/opdata/Aso/updateGooglePlayImage"><i class="fa fa-btn"></i>Play ASO Image</a></li>
                            <li><a href="/opdata/AsoKeywords/"><i class="fa fa-btn"></i>Play Keywords Tools</a></li>
                            <li><a href="/opdata/keyword"><i class="fa fa-btn"></i>Keyword Config</a></li>
                            <li><a href="/opdata/ReviewDesc"><i class="fa fa-btn"></i>APP Review && desc </a></li>
                            <!-- <li><a href="/opdata/report/import"><i class="fa fa-btn"></i>import</a></li> -->
                        </ul>
                    </li>
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                            Store<span class="caret"></span>
                        </a>

                        <ul class="dropdown-menu" role="menu">
                            <li><a href="http://store.amberweather.com/home/ThemePage" class="">Theme</a></li>
                            <li><a href="http://store.amberweather.com/ezweather/get_code.php" class="">get widget code</a></li>
                            <li><a href="http://store.amberweather.com/ezweather/set_version.php" class="">set version</a></li>
                            <li><a href="http://store.amberweather.com/activity/LotteryPage" class="">Lottery</a></li>
                            <li><a href="http://store.amberweather.com/gallery/GalleryPage" class="">Gallery</a></li>
                        </ul>
                    </li>

                    <li><a href="http://daisy.amberweather.com" class="">Daisy</a></li>
                </ul>

                <!-- Right Side Of Navbar -->
                <ul class="nav navbar-nav navbar-right">
                    <!-- Authentication Links -->
                    {if $user}
                    <li><img src="{$user.picture}?size=36" class="img-responsive img-circle avatar" style="display: inline-block;overflow: hidden;float: left;height: 100%;vertical-align: middle;margin: 7px 0px;" alt="Avatar">
                    </li>

                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                                {$user.name} <span class="caret"></span>
                            </a>

                            <ul class="dropdown-menu" role="menu">
                                <li><a href="/google-auth/login.php?logout"><i class="fa fa-btn fa-sign-out"></i>Logout</a></li>
                            </ul>
                        </li>
                    {else}
                        <!--
                        <li><a href="/login}">Login</a></li>
                        <li><a href="/register}">Register</a></li> -->
                        <li>
                            <a href="/google-auth/login.php">Login With Google Account</a>
                        </li>
                    {/if}
                </ul>
            </div>
        </div>
    </nav>


    <div class="sidebar">
        {block name=sidebar}{/block}
    </div>

    <div class="container">
        {block name=content}

        {/block}
    </div>

    <footer class="footer" style="text-align: center;">
        <div class="container">
            <p>
            &copy; 2015-{$smarty.now|date_format:"%Y"} Amberweather.com <a href="/home/terms" target="_blank">Terms</a>
            </p>
        </div>
    </footer>
    {block name=javascript append}
    <!-- JavaScripts -->
    <script src="https://cdn.bootcss.com/jquery/2.2.4/jquery.min.js" integrity="sha384-rY/jv8mMhqDabXSo+UCggqKtdmBfd3qC2/KvyTDNQ6PcUJXaxK1tMepoQda4g5vB" crossorigin="anonymous"></script>
    <script src="https://cdn.bootcss.com/lodash.js/4.17.2/lodash.min.js" integrity="sha384-wGdfmHRGSbzLZMDJzTsi2aq6Qwa6OzK7F58PLm8Bu+4S52WkLa1vU5o6ZQKXu9ny" crossorigin="anonymous"></script>
    <script src="https://cdn.bootcss.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
    <script src="https://cdn.bootcss.com/bootbox.js/4.4.0/bootbox.min.js" integrity="sha384-Nk2l95f1t/58dCc4FTWQZoXfrOoI2DkcpUvgbLk26lL64Yx3DeBbeftGruSisV3a" crossorigin="anonymous"></script>
    {/block}
</body>
</html>
