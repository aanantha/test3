<?php
// TODO
//        Input validation (client side)
//        Input validation (Server side)


$TP = $winWin = 0;      // we predicted Win and Actual Outcome is Win
$TN = $lossLoss = 0;	// we predicted Loss and Actual Outcome is Loss

$FN = $lossWin = 0;     // we predicted Loss but Actual Outcome is Win
$FP = $winLoss = 0;		// we predicted Win but Actual Outcome is Loss


$total=0;

$recall=0;
$precision=0;
$accuracy=0;

$filter_on=0;

try {

	//echo  time(). '<BR/>';
    $servername = "localhost";
	$username = "root";
	$password = "dxc!2012";
	$dbname = "dxcontinuum";

	// Create connection
	$conn = new mysqli($servername, $username, $password, $dbname);


	// Check connection
	if ($conn->connect_error) {
	    die("Connection failed: " . $conn->connect_error);
	}

	// set timeout to be 2 mins
	if (!$conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 1200)) {
	    die('Setting MYSQLI_OPT_CONNECT_TIMEOUT failed');
	}

	//if(!empty($_GET['customerName']) && !empty($_GET['objectType']) && !empty($_GET['outcome']) && !empty($_GET['fromDate']) && !empty($_GET['toDate']))
	//{
	//	echo "customerName & objectType, etc are not empty";
	//}
	//else{
	//	echo "One of customerName, etc is  empty";
	//}
	$objectType = null;
	$outcome = null;
	if(!empty($_GET['customerName']) && !empty($_GET['objectType']) && !empty($_GET['outcome']) && !empty($_GET['fromDate']) && !empty($_GET['toDate']))
	{
		$filter_on = 1;
		$customerName = $_GET["customerName"];
		$objectType = $_GET["objectType"];
		$outcome = $_GET["outcome"];
		$fromDate = $_GET["fromDate"];
		$toDate = $_GET["toDate"];

		//echo "*********Debug************";
		//echo $customerName;
		//echo $objectType;
		//echo $outcome;
		//echo $fromDate;
		//echo $toDate;
 		//echo "**********Debug***********";

		// get CustomerId for customerName from database customer table
		$customer_sql = "SELECT customerId, CustomerName from customer where CustomerName =" . "'". $customerName . "'";

		$result = $conn->query($customer_sql);

		$customerId = 1;
		//echo "*********************";
		if ($result->num_rows > 0) {
			// output data of each row
			while($row = $result->fetch_assoc()) {
				//print_r($row['customerId'] . '-' . $row['CustomerName']);
				$customerId = $row['customerId'];
			}
		}
		//echo "*********************";



		// OLD GOOD $sql = 'SELECT outcome, ActualOutcome, count(*) cnt from scoretrends where ActualOutcome is not null group by outcome, ActualOutcome';


		// Select Outcome or Firstcome to use
		if ($outcome == 'F'){
			//echo "Outcome to use is Current Outcome( i.e outcome)";
			$mysql = "select FirstOutcome, ActualOutcome, count(*) cnt from scoretrends where customerId ='" . $customerId . "' and CreatedDate between '" . $fromDate . "' AND '" . $toDate . "'";
			// select ObjectType: Leads or Opportunity
			if ($objectType == 'L'){
				//echo "ObjectType is Leads";
				$mysql = $mysql . " And Type = '1'" . " AND ActualOutcome is not null group by FirstOutcome, ActualOutcome;";
			}
			else {
				//echo "ObjectType is Opportunity";
				$mysql = $mysql . " And Type = '2'" . " AND ActualOutcome is not null group by FirstOutcome, ActualOutcome;";
			}
		}
		else {
			//echo "Outcome to use if FirstOutcome";
			$mysql = "select outcome, ActualOutcome, count(*) cnt from scoretrends where customerId ='" . $customerId . "' and CreatedDate between '" . $fromDate . "' AND '" . $toDate . "'";
			if ($objectType == 'L'){
				//echo "ObjectType is Leads";
				$mysql = $mysql . " And Type = '1'" . " AND ActualOutcome is not null group by outcome, ActualOutcome;";
			}
			else {
				//echo "ObjectType is Opportunity";
				$mysql = $mysql . " And Type = '2'" . " AND ActualOutcome is not null group by outcome, ActualOutcome;";
			}
		}
		//echo '\r\n';
		//echo "\r\n Mysql" . $mysql;


		//echo "\nMysql" . $mysql;
	}
	else
	{
		// no filters
		$filter_on = 0;
		$mysql = 'SELECT outcome, ActualOutcome, count(*) cnt from scoretrends where ActualOutcome is not null group by outcome, ActualOutcome';



		//echo "\nMysql" . $mysql;
	}

	// OLD GOOD $sql = 'SELECT outcome, ActualOutcome, count(*) cnt from scoretrends where ActualOutcome is not null group by outcome, ActualOutcome';

	//echo "\nMysql" . $mysql;

	$result = $conn->query($mysql);

	if ($result->num_rows > 0) {

		if ($outcome == 'F') {
			// output data of each row
			while($row = $result->fetch_assoc()) {
					//print_r($row['FirstOutcome'] . '-' . $row['ActualOutcome'] . '-' . $row['cnt']);
					if (strtolower ($row['FirstOutcome']) == 'win'  && strtolower($row['ActualOutcome']) == 'win')
						$TP = $winWin = $row['cnt'];   //TruePositive
					else if (strtolower ($row['FirstOutcome']) == 'loss'  && strtolower($row['ActualOutcome']) == 'win')
						$FN = $lossWin = $row['cnt'];  //FalseNegative
					else if (strtolower ($row['FirstOutcome']) == 'loss'  && strtolower($row['ActualOutcome']) == 'loss')
						$TN = $lossLoss = $row['cnt']; // TrueNegative
					else if (strtolower ($row['FirstOutcome']) == 'win'  && strtolower($row['ActualOutcome']) == 'loss')
						$FP= $winLoss = $row['cnt'];  // FalsePositive

			}
		}
		else {
			// output data of each row
			while($row = $result->fetch_assoc()) {
					//print_r($row['outcome'] . '-' . $row['ActualOutcome'] . '-' . $row['cnt']);
					if (strtolower ($row['outcome']) == 'win'  && strtolower($row['ActualOutcome']) == 'win')
						$TP = $winWin = $row['cnt'];   //TruePositive
					else if (strtolower ($row['outcome']) == 'loss'  && strtolower($row['ActualOutcome']) == 'win')
						$FN = $lossWin = $row['cnt'];  //FalseNegative
					else if (strtolower ($row['outcome']) == 'loss'  && strtolower($row['ActualOutcome']) == 'loss')
						$TN = $lossLoss = $row['cnt']; // TrueNegative
					else if (strtolower ($row['outcome']) == 'win'  && strtolower($row['ActualOutcome']) == 'loss')
						$FP= $winLoss = $row['cnt'];  // FalsePositive

			}


		}

// Test data
//$TN=50;
//$FP=10;
//$FN=5;
//$TP=100;


		$total = $TP + $TN + $FP + $FN;

//echo "TP=" . $TP . " ";
//echo "FP=" . $FP . " ";
//echo "TN=" . $TN. " ";
//echo "FN=" . $FN . " ";

		$accuracy  = ($TP + $TN)/($total);  // How often is the Model correct.
		$recall = $TP/($TP + $FN);			// When it is actual WIN, how often did we predict the WIN.
		$precision    = $TP/($FP + $TP);	// When it predicts WIN how often is it correct?

	}


	// Get date of last run. ****This is wrong We are not putting this date in the table.

	$rundatesql = 'SELECT CreatedDate, MAX( CreatedDate ) mx FROM scoretrends';
	//echo $rundatesql;


	$dateresult = $conn->query($rundatesql);

	//echo $dateresult;

	$rundate = "2015-04-28";

	if ($dateresult->num_rows > 0) {
		// output data of each row
		while($row = $dateresult->fetch_assoc()) {
			//print_r($row['CreatedDate']);
			//$rundate = $row['MAX(CreatedDate)'];
			$rundate = $row['mx'];
		}
	}

	$conn->close();
} catch(Exception $e) {
    echo 'ERROR: ' . $e->getMessage();
}

