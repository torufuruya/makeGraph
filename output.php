<?php

define('LOG_PATH', 'log/');
define('MAX_HISTORY', 5);
define('MIN_WORK_TIME', 14.5);

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

//generate month list
for ($i = 0; $i < MAX_HISTORY; $i++) {
    $month_list[] = date('Y-m', strtotime('-'.$i.'month'));
}

//prepare all member's log
if ($param_name == 'all') {
    $order = -1;
    foreach ($name_list as $name => $file_name) {
        $order = $order + 1;
        //check if log file exists
        if (!file_exists($file_name)) {
            continue;
        }
        $file = fopen($file_name, 'r');

        $data = array();
        //create data from log file
        while(!feof($file)) {
            $tmp_data = fgets($file);
            if ($param_date == "all") {
                $data[] = $tmp_data;
            } elseif (strpos($tmp_data, $param_date) !== false) {
                $data[] = $tmp_data;
            }
        }
        if (empty($data)) {
            continue;
        }
        fclose($file);

        $date = $time = array();
        foreach ($data as $key => $item) {
            $res = explode(' ', $item);
            $tmp_time = isset($res[1]) ? round(str_replace(':', '.', $res[1]), 3) : null;
            $tmp_date = isset($res[0]) ? str_replace('-', '/', substr($res[0], 5)) : null;
            //convert date and time e.g.) 2/13 01:00 -> 2/12 25:00
            if ($tmp_time < MIN_WORK_TIME) {
                $tmp_time = 24 + $tmp_time;
                $tmp_date = date("m/d", strtotime("-1 day", strtotime($tmp_date)));
            }
            $date[] = $tmp_date;
            $time[] = $tmp_time;
        }
        $series[$order]['name'] = $name;
        $series[$order]['data'] = $time;
        $date_list[$name] = $date;
        $date_count_list[$name] = count($date_list[$name]);
    }
    if (empty($series)) {
        header("Location: http://toru-furuya/toru-furuya/mkaerase/index.php?error=true&month=$param_month");
        exit;
    }

    //use the longest date for common date
    arsort($date_count_list);
    $keys = array_keys($date_count_list);
    $date = $date_list[$keys[0]];

//prepare specific member's log
} else {
    $data = $date = $time = array();

    //check if log file exists
    $file_name = $name_list[$param_name];
    if (!file_exists($file_name)) {
        header("Location: http://toru-furuya/~toru-furuya/mkaerase/index.php?error=true&name=$param_name&month=$param_date");
        exit;
    }
    $file = fopen($file_name, 'r');

    //create data from log file
    while(!feof($file)) {
        $tmp_data = fgets($file);
        if ($param_date == "all") {
            $data[] = $tmp_data;
        } elseif (strpos($tmp_data, $param_date) !== false) {
            $data[] = $tmp_data;
        }
    }
    if (empty($data)) {
        header("Location: http://toru-furuya/~toru-furuya/mkaerase/index.php?error=true&name=$param_name&month=$param_date");
        exit;
    }
    fclose($file);

    foreach ($data as $key => $item) {
        $res = explode(' ', $item);
        $tmp_time = isset($res[1]) ? round(str_replace(':', '.', $res[1]), 2) : null;
        $tmp_date = isset($res[0]) ? str_replace('-', '/', substr($res[0], 5)) : null;
        //convert date and time e.g.) 2/13 01:00 -> 2/12 25:00
        if ($tmp_time < MIN_WORK_TIME) {
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
error_log(var_export($series, true));

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
      <div class="input">
      <form action="output.php" method="POST">
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
    <script type="text/javascript" src="js/themes/gray.js"></script>


  </body>
</html>
EOF;
