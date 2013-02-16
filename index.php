<?php

define('LOG_PATH', 'log/');
define('MAX_HISTORY', 5);

if ($_GET["error"]) {
    $error_message = "指定された期間に".$_GET["name"]."さんのログが見つかりません。日付を選択し直してください。";
} else {
    $error_message = "";
}

$log_list = glob(LOG_PATH . '*', GLOB_NOSORT);
//generate member list from log file name
foreach ($log_list as $name) {
    if (preg_match('/([a-zA-Z0-9]+)%/', $name, $match)) {
        $name_list[] = $match[1];
    }
}

//generate month list
for ($i = 0; $i < MAX_HISTORY; $i++) {
    $month_list[] = date('Y-m', strtotime('-'.$i.'month'));
}

//output selectbox
print<<<EOF
<!DOCTYPE html>
<html lang="en">
  
  <head>
    <meta charset="utf-8">
    <title>Kaerase Master
    </title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <!-- Le styles -->
    <link href="http://s3.amazonaws.com/jetstrap-site/lib/bootstrap/2.2.1/css/bootstrap.css" rel="stylesheet">
    <style>
      body { padding-top: 60px; /* 60px to make the container go all the way
      to the bottom of the topbar */ }
    </style>
    <link href="http://s3.amazonaws.com/jetstrap-site/lib/bootstrap/2.2.1/css/bootstrap-responsive.css" rel="stylesheet">
    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js">
      </script>
    <![endif]-->
    <!-- Le fav and touch icons -->
    <link rel="shortcut icon" href="http://s3.amazonaws.com/jetstrap-site/lib/bootstrap/2.2.1/ico/favicon.ico">
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="assets/ico/apple-touch-icon-144-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="assets/ico/apple-touch-icon-114-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="assets/ico/apple-touch-icon-72-precomposed.png">
    <link rel="apple-touch-icon-precomposed" href="assets/ico/apple-touch-icon-57-precomposed.png">
    <style>
      .input select { margin: 8px }
    </style>
  </head>
  
  <body>
    <div class="navbar navbar-fixed-top navbar-inverse">
      <div class="navbar-inner">
        <div class="container-fluid">
          <a class="brand" href="http://localhost/makeGraph/">
            KAERASE Master
          </a>
          <ul class="nav">
            <li>
              <a href="#madananimonai">
                About
              </a>
            </li>
            <li>
              <a href="#madananimonai">
                Home
              </a>
            </li>
            <li>
              <a href="#madananimonai">
                Contact
              </a>
            </li>
          </ul>
        </div>
      </div>
    </div>
    <div class="container-fluid">
      <div class="well">
        <div>
          <h1>
            GO&nbsp;HOME!
          </h1>
          produced by 4E Project.
        </div>
      </div>
      <p style="color:red">$error_message</p>
      <div class="input">
      <form action="makeGraph.php" method="POST">
        <div class="control-group">
          <select name="name">
            <option value="all">ALL

EOF;
foreach ($name_list as $name) {
    $selected = ($_GET["name"] && $_GET["name"] == $name) ? "selected" : "";
    printf('<option value="%s" %s>%s', $name, $selected, $name);
}
print<<<EOF

          </select>
          <select name="date">

EOF;
foreach ($month_list as $month) {
    $selected = ($_GET["month"] && $_GET["month"] == $month) ? "selected" : "";
    printf('<option value="%s" %s>%s', $month, $selected, $month);
}
print<<<EOF

            <option value="all">ALL(非推奨)
          </select>
          <input class="btn" type="submit" value="output">
      </form>
      </div>
    </div>
    <hr>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js">
    </script>
    <script src="http://s3.amazonaws.com/jetstrap-site/lib/bootstrap/2.2.1/js/bootstrap.js">
    </script>


</body>
</html>
EOF;