?>


<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>DxContinuum Accuracy</title>
		<link href="http://www.dxcontinuum.com/wp-content/themes/DxContinuum/css/bootstrap.min.css" rel="stylesheet">
		<link href="http://www.dxcontinuum.com/wp-content/themes/DxContinuum/css,_style.css+style.css+css,_tabs.css+css,_ionicons.css.pagespeed.cc.eynDJR4aGF.css" rel="stylesheet"/>
		<link rel='stylesheet' id='contact-form-7-css' href='http://www.dxcontinuum.com/wp-content/plugins/contact-form-7/includes/css/styles.css?ver=4.0.3' type='text/css' media='all'/>
		<script type='text/javascript' src='http://www.dxcontinuum.com/wp-includes/js/jquery/jquery.js?ver=1.11.1'></script>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
		<link rel="shortcut icon" href="http://www.dxcontinuum.com/wp-content/themes/DxContinuum/images/fevicon.png">
		<link rel="shortcut icon" href="http://www.dxcontinuum.com/wp-content/themes/DxContinuum/images/favicon.ico">
		<style>
			.table-cell {
				font-size: 16px;
			}

			.table-header {
				font-weight: bold;
				font-size: 18px;
			}

			.good {
				color: #8cc63e;
				font-weight: bold;
				font-size: 18px;
				text-align: right;
				padding-right: 5px;
			}

			.bad {
				color: #e84a40;
				font-weight: bold;
				font-size: 18px;
				text-align: right;
				padding-right: 5px;
			}
		</style>
	</head>
	<body>

		<div class="container">
			<div class="d_header">
				<div class="row">
					<div class="col-sm-12">
						<div class="d_logo">
							<a href="http://www.dxcontinuum.com"><img src="http://www.dxcontinuum.com/wp-content/themes/DxContinuum/images/logo.png" pagespeed_url_hash="1373298386"></a>
						</div>
						<div class="d_nav">
							<nav class="navbar navbar-default" role="navigation">
								<div class="navbar-header">
									<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1"> <span class="sr-only">Toggle navigation</span><span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span></button>
									<div id="bs-example-navbar-collapse-1" class="collapse navbar-collapse">
										<ul class="nav nav-pills">
											<li role="presentation"><a href="index.html">TrendScore</a></li>
											<li role="presentation" class="active"><a href="matrix.php">Accuracy</a></li>
											<li role="presentation"><a href="#">Sales Velocity</a></li>
										</ul>
									</div>
								</div>
							</nav>
						</div>
						<div class="clear"></div>
					</div>
				</div>
			</div>
			<table cellspacing="50" class="table table-condensed">
				<tbody>
					<tr>
						<td><h1>Accuracy Dashboard</h1></td><td align="right"><input type="button" id="filter-button" class="btn btn-warning" value="Filters"/></td>
					</tr>
				</tbody>
				<tr>
					<!-- <td>Last Closed Leads Processing Date : <?php echo $rundate; $newDate = date('Y-M-d', $rundate); echo date('d-F-Y', $rundate);?></td> -->
					<td>Last Closed Leads Processing Date : 28-04-2015 </td>

				</tr>

			</table>



			<div class="row well" id="filterPanel" style="display:auto;">

