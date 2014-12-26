<?php
function publishHeader() {
	echo <<<EOD
<html lang="ja">
        <head>
                <meta charset="utf-8">
                <meta http-equiv="X-UA-Compatible" content="IE=edge">
                <title>Nukotan Live</title>
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <meta name="description" content="">
                <meta name="author" content="">

		<!-- JQuery -->
		<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
		<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1/jquery-ui.min.js"></script>
		<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1/i18n/jquery.ui.datepicker-ja.min.js"></script>
		<link type="text/css" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1/themes/ui-darkness/jquery-ui.css" rel="stylesheet" />
		<script type="text/javascript" src="https://www.google.com/jsapi"></script>

                <!-- Bootstrap -->
                <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.3/css/bootstrap.min.css">
                <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.3/css/bootstrap-theme.min.css">

                <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
                <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
                <!-- [if lt IE9]>
                <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
                <script src="https://css.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
                <![endif]-->

<script type="text/javascript">
$(function(){
	$("#startdate").datepicker({
		changeMonth: true,
		changeYear: true,
		onSelect: function(selectedDate) {
			$("#enddate").datepicker("option", "minDate", selectedDate);
		}
	});
	$("#startDate").datepicker("option", "dateFormat", "yy-mm-dd");
});
$(function(){
	$("#enddate").datepicker({
		changeMonth: true,
		changeYear: true,
		onSelect: function(selectedDate) {
			$("#startDate").datepicker("option", "maxDate", selectedDate);
		}
	});
});
</script>
</head>
<body>
<noscript><iframe src="//www.googletagmanager.com/ns.html?id=GTM-N3WN4L"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'//www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-N3WN4L');</script>

        <div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
                <div class="container">
                        <div class="collapse navbar-collapse">
                                <ul class="nav navbar-nav">
                                        <li><a href="http://www.nukotan.net/">ぬこたん☆どっとねっと(*´Д`)ﾊｧﾊｧ</a></li>
                                        <li><a href="http://www.nukotan.net/live/">ぬこたん公演まとめ(*´Д`)ﾊｧﾊｧ</a></li>
                                        <li><a href="http://www.nukotan.net/chart/">ぬこたんグラフ(*´Д`)ﾊｧﾊｧ</a></li>
                                        <li><a href="http://www.nukotan.net/quiz/">ぬこたんクイズ(*´Д`)ﾊｧﾊｧ</a></li>
                                </ul>
                        </div><!-- /.nav-collapse -->
                </div>
        </div>

        <div class="container">
                <div class="page-header">
                        <h1>Nukotan Live</h1>
                        <p class="lead"></p>
                </div>

EOD;
}
