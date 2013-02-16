<?php

define('LOG_PATH', 'log/');
define('MAX_HISTORY', 5);

//get post parameter
$param_name = isset($_POST['name']) ? $_POST['name'] : null;
$param_date = isset($_POST['date']) ? $_POST['date'] : null;
$date = array();
$series = array();

//generate member list
$file_list = glob(LOG_PATH . '*', GLOB_NOSORT);
foreach ($file_list as $key => $file_name) {
    if (preg_match('/([a-zA-Z0-9]+)%/', $file_name, $match)) {
        $name_list[$match[1]] = $file_name;
    }
}

//prepare all member's log
if ($param_name == 'all') {
    foreach ($name_list as $name => $file_name) {
        $data = $date = $time = array();
        //check does file exist
        if (file_exists($file_name)) {
            $file = fopen($file_name, 'r');
            if (!$file) { continue; }
        } else {
            continue;
        }

        while(!feof($file)) {
             $data[] = fgets($file);
        }
        fclose($file);
        array_pop($data);

        foreach ($data as $k => $item) {
            $res = explode(' ', $item);
            $date[] = isset($res[0]) ? str_replace('-', '/', substr($res[0], 5)) : null;
            $time[] = isset($res[1]) ? round(str_replace(':', '.', $res[1]), 2) : null;
        }
        $series[] = array(
            'name' => $name,
            'data' => $time,
        );
    }
    if (empty($series)) {
        header("Location: http://toru-furuya/~toru-furuya/makeGraph/index.php?error=ture&name=$param_name&month=$param_month");
        exit;
    }
//prepare specific member's log
} else {
    $data = $date = $time = array();
    //check does file exist
    $file_name = $name_list[$param_name];
    if (!file_exists($file_name)) {
        header("Location: http://localhost/makeGraph/index.php?error=true");
        exit;
    }
    $file = fopen($file_name, 'r');

    while(!feof($file)) {
        $tmp_data = fgets($file);
        if ($param_date == "all") {
            $data[] = $tmp_data;
        } elseif (strpos($tmp_data, $param_date) !== false) {
            $data[] = $tmp_data;
        }
    }
    if (empty($data)) {
        header("Location: http://localhost/makeGraph/index.php?error=ture&name=$param_name&month=$param_date");
        exit;
    }
    fclose($file);

    foreach ($data as $key => $item) {
        $res = explode(' ', $item);
        $tmp_time = isset($res[1]) ? round(str_replace(':', '.', $res[1]), 2) : null;
        $tmp_date = isset($res[0]) ? str_replace('-', '/', substr($res[0], 5)) : null;
        if ($tmp_time < 14.5) {
            $tmp_time = 24 + $tmp_time;
            $tmp_date = date("m/d", strtotime("-1 day", strtotime($tmp_date)));
        }
        $date[] = $tmp_date;
        $time[] = $tmp_time;
    }
    $series[0]['name'] = $param_name;
    $series[0]['data'] = $time;
}

//encode to json
$categories = json_encode($date);
$series = json_encode($series);

//generate month list
for ($i = 0; $i < MAX_HISTORY; $i++) {
    $month_list[] = date('Y-m', strtotime('-'.$i.'month'));
}

//output graph
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

    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
    <script type="text/javascript">
    $(function () {
        var chart;
        $(document).ready(function() {
            chart = new Highcharts.Chart({
                chart: {
                    renderTo: 'container',
                    type: 'line',
                    marginRight: 130,
                    marginBottom: 25
                },
                title: {
                    text: 'Quitting Time',
                    x: -20 //center
                },
                subtitle: {
                    text: 'Source: 帰らせマスターBot',
                    x: -20
                },
                xAxis: {
                    categories: $categories,
                },
                yAxis: {
                    title: {
                        text: 'time'
                    },
                    plotLines: [{
                        value: 0,
                        width: 1,
                        color: '#808080'
                    }]
                },
                tooltip: {
                    formatter: function() {
                        return '<b>'+ this.series.name +'</b><br/>'+
                        this.x +': '+ this.y;
                    }
                },
                legend: {
                    layout: 'vertical',
                    align: 'right',
                    verticalAlign: 'top',
                    x: -10,
                    y: 100,
                    borderWidth: 0
                },
                series: $series,
            });
        });
    
    });
    </script>
  </head>

  <body>
    <script src="http://code.highcharts.com/highcharts.js"></script>
    <script src="http://code.highcharts.com/modules/exporting.js"></script>

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
      <div class="input">
      <form action="makeGraph.php" method="POST">
        <div class="control-group">
          <select name="name">
            <option value="all">ALL
EOF;

foreach ($name_list as $name => $file_name) {
    $selected = ($param_name == $name) ? "selected" : "";
    printf('<option value="%s" %s>%s', $name, $selected, $name);
}

print<<<EOF
          </select>
          <select name="date">
EOF;

foreach ($month_list as $month) {
    $selected = ($param_date == $month) ? "selected" : "";
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
    <div id="container" style="min-width: 400px; height: 400px; margin: 0 auto"></div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js">
    </script>
    <script src="http://s3.amazonaws.com/jetstrap-site/lib/bootstrap/2.2.1/js/bootstrap.js">
    </script>


  </body>
</html>
EOF;
