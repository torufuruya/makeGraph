<?php

/*
const LOG_PATH = 'log/';
const MAX_HISTORY = 5;
 */
define('LOG_PATH', 'log/');
define('MAX_HISTORY', 5);

//get post parameter
$param_name = isset($_POST['name']) ? $_POST['name'] : null;
$param_date = isset($_POST['date']) ? $_POST['date'] : null;
$date = array();
$series = array();

//generate member list
$name_list = glob(LOG_PATH . '*', GLOB_NOSORT);
foreach ($name_list as $key => $name) {
    $name_list[$key] = substr($name, strlen(LOG_PATH));
}

//prepare all member's log
if ($param_name == 'all') {
    foreach ($name_list as $key => $name) {
        $data = $date = $time = array();
        //check does file exist
        $file_name = LOG_PATH . $name;
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
            $time[] = isset($res[1]) ? (float)$res[1] : null;
        }
        $series[$key]['name'] = $name;
        $series[$key]['data'] = $time;
    }
    if (empty($series)) {
        header("Location: http://toru-furuya/~toru-furuya/makeGraph/index.php?error=true");
        exit;
    }
//prepare specific member's log
} else {
    //check does file exist
    $file_name = LOG_PATH . $param_name;
    if (file_exists($file_name)) {
        $file = fopen($file_name, 'r');
    } else {
        header("Location: http://toru-furuya/~toru-furuya/makeGraph/index.php?error=true");
        exit;
    }

    while(!feof($file)) {
        $data[] = fgets($file);
    }
    fclose($file);
    array_pop($data);

    foreach ($data as $key => $item) {
        $res = explode(' ', $item);
        $date[] = isset($res[0]) ? str_replace('-', '/', substr($res[0], 5)) : null;
        $time[] = isset($res[1]) ? (float)$res[1] : null;
    }
    $series[0]['name'] = $param_name;
    $series[0]['data'] = $time;
    error_log(var_export($series, true));
}

//encode to json
$categories = json_encode($date);
$series = json_encode($series);

//generate month list
for ($i = 0; $i < MAX_HISTORY; $i++) {
    $month_list[] = date('Ym', strtotime('-'.$i.'month'));
}

//output graph
print<<<EOF
<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Highcharts Example</title>

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
                text: 'たいしゃのきろく',
                x: -20 //center
            },
            subtitle: {
                text: 'Source: 帰らせくん',
                x: -20
            },
            xAxis: {
                categories: $categories,
            },
            yAxis: {
                title: {
                    text: 'じこく'
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

<form action="makeGraph.php" method="POST">
  <select name="name">
    <option value="all">all
EOF;

foreach ($name_list as $name) {
    echo '<option value="' . $name . '">' . $name;
}

print<<<EOF
  </select>
  <select name="date">
EOF;

foreach ($month_list as $month) {
    echo '<option value="' . $month . '">' . $month;
}

print<<<EOF
  </select>
  <input type="submit" value="出力">
</form>

<div id="container" style="min-width: 400px; height: 400px; margin: 0 auto"></div>

</body>
</html>
EOF;