<!-- <?php echo $TN ?> -->
<?php

date_default_timezone_set('America/Los_Angeles');

$currentYear = date("Y");
$beginOfYear = $currentYear . "-01-01";

$customerNameDisplay = "Adobe";
if (!empty($_GET['customerName'])) {
 $customerNameDisplay = $customerName;
}

$fromDateDisplay = $beginOfYear;
if (!empty($_GET['fromDate'])) {
 $fromDateDisplay = $fromDate;
}

//$today = date("Y-m-d");
//$toDateDisplay = $today;
$toDateDisplay = "";
if (!empty($_GET['toDate'])) {
 $toDateDisplay = $toDate;
}

?>


				<form class="col-lg-4" action="matrix.php" method="GET">
					<div class="form-group">
						<label for="customerName">Customer:</label>
						<input type="text"  name="customerName" id="customerName" value="<?php echo $customerNameDisplay;?>">
					</div>

					<div class="form-group">
						<label for="objectType">Object Type</label>
						<select name="objectType" id="objectType">
							<option value="L" selected >Leads</option>
							<option value="O">Opportunity</option>
						</select>
					</div>

					<div class="form-group">
						<label for="outcome">Predicted Outcome</label>
						<select name="outcome" id="outcome">
							<option value="F" selected>First Predicted Outcome</option>
							<option value="C">Current Predicted Outcome</option>
						</select>
					</div>

					<div class="form-group">
						<label for="fromDate">Created From Date:</label>
						<input type="date" name="fromDate" value="<?php echo $fromDateDisplay;?>">
					</div>
					<div class="form-group">
						<label for="toDate"  >Created To Date:  </label>
						<input type="date" id="toDate" name="toDate" value="<?php echo $toDateDisplay;?>">


					</div>
					<button type="submit" class="btn btn-default">Submit</button>


				</form>
			</div>

			<div class="row">
				<div class="col-sm-5">
					<table class="table table-striped table-condensed" style="font-size: 16px !important; border:1px solid #efefef">
						<thead>

							<tr>
								<td> </td>
								<td class="table-header" style="text-align:right;">Predicted Win</td>
								<td class="table-header" style="text-align:right;">Predicted Loss</td>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td class="table-header">Actual Win</td>
								<td class="table-cell good"><?php echo $TP ?></td>
								<td class="table-cell bad"><?php echo $FN ?></td>
							</tr>
							<tr>
								<td class="table-header">Actual Loss</td>
								<td class="table-cell bad"><?php echo $FP ?></td>
								<td class="table-cell good"><?php echo $TN ?></td>
							</tr>
						</tbody>
					</table>
				</div>
				<div class="col-sm-1"></div>
				<div class="col-sm-2">
					<div class="panel panel-default" " >
						<div class="panel-heading">
					   		<h3 class="panel-title">Recall</h3>
					  	</div>
					    <div class="panel-body">
					    	<?php echo round($recall,2); ?>
					    </div>
					</div>
				</div>
				<div class="col-sm-2">
					<div class="panel panel-default">
						<div class="panel-heading">
					   		<h3 class="panel-title">Precision</h3>
					  	</div>
					    <div class="panel-body">
					    	<?php echo round($precision,2); ?>
					    </div>
					</div>
				</div>
				<div class="col-sm-2">
					<div class="panel panel-default">
						<div class="panel-heading">
					   		<h3 class="panel-title">Accuracy</h3>
					  	</div>
					    <div class="panel-body">
					    	<?php echo round($accuracy,2); ?>
					    </div>
					</div>
				</div>
				<div class="col-sm-1"></div>
			</div>
		</div>

	</body>
	<script>

		$('#filter-button').on('click', function() {
			$('#filterPanel').slideToggle();
		});

		<?php
		if ($objectType == null || $objectType == '') $objectType = "L";
		if ($outcome == null || $outcome == '') $outcome = "F";
		?>

		$('#objectType').val("<?php echo $objectType;?>");
		$('#outcome').val("<?php echo $outcome;?>");




		// js function to get URL parameters
		function getParameterByName(name) {
			name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
			var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
				results = regex.exec(location.search);
			return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
		}

		$(document).on('ready',function(){
			var toDate = "";
			var dt = new Date();
			toDate = (dt.getYear() + 1900) + "-";
			var mth = dt.getMonth() + 1;

			if (mth < 10) {
				toDate += "0";

			}
			toDate += mth + "-";
			if (dt.getDate() < 10) {
				toDate += "0";
			}

			toDate += dt.getDate();
			//alert(toDate);

			// we need to do this only if url parameter for date does not exist.
			var todate_parm = getParameterByName('toDate');
			//alert(todate_parm);
			// Do we have  url parameter value for toDate??
			if (todate_parm == "")
				$('#toDate').val(toDate);
		});
	</script>
</html>