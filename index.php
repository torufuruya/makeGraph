<?php

define('LOG_PATH', 'log/');
define('MAX_HISTORY', 5);

if (isset($_GET["error"])) {
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
<html>
  
  <head>
    <meta charset="utf-8">
    <title>Kaerase Master
    </title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <link href="http://s3.amazonaws.com/jetstrap-site/lib/bootstrap/2.2.1/css/bootstrap.css" rel="stylesheet">
    <style>
      body { padding-top: 60px }
      .input select { margin: 8px }
    </style>
    <link href="http://s3.amazonaws.com/jetstrap-site/lib/bootstrap/2.2.1/css/bootstrap-responsive.css" rel="stylesheet">
    <link rel="shortcut icon" href="http://s3.amazonaws.com/jetstrap-site/lib/bootstrap/2.2.1/ico/favicon.ico">
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="assets/ico/apple-touch-icon-144-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="assets/ico/apple-touch-icon-114-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="assets/ico/apple-touch-icon-72-precomposed.png">
    <link rel="apple-touch-icon-precomposed" href="assets/ico/apple-touch-icon-57-precomposed.png">
  </head>
  
  <body>
    <div class="navbar navbar-fixed-top navbar-inverse">
      <div class="navbar-inner">
        <div class="container-fluid">
          <a class="brand" href="http://toru-furuya/~toru-furuya/mkaerase/">
            K&M
          </a>
          <ul class="nav">
            <li>
              <a href="http://toru-furuya/~toru-furuya/mkaerase/">
                Home
              </a>
            </li>
            <li>
              <a href="#madananimonai">
                Ranking
              </a>
            </li>
            <li>
              <a href="#madananimonai">
                Award
              </a>
            </li>
          </ul>
        </div>
      </div>
    </div>
    <div class="container-fluid">
      <div class="well">
        <div>
          <h2>
            K&M
          </h2>
          <ul>
            <li>K&M shows the graph based on the time you go home.</li>
            <li>you can see trend of your quitting time and compare it with co-workers.</li>
            <li>select member and month. then click output button.</li>
            <li>produced by 4E Project.</li>
          </ul>
        </div>
      </div>
      <p style="color:red">$error_message</p>
      <div class="input">
      <form action="output.php" method="POST">
        <div class="control-group">
          <select name="name">
            <option value="all">ALL

EOF;
foreach ($name_list as $name) {
    $selected = (isset($_GET["name"]) && $_GET["name"] == $name) ? "selected" : "";
    printf('<option value="%s" %s>%s', $name, $selected, $name);
}
print<<<EOF

          </select>
          <select name="date">

EOF;
foreach ($month_list as $month) {
    $selected = (isset($_GET["month"]) && $_GET["month"] == $month) ? "selected" : "";
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
