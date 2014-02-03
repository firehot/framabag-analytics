<?php
include "libchart/classes/libchart.php";

include('../config.php');

// query to clean up old inactive accounts
// SELECT * FROM `accounts` WHERE `active` = 0 AND  DATE(created_at) < DATE(NOW() - INTERVAL 3 MONTH)

$sql = "
    SELECT 
        EXTRACT(MONTH FROM created_at) as month, 
        EXTRACT(YEAR FROM created_at) as year, 
        COUNT(id) as SumField
    FROM 
        accounts
    WHERE active = 1
    GROUP BY 
        month, 
        year
    ORDER BY 
        year ASC, 
        month ASC
";
$query = mysql_query($sql);
$chart = new VerticalBarChart(600,500);

$dataSet = new XYDataSet();

$max_month = 0;
$total = 0;
$date_month = '';
while ($line = mysql_fetch_array($query)) {
    if ($line['SumField'] > $max_month) {
        $max_month = $line['SumField'];
        $date_month = $line['month'] . "-" . $line['year'];
    }
    $dataSet->addPoint(new Point($line['month'] . "-" . $line['year'], $line['SumField']));
    $total += $line['SumField'];
}

$chart->setDataSet($dataSet);
$chart->setTitle("Monthly subscriptions");
$chart->render("generated/monthly.png");

$sql = "
    SELECT CAST(created_at AS DATE) as DateField, COUNT(id) as SumField
    FROM accounts
    WHERE active = 1
    GROUP BY CAST(created_at AS DATE)";

$query = mysql_query($sql);
$chart = new HorizontalBarChart(500, 1500);

$dataSet = new XYDataSet();

$max = 0;
$date = '';
while ($line = mysql_fetch_array($query)) {
    if ($line['SumField'] > $max) {
        $max = $line['SumField'];
        $date = $line['DateField'];
    }
    $dataSet->addPoint(new Point($line['DateField'], $line['SumField']));
}

$chart->setDataSet($dataSet);
$chart->setTitle("Daily subscriptions");
$chart->render("generated/daily.png");

?>
<!doctype html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="description" content="">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title>Framabag — Stats</title>
    </head>
    <body>
        <div><img alt="Vertical bars chart" src="generated/monthly.png" style="border: 1px solid gray;"/></div>
        <div><img alt="Vertical bars chart" src="generated/daily.png" style="border: 1px solid gray;"/></div>
        <div>the day with the most subscriptions: <?php echo $date; ?> (<?php echo $max ?>)</div>
        <div>the month with the most subscriptions: <?php echo $date_month; ?> (<?php echo $max_month ?>)</div>
        <div>total of subscriptions: <?php echo $total; ?></div>
    </body>
</html>
