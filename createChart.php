<?php
	
	//establish connection to database
	require_once 'database/DBInfo.php';
	$db_server = new mysqli($db_hostname, $db_username, $db_password, $db_database);
	
	if ($db_server->connect_errno) {
		// connect_error returns the a string of the error from the latest sql command
		print ("<h1> There was an error:</h1> <p> " . $db_server->connect_error . "</p>");
	}
	
	//obtain report filters
	$reportName = $_POST['reportName'];
 	$report = $_POST['reportVal']; 
 	$year = $_POST['year']; 
 	$level = $_POST['academicLevel']; 
 	$gender = $_POST['gender']; 
 	$region = ( $_POST['region'] == 'All' ? $_POST['region'] : strtolower($_POST['region']) ); 
 	$country = ( $_POST['country'] == 'All' ? $_POST['country'] : strtolower($_POST['country']) ); 
 	$program = $_POST['program']; 
 	$college = ( $_POST['college'] == 'All' ? $_POST['college'] : strtolower($_POST['college']) );
 	
 	$errorCount = 0;
 	$colors = "['#FF9900', '#EBB461' , '#FFCC00', '#D0D0D0', '#FF3333', '#FF6666', '#00CC99', '#CCFF99', '#CC9966', '#CC6600', '#993333']";
 	
 	//functions 
 	/** selects the proper joins for the user's query */
 	function makeJoins($gender, $region, $country, $program, $college){
 		$q = "";
 		
 		if ($gender != 'All' && $gender != '0') {
			$q = $q." ,student";
		}
		
		if ( ( ($region != 'All' && $country != '0') || ($country != 'All' && $country != '0') ) && $gender == '0') {
			$q = $q." ,country";
		} else if ( ( ($region != 'All' && $country != '0') || ($country != 'All' && $country != '0') ) && $gender == 'All') {
			$q = $q." ,student, country";
		} else if ( ( ($region != 'All' && $country != '0') || ($country != 'All' && $country != '0') ) && $gender != 'All') {
			$q = $q." ,country";
		}
		
		if ($program != 'All' && $program != '0') {
			$q = $q." ,programs";
		}
		
		if ($college != 'All' && $college != '0') {
			$q = $q." ,academic_info";
		}
		
		return $q;
 	
 	}
 	
 	/** selects the proper where conditions for the user's query */
 	function makeQuery($level, $gender, $region, $country, $program, $college){
 		$q = "";
 		$and = false;
 		
 		//input where conditions
 		if ($level != 'All') {
			$q = $q." academic_level='".$level."'";
			$and = true;
		} 
		
		if ($gender != 'All' && !$and) {
			$q = $q." gender='".$gender."' and semester.ut_eid=student.ut_eid";
			$and = true;
		} else if ($gender != 'All') {
			$q = $q." and gender='".$gender."' and semester.ut_eid=student.ut_eid";
		}
		
		if ($region != 'All' && !$and) {
			$q = $q." Region_of_citizenship='".$region."' and country.country_code=student.country_code and semester.ut_eid=student.ut_eid";
			$and = true;
		} else if ($region != 'All') {
			$q = $q." and Region_of_citizenship='".$region."' and country.country_code=student.country_code and semester.ut_eid=student.ut_eid";
		}
		
		if ($country != 'All' && !$and) {
			$q = $q." country.country_name='".$country."' and country.country_code=student.country_code and semester.ut_eid=student.ut_eid";
			$and = true;
		} else if ($country != 'All') {
			$q = $q." and country.country_name='".$country."' and country.country_code=student.country_code and semester.ut_eid=student.ut_eid";
		}
		
		if ($program != 'All' && !$and && $program == '1') {
			$q = $q." (programs.program_code='1' or programs.program_code='3' or programs.program_code='4' or programs.program_code='5') and semester.program_code=programs.program_code";
			$and = true;
		} else if ($program != 'All' && $program == '1') {
			$q = $q." and (programs.program_code='1' or programs.program_code='3' or programs.program_code='4' or programs.program_code='5') and semester.program_code=programs.program_code";
		} else if ($program != 'All' && !$and && ($program == '3' || $program == '4') ) {
			$q = $q." (programs.program_code='".$program."' or programs.program_code='5') and semester.program_code=programs.program_code";
			$and = true;
		} else if ($program != 'All' && ($program == '3' || $program == '4') ) {
			$q = $q." and (programs.program_code='".$program."' or programs.program_code='5') and semester.program_code=programs.program_code";
		} else if ($program != 'All' && !$and) {
			$q = $q." programs.program_code='".$program."' and semester.program_code=programs.program_code";
			$and = true;
		} else if ($program != 'All') {
			$q = $q." and programs.program_code='".$program."' and semester.program_code=programs.program_code";
		}
		
		if ($college != 'All' && !$and) {
			$q = $q." academic_info.school_name='".$college."' and semester.major_code=academic_info.major_code";
			$and = true;
		} else if ($college != 'All') {
			$q = $q." and academic_info.school_name='".$college."' and semester.major_code=academic_info.major_code";
		}
		
		return $q;
 	}
 	
 	
 	//begin creating response
 	$response = '<script language="javascript"> function graph() {';
 	
 	//student distribution by academic_level
 	if ($report == '1'){
		//obtain undergrads
		$query = "select academic_level, count(*) as count from semester";
		//select joins
		$query = $query.makeJoins($gender, $region, $country, $program, $college);
		$query = $query." where";
		//input where conditions
		$query = $query.makeQuery('All', $gender, $region, $country, $program, $college);
		//finish query
		$pos = strpos($query, '=');
		if ($pos === false){
		$query = $query." year=".$year." and semester='Fall' and academic_level='UG' group by academic_level;"; 
		} else {
			$query = $query." and year=".$year." and semester='Fall' and academic_level='UG' group by academic_level;"; 
		}
		
		echo ($query."   ");
		
		$total = 0;
		
		//execute query and store results
		$stmt = $db_server->query($query);
		$queryResult = $stmt->fetch_array(MYSQLI_ASSOC);
		$ugNum = ($queryResult['count'] > 0 ? $queryResult['count'] : 0);
		
		//obtain graduates
		$query = "select academic_level, count(*) as count from semester";
		//select joins
		$query = $query.makeJoins($gender, $region, $country, $program, $college);
		$query = $query." where";
		//input where conditions
		$query = $query.makeQuery('All', $gender, $region, $country, $program, $college);
		//finish query
		$pos = strpos($query, '=');
		if ($pos === false){
		$query = $query." year=".$year." and semester='Fall' and academic_level='G' group by academic_level;"; 
		} else {
			$query = $query." and year=".$year." and semester='Fall' and academic_level='G' group by academic_level;"; 
		}
		
		//execute query and store results
		$stmt = $db_server->query($query);
		$queryResult = $stmt->fetch_array(MYSQLI_ASSOC);
		$gNum = ($queryResult['count'] > 0 ? $queryResult['count'] : 0);
		
		//obtain scholars
		$query = "select academic_level, count(*) as count from semester";
		//select joins
		$query = $query.makeJoins($gender, $region, $country, $program, $college);
		$query = $query." where";
		//input where conditions
		$query = $query.makeQuery('All', $gender, $region, $country, $program, $college);
		//finish query
		$pos = strpos($query, '=');
		if ($pos === false){
		$query = $query." year=".$year." and semester='Fall' and academic_level='S' group by academic_level;"; 
		} else {
			$query = $query." and year=".$year." and semester='Fall' and academic_level='S' group by academic_level;"; 
		}
		
		//execute query and store results
		$stmt = $db_server->query($query);
		$queryResult = $stmt->fetch_array(MYSQLI_ASSOC);
		$sNum = ($queryResult['count'] > 0 ? $queryResult['count'] : 0);
		
		$total = $ugNum + $gNum + $sNum;
		
		//create chart
		$response = $response."Highcharts.setOptions({ colors:".$colors." });"
		."$('#graphContainer').highcharts({ chart: { type: 'column' }, credits: { position: { align: 'right', verticalAlign: 'bottom'},"
		."text: 'Total Students:".$total."', href: '#', style: { cursor: 'cursor', color: '#3E576F', fontSize: '15px'} },"
		."title: { text: '".$reportName." (".$year.")' }, xAxis: { categories: ['Academic Level'] }, yAxis: { title: { text: 'Number of Students'} },"
		."series: [{name: 'Undergraduate', data: [".$ugNum."]}, { name: 'Graduate', data: [".$gNum."] }, { name: 'Scholar', data: [".$sNum."] } ],  });";
  	}
  	
  	//student distribution by classification
  	else if ($report == '2'){
  		//obtain freshman
		$query = "select classification as class, count(*) as count from semester";
		//select joins
		$query = $query.makeJoins($gender, $region, $country, $program, $college);
		$query = $query." where";
		//input where conditions
		$query = $query.makeQuery($level, $gender, $region, $country, $program, $college);
		//finish query
		$pos = strpos($query, '=');
		if ($pos === false){
		$query = $query." year=".$year." and semester='Fall' and classification='freshman' group by classification;"; 
		} else {
			$query = $query." and year=".$year." and semester='Fall' and classification='freshman' group by classification;"; 
		}
		
		echo ($query."      ");
		
		//execute query and store results
		$stmt = $db_server->query($query);
		$queryResult = $stmt->fetch_array(MYSQLI_ASSOC);
		$freshmanNum = ($queryResult['count'] > 0 ? $queryResult['count'] : 0);
		
		//obtain sophomores
		$query = "select classification as class, count(*) as count from semester";
		//select joins
		$query = $query.makeJoins($gender, $region, $country, $program, $college);
		$query = $query." where";
		//input where conditions
		$query = $query.makeQuery($level, $gender, $region, $country, $program, $college);
		//finish query
		$pos = strpos($query, '=');
		if ($pos === false){
		$query = $query." year=".$year." and semester='Fall' and classification='sophomore' group by classification;"; 
		} else {
			$query = $query." and year=".$year." and semester='Fall' and classification='sophomore' group by classification;"; 
		}
		
		//execute query and store results
		$stmt = $db_server->query($query);
		$queryResult = $stmt->fetch_array(MYSQLI_ASSOC);
		$sophomoreNum = ($queryResult['count'] > 0 ? $queryResult['count'] : 0);
		
		//obtain juniors
		$query = "select classification as class, count(*) as count from semester";
		//select joins
		$query = $query.makeJoins($gender, $region, $country, $program, $college);
		$query = $query." where";
		//input where conditions
		$query = $query.makeQuery($level, $gender, $region, $country, $program, $college);
		//finish query
		$pos = strpos($query, '=');
		if ($pos === false){
		$query = $query." year=".$year." and semester='Fall' and classification='junior' group by classification;"; 
		} else {
			$query = $query." and year=".$year." and semester='Fall' and classification='junior' group by classification;"; 
		}
		
		//execute query and store results
		$stmt = $db_server->query($query);
		$queryResult = $stmt->fetch_array(MYSQLI_ASSOC);
		$juniorNum = ($queryResult['count'] > 0 ? $queryResult['count'] : 0);
		
		//obtain seniors
		$query = "select classification as class, count(*) as count from semester";
		//select joins
		$query = $query.makeJoins($gender, $region, $country, $program, $college);
		$query = $query." where";
		//input where conditions
		$query = $query.makeQuery($level, $gender, $region, $country, $program, $college);
		//finish query
		$pos = strpos($query, '=');
		if ($pos === false){
		$query = $query." year=".$year." and semester='Fall' and classification='senior' group by classification;"; 
		} else {
			$query = $query." and year=".$year." and semester='Fall' and classification='senior' group by classification;"; 
		}
		
		//execute query and store results
		$stmt = $db_server->query($query);
		$queryResult = $stmt->fetch_array(MYSQLI_ASSOC);
		$seniorNum = ($queryResult['count'] > 0 ? $queryResult['count'] : 0);
		
		//obtain Masters
		$query = "select classification as class, count(*) as count from semester";
		//select joins
		$query = $query.makeJoins($gender, $region, $country, $program, $college);
		$query = $query." where";
		//input where conditions
		$query = $query.makeQuery($level, $gender, $region, $country, $program, $college);
		//finish query
		$pos = strpos($query, '=');
		if ($pos === false){
		$query = $query." year=".$year." and semester='Fall' and classification='masters' group by classification;"; 
		} else {
			$query = $query." and year=".$year." and semester='Fall' and classification='masters' group by classification;"; 
		}
		
		//execute query and store results
		$stmt = $db_server->query($query);
		$queryResult = $stmt->fetch_array(MYSQLI_ASSOC);
		$mastersNum = ($queryResult['count'] > 0 ? $queryResult['count'] : 0);
		
		//obtain Doctoral
		$query = "select classification as class, count(*) as count from semester";
		//select joins
		$query = $query.makeJoins($gender, $region, $country, $program, $college);
		$query = $query." where";
		//input where conditions
		$query = $query.makeQuery($level, $gender, $region, $country, $program, $college);
		//finish query
		$pos = strpos($query, '=');
		if ($pos === false){
		$query = $query." year=".$year." and semester='Fall' and classification='doctoral' group by classification;"; 
		} else {
			$query = $query." and year=".$year." and semester='Fall' and classification='doctoral' group by classification;"; 
		}
		
		//execute query and store results
		$stmt = $db_server->query($query);
		$queryResult = $stmt->fetch_array(MYSQLI_ASSOC);
		$doctoralNum = ($queryResult['count'] > 0 ? $queryResult['count'] : 0);
		
		//obtain Law
		$query = "select classification as class, count(*) as count from semester";
		//select joins
		$query = $query.makeJoins($gender, $region, $country, $program, $college);
		$query = $query." where";
		//input where conditions
		$query = $query.makeQuery($level, $gender, $region, $country, $program, $college);
		//finish query
		$pos = strpos($query, '=');
		if ($pos === false){
		$query = $query." year=".$year." and semester='Fall' and classification='law' group by classification;"; 
		} else {
			$query = $query." and year=".$year." and semester='Fall' and classification='law' group by classification;"; 
		}
		
		//execute query and store results
		$stmt = $db_server->query($query);
		$queryResult = $stmt->fetch_array(MYSQLI_ASSOC);
		$lawNum = ($queryResult['count'] > 0 ? $queryResult['count'] : 0);
		
		//obtain PharmD
		$query = "select classification as class, count(*) as count from semester";
		//select joins
		$query = $query.makeJoins($gender, $region, $country, $program, $college);
		$query = $query." where";
		//input where conditions
		$query = $query.makeQuery($level, $gender, $region, $country, $program, $college);
		//finish query
		$pos = strpos($query, '=');
		if ($pos === false){
		$query = $query." year=".$year." and semester='Fall' and classification='pharmd' group by classification;"; 
		} else {
			$query = $query." and year=".$year." and semester='Fall' and classification='pharmd' group by classification;"; 
		}
		
		//execute query and store results
		$stmt = $db_server->query($query);
		$queryResult = $stmt->fetch_array(MYSQLI_ASSOC);
		$pharmNum = ($queryResult['count'] > 0 ? $queryResult['count'] : 0);
		
		
		//obtain Scholars
		$query = "select classification as class, count(*) as count from semester";
		//select joins
		$query = $query.makeJoins($gender, $region, $country, $program, $college);
		$query = $query." where";
		//input where conditions
		$query = $query.makeQuery($level, $gender, $region, $country, $program, $college);
		//finish query
		$pos = strpos($query, '=');
		if ($pos === false){
		$query = $query." year=".$year." and semester='Fall' and classification='scholar' group by classification;"; 
		} else {
			$query = $query." and year=".$year." and semester='Fall' and classification='scholar' group by classification;"; 
		}
		
		echo ($query."      ");
		
		//execute query and store results
		$stmt = $db_server->query($query);
		$queryResult = $stmt->fetch_array(MYSQLI_ASSOC);
		$scholarNum = ($queryResult['count'] > 0 ? $queryResult['count'] : 0);

		$total = $freshmanNum + $sophomoreNum + $juniorNum + $seniorNum + $mastersNum + $doctoralNum + $lawNum + $pharmNum + $scholarNum;
		
		//create chart
		$response = $response."Highcharts.setOptions({ colors:".$colors." });"
		."$('#graphContainer').highcharts({ chart: { type: 'column' }, credits: { position: { align: 'right', verticalAlign: 'bottom', y: .45},"
		."text: 'Total Students:".$total."', href: '#', style: { cursor: 'cursor', color: '#3E576F', fontSize: '15px'} }," 
		."title: { text: '".$reportName." (".$year.")' },"
		."xAxis: { categories: ['Classification'] }, yAxis: { title: { text: 'Number of Students'} },"
		."series: [{name: 'Freshman', data: [".$freshmanNum."]}, { name: 'Sophomore', data: [".$sophomoreNum."] },"
		." { name: 'Junior', data: [".$juniorNum."] }, { name: 'Senior', data: [".$seniorNum."] },"
		." { name: 'Masters', data: [".$mastersNum."] }, { name: 'Doctoral', data: [".$doctoralNum."] }, { name: 'Law', data: [".$lawNum."] },"
		."{ name: 'PharmD', data: [".$pharmNum."] }, { name: 'Scholar', data: [".$scholarNum."] } ]});";
  		
  	}
  	
  	//student distribution by gender
 	else if ($report == '3'){
		//build querry
		$query = "select student.gender as gender, count(*) as count from semester, student";
		//select joins
		$query = $query.makeJoins('0', $region, $country, $program, $college);
		$query = $query." where";
		//input where conditions
		$query = $query.makeQuery($level, 'All', $region, $country, $program, $college);
		//finish query
		$pos = strpos($query, '=');
		if ($pos === false){
		$query = $query." year=".$year." and semester='Fall' and semester.ut_eid=student.ut_eid group by student.gender;"; 
		} else {
			$query = $query." and year=".$year." and semester='Fall' and semester.ut_eid=student.ut_eid group by student.gender;"; 
		}
		
		//echo ($query."   ");
		
		$val1 = $val2 = 0;
		$colName1 = $colName2 = "";
		
		//execute query and store results
		$stmt = $db_server->query($query);
		$queryResult = $stmt->fetch_array(MYSQLI_ASSOC);
		$val1 = ($queryResult['count'] > 0 ? $queryResult['count'] : 0);
		$colName1 = ($queryResult['gender'] == 'm'? 'Male' : 'Female');
		$queryResult = $stmt->fetch_array(MYSQLI_ASSOC);
		$val2 = ($queryResult['count'] > 0 ? $queryResult['count'] : 0);
		$colName2 = ($colName1 == 'Male'? 'Female' : 'Male');
		
		$total = $val1 + $val2;
		
		//$val1 = ((double)$val1)/$total * 100;
		//$val2 = ((double)$val2)/$total * 100;
		
		//create chart
		$response = $response."Highcharts.setOptions({ colors:".$colors." });"
		."$('#graphContainer').highcharts({
        chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false
            },
            title: {
                text: '".$reportName." (".$year.")'
            },
            tooltip: {
        	    pointFormat: '{series.name}: <b>{point.percentage:.0f}%</b> <br /> Number of Students: <b>{point.y}</b>',
            	valueDecimals: 0
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true,
                        color: '#000000',
                        connectorColor: '#000000',
                        formatter: function() {
                            return '<b>'+ this.point.name +'</b>: '+ Math.round(this.percentage) +' %';
                        }
                    }
                }
            },
            series: [{
                type: 'pie',
                name: 'Percent of Students',
                data: [
                    ['".$colName1."', ".$val1."],
                    {
                        name: '".$colName2."',
                        y: ".$val2.",
                        sliced: true,
                        selected: true
                    },
                ]
            }], credits: { position: { align: 'right', verticalAlign: 'bottom'},"
		."text: 'Total Students:".$total."', href: '#', style: { cursor: 'cursor', color: '#3E576F', fontSize: '15px'} }
        });";
  	}
 	//student distribution by college
 	else if ($report == '4'){
 		$total = 0;
 		
 		//obtain Engineering
 		$query = "select academic_info.school_name as school, count(*) as count from semester, academic_info";
 		//make join
		$query = $query.makeJoins($gender, $region, $country, $program, '0');
 		$query = $query." where";
 		//input where conditions
 		$query = $query.makeQuery($level, $gender, $region, $country, $program, 'All');
 		//finish query
 		$pos = strpos($query, '=');
 		if ($pos === false){
 			$query = $query." year=".$year." and semester.major_code = academic_info.major_code and academic_info.school_name = 'cockrell school of engineering'
 			group by academic_info.school_name;";
 		} else {
 			$query = $query." and year=".$year." and semester.major_code = academic_info.major_code and academic_info.school_name = 'cockrell school of engineering'
 			group by academic_info.school_name;"; 
 		}
 		
 		//echo ($query."       ");
 		
 		//execute query and store results
 		$stmt = $db_server->query($query);
 		$queryResult = $stmt->fetch_array(MYSQLI_ASSOC);
 		$engineeringNum = ($queryResult['count'] > 0 ? $queryResult['count'] : 0);
 		$total += $engineeringNum;
 		
 		//obtain Communication
 		$query = "select academic_info.school_name as school, count(*) as count from semester, academic_info";
 		//make join
 		$query = $query.makeJoins($gender, $region, $country, $program, '0');
 		$query = $query." where";
 		//input where conditions
 		$query = $query.makeQuery($level, $gender, $region, $country, $program, 'All');
 		//finish query
 		$pos = strpos($query, '=');
 		if ($pos === false){
 			$query = $query." year=".$year." and semester.major_code = academic_info.major_code and academic_info.school_name = 'college of communication'
 			group by academic_info.school_name;";
 		} else {
 			$query = $query." and year=".$year." and semester.major_code = academic_info.major_code and academic_info.school_name = 'college of communication'
 			group by academic_info.school_name;"; 
 		}
 		
 		//echo ($query."       ");
 		
 		//execute query and store results
 		$stmt = $db_server->query($query);
 		$queryResult = $stmt->fetch_array(MYSQLI_ASSOC);
 		$communicationNum = ($queryResult['count'] > 0 ? $queryResult['count'] : 0);
 		$total += $communicationNum;
 		
 		//obtain Education
 		$query = "select academic_info.school_name as school, count(*) as count from semester, academic_info";
 		//make join
 		$query = $query.makeJoins($gender, $region, $country, $program, '0');
 		$query = $query." where";
 		//input where conditions
 		$query = $query.makeQuery($level, $gender, $region, $country, $program, 'All');
 		//finish query
 		$pos = strpos($query, '=');
 		if ($pos === false){
 			$query = $query." year=".$year." and semester.major_code = academic_info.major_code and academic_info.school_name = 'college of education'
 			group by academic_info.school_name;";
 		} else {
 			$query = $query." and year=".$year." and semester.major_code = academic_info.major_code and academic_info.school_name = 'college of education'
 			group by academic_info.school_name;"; 
 		}
 		
 		//echo ($query."       ");
 		
 		//execute query and store results
 		$stmt = $db_server->query($query);
 		$queryResult = $stmt->fetch_array(MYSQLI_ASSOC);
 		$educationNum = ($queryResult['count'] > 0 ? $queryResult['count'] : 0);
 		$total += $educationNum;
 		
 		//obtain Fine Arts
 		$query = "select academic_info.school_name as school, count(*) as count from semester, academic_info";
 		//make join
 		$query = $query.makeJoins($gender, $region, $country, $program, '0');
 		$query = $query." where";
 		//input where conditions
 		$query = $query.makeQuery($level, $gender, $region, $country, $program, 'All');
 		//finish query
 		$pos = strpos($query, '=');
 		if ($pos === false){
 			$query = $query." year=".$year." and semester.major_code = academic_info.major_code and academic_info.school_name = 'college of fine arts'
 			group by academic_info.school_name;";
 		} else {
 			$query = $query." and year=".$year." and semester.major_code = academic_info.major_code and academic_info.school_name = 'college of fine arts'
 			group by academic_info.school_name;"; 
 		}
 		
 		//echo ($query."       ");
 		
 		//execute query and store results
 		$stmt = $db_server->query($query);
 		$queryResult = $stmt->fetch_array(MYSQLI_ASSOC);
 		$fineArtsNum = ($queryResult['count'] > 0 ? $queryResult['count'] : 0);
 		$total += $fineArtsNum;
 		
 		//obtain Liberal Arts
 		$query = "select academic_info.school_name as school, count(*) as count from semester, academic_info";
 		//make join
 		$query = $query.makeJoins($gender, $region, $country, $program, '0');
 		$query = $query." where";
 		//input where conditions
 		$query = $query.makeQuery($level, $gender, $region, $country, $program, 'All');
 		//finish query
 		$pos = strpos($query, '=');
 		if ($pos === false){
 			$query = $query." year=".$year." and semester.major_code = academic_info.major_code and academic_info.school_name = 'college of liberal arts'
 			group by academic_info.school_name;";
 		} else {
 			$query = $query." and year=".$year." and semester.major_code = academic_info.major_code and academic_info.school_name = 'college of liberal arts'
 			group by academic_info.school_name;"; 
 		}
 		
 		//echo ($query."       ");
 		
 		//execute query and store results
 		$stmt = $db_server->query($query);
 		$queryResult = $stmt->fetch_array(MYSQLI_ASSOC);
 		$liberalArtsNum = ($queryResult['count'] > 0 ? $queryResult['count'] : 0);
 		$total += $liberalArtsNum;
 		
 		//obtain Natural Sciences
 		$query = "select academic_info.school_name as school, count(*) as count from semester, academic_info";
 		//make join
 		$query = $query.makeJoins($gender, $region, $country, $program, '0');
 		$query = $query." where";
 		//input where conditions
 		$query = $query.makeQuery($level, $gender, $region, $country, $program, 'All');
 		//finish query
 		$pos = strpos($query, '=');
 		if ($pos === false){
 			$query = $query." year=".$year." and semester.major_code = academic_info.major_code and academic_info.school_name = 'college of natural sciences'
 			group by academic_info.school_name;";
 		} else {
 			$query = $query." and year=".$year." and semester.major_code = academic_info.major_code and academic_info.school_name = 'college of natural sciences'
 			group by academic_info.school_name;"; 
 		}
 		
 		//echo ($query."       ");
 		
 		//execute query and store results
 		$stmt = $db_server->query($query);
 		$queryResult = $stmt->fetch_array(MYSQLI_ASSOC);
 		$naturalSciencesNum = ($queryResult['count'] > 0 ? $queryResult['count'] : 0);
 		$total += $naturalSciencesNum;
 		
 		//obtain Pharmacy
 		$query = "select academic_info.school_name as school, count(*) as count from semester, academic_info";
 		//make join
 		$query = $query.makeJoins($gender, $region, $country, $program, '0');
 		$query = $query." where";
 		//input where conditions
 		$query = $query.makeQuery($level, $gender, $region, $country, $program, 'All');
 		//finish query
 		$pos = strpos($query, '=');
 		if ($pos === false){
 			$query = $query." year=".$year." and semester.major_code = academic_info.major_code and academic_info.school_name = 'college of pharmacy'
 			group by academic_info.school_name;";
 		} else {
 			$query = $query." and year=".$year." and semester.major_code = academic_info.major_code and academic_info.school_name = 'college of pharmacy'
 			group by academic_info.school_name;"; 
 		}
 		
 		//echo ($query."       ");
 		
 		//execute query and store results
 		$stmt = $db_server->query($query);
 		$queryResult = $stmt->fetch_array(MYSQLI_ASSOC);
 		$pharmacyNum = ($queryResult['count'] > 0 ? $queryResult['count'] : 0);
 		$total += $pharmacyNum;
 		
 		//obtain Medical School
 		$query = "select academic_info.school_name as school, count(*) as count from semester, academic_info";
 		//make join
 		$query = $query.makeJoins($gender, $region, $country, $program, '0');
 		$query = $query." where";
 		//input where conditions
 		$query = $query.makeQuery($level, $gender, $region, $country, $program, 'All');
 		//finish query
 		$pos = strpos($query, '=');
 		if ($pos === false){
 			$query = $query." year=".$year." and semester.major_code = academic_info.major_code and academic_info.school_name = 'dell medical school'
 			group by academic_info.school_name;";
 		} else {
 			$query = $query." and year=".$year." and semester.major_code = academic_info.major_code and academic_info.school_name = 'dell medical school'
 			group by academic_info.school_name;"; 
 		}
 		
 		//echo ($query."       ");
 		
 		//execute query and store results
 		$stmt = $db_server->query($query);
 		$queryResult = $stmt->fetch_array(MYSQLI_ASSOC);
 		$medSchoolNum = ($queryResult['count'] > 0 ? $queryResult['count'] : 0);
 		$total += $medSchoolNum;
 		
 		//obtain Graduate
 		$query = "select academic_info.school_name as school, count(*) as count from semester, academic_info";
 		//make join
 		$query = $query.makeJoins($gender, $region, $country, $program, '0');
 		$query = $query." where";
 		//input where conditions
 		$query = $query.makeQuery($level, $gender, $region, $country, $program, 'All');
 		//finish query
 		$pos = strpos($query, '=');
 		if ($pos === false){
 			$query = $query." year=".$year." and semester.major_code = academic_info.major_code and academic_info.school_name = 'graduate school'
 			group by academic_info.school_name;";
 		} else {
 			$query = $query." and year=".$year." and semester.major_code = academic_info.major_code and academic_info.school_name = 'graduate school'
 			group by academic_info.school_name;"; 
 		}
 		
 		//echo ($query."       ");
 		
 		//execute query and store results
 		$stmt = $db_server->query($query);
 		$queryResult = $stmt->fetch_array(MYSQLI_ASSOC);
 		$graduateNum = ($queryResult['count'] > 0 ? $queryResult['count'] : 0);
 		$total += $graduateNum;
 		
 		//obtain Geosciences
 		$query = "select academic_info.school_name as school, count(*) as count from semester, academic_info";
 		//make join
 		$query = $query.makeJoins($gender, $region, $country, $program, '0');
 		$query = $query." where";
 		//input where conditions
 		$query = $query.makeQuery($level, $gender, $region, $country, $program, 'All');
 		//finish query
 		$pos = strpos($query, '=');
 		if ($pos === false){
 			$query = $query." year=".$year." and semester.major_code = academic_info.major_code and academic_info.school_name = 'jackson school of geosciences'
 			group by academic_info.school_name;";
 		} else {
 			$query = $query." and year=".$year." and semester.major_code = academic_info.major_code and academic_info.school_name = 'jackson school of geosciences'
 			group by academic_info.school_name;"; 
 		}
 		
 		//echo ($query."       ");
 		
 		//execute query and store results
 		$stmt = $db_server->query($query);
 		$queryResult = $stmt->fetch_array(MYSQLI_ASSOC);
 		$geosciencesNum = ($queryResult['count'] > 0 ? $queryResult['count'] : 0);
 		$total += $geosciencesNum;
 		
 		//obtain Public Affairs
 		$query = "select academic_info.school_name as school, count(*) as count from semester, academic_info";
 		//make join
 		$query = $query.makeJoins($gender, $region, $country, $program, '0');
 		$query = $query." where";
 		//input where conditions
 		$query = $query.makeQuery($level, $gender, $region, $country, $program, 'All');
 		//finish query
 		$pos = strpos($query, '=');
 		if ($pos === false){
 			$query = $query." year=".$year." and semester.major_code = academic_info.major_code and academic_info.school_name = 'lyndon b. johnson school of public affairs'
 			group by academic_info.school_name;";
 		} else {
 			$query = $query." and year=".$year." and semester.major_code = academic_info.major_code and academic_info.school_name = 'lyndon b. johnson school of public affairs'
 			group by academic_info.school_name;"; 
 		}
 		
 		//echo ($query."       ");
 		
 		//execute query and store results
 		$stmt = $db_server->query($query);
 		$queryResult = $stmt->fetch_array(MYSQLI_ASSOC);
 		$publicAffarisNum = ($queryResult['count'] > 0 ? $queryResult['count'] : 0);
 		$total += $publicAffarisNum;
 		
 		//obtain Business
 		$query = "select academic_info.school_name as school, count(*) as count from semester, academic_info";
 		//make join
 		$query = $query.makeJoins($gender, $region, $country, $program, '0');
 		$query = $query." where";
 		//input where conditions
 		$query = $query.makeQuery($level, $gender, $region, $country, $program, 'All');
 		//finish query
 		$pos = strpos($query, '=');
 		if ($pos === false){
 			$query = $query." year=".$year." and semester.major_code = academic_info.major_code and academic_info.school_name = 'mccombs school of business'
 			group by academic_info.school_name;";
 		} else {
 			$query = $query." and year=".$year." and semester.major_code = academic_info.major_code and academic_info.school_name = 'mccombs school of business'
 			group by academic_info.school_name;"; 
 		}
 		
 		//echo ($query."       ");
 		
 		//execute query and store results
 		$stmt = $db_server->query($query);
 		$queryResult = $stmt->fetch_array(MYSQLI_ASSOC);
 		$businessNum = ($queryResult['count'] > 0 ? $queryResult['count'] : 0);
 		$total += $businessNum;
 		
 		//obtain Architecture
 		$query = "select academic_info.school_name as school, count(*) as count from semester, academic_info";
 		//make join
 		$query = $query.makeJoins($gender, $region, $country, $program, '0');
 		$query = $query." where";
 		//input where conditions
 		$query = $query.makeQuery($level, $gender, $region, $country, $program, 'All');
 		//finish query
 		$pos = strpos($query, '=');
 		if ($pos === false){
 			$query = $query." year=".$year." and semester.major_code = academic_info.major_code and academic_info.school_name = 'school of architecture'
 			group by academic_info.school_name;";
 		} else {
 			$query = $query." and year=".$year." and semester.major_code = academic_info.major_code and academic_info.school_name = 'school of architecture'
 			group by academic_info.school_name;"; 
 		}
 		
 		//echo ($query."       ");
 		
 		//execute query and store results
 		$stmt = $db_server->query($query);
 		$queryResult = $stmt->fetch_array(MYSQLI_ASSOC);
 		$architectureNum = ($queryResult['count'] > 0 ? $queryResult['count'] : 0);
 		$total += $architectureNum;
 		
 		//obtain Information
 		$query = "select academic_info.school_name as school, count(*) as count from semester, academic_info";
 		//make join
 		$query = $query.makeJoins($gender, $region, $country, $program, '0');
 		$query = $query." where";
 		//input where conditions
 		$query = $query.makeQuery($level, $gender, $region, $country, $program, 'All');
 		//finish query
 		$pos = strpos($query, '=');
 		if ($pos === false){
 			$query = $query." year=".$year." and semester.major_code = academic_info.major_code and academic_info.school_name = 'school of information'
 			group by academic_info.school_name;";
 		} else {
 			$query = $query." and year=".$year." and semester.major_code = academic_info.major_code and academic_info.school_name = 'school of information'
 			group by academic_info.school_name;"; 
 		}
 		
 		//echo ($query."       ");
 		
 		//execute query and store results
 		$stmt = $db_server->query($query);
 		$queryResult = $stmt->fetch_array(MYSQLI_ASSOC);
 		$informationNum = ($queryResult['count'] > 0 ? $queryResult['count'] : 0);
 		$total += $informationNum;
 		
 		
 		//obtain Law
 		$query = "select academic_info.school_name as school, count(*) as count from semester, academic_info";
 		//make join
 		$query = $query.makeJoins($gender, $region, $country, $program, '0');
 		$query = $query." where";
 		//input where conditions
 		$query = $query.makeQuery($level, $gender, $region, $country, $program, 'All');
 		//finish query
 		$pos = strpos($query, '=');
 		if ($pos === false){
 			$query = $query." year=".$year." and semester.major_code = academic_info.major_code and academic_info.school_name = 'school of law'
 			group by academic_info.school_name;";
 		} else {
 			$query = $query." and year=".$year." and semester.major_code = academic_info.major_code and academic_info.school_name = 'school of law'
 			group by academic_info.school_name;"; 
 		}
 		
 		//echo ($query."       ");
 		
 		//execute query and store results
 		$stmt = $db_server->query($query);
 		$queryResult = $stmt->fetch_array(MYSQLI_ASSOC);
 		$lawNum = ($queryResult['count'] > 0 ? $queryResult['count'] : 0);
 		$total += $lawNum;
 		
 		//obtain Nursing
 		$query = "select academic_info.school_name as school, count(*) as count from semester, academic_info";
 		//make join
 		$query = $query.makeJoins($gender, $region, $country, $program, '0');
 		$query = $query." where";
 		//input where conditions
 		$query = $query.makeQuery($level, $gender, $region, $country, $program, 'All');
 		//finish query
 		$pos = strpos($query, '=');
 		if ($pos === false){
 			$query = $query." year=".$year." and semester.major_code = academic_info.major_code and academic_info.school_name = 'school of nursing'
 			group by academic_info.school_name;";
 		} else {
 			$query = $query." and year=".$year." and semester.major_code = academic_info.major_code and academic_info.school_name = 'school of nursing'
 			group by academic_info.school_name;"; 
 		}
 		
 		//echo ($query."       ");
 		
 		//execute query and store results
 		$stmt = $db_server->query($query);
 		$queryResult = $stmt->fetch_array(MYSQLI_ASSOC);
 		$nursingNum = ($queryResult['count'] > 0 ? $queryResult['count'] : 0);
 		$total += $nursingNum;
 	
 		//obtain Social Work
 		$query = "select academic_info.school_name as school, count(*) as count from semester, academic_info";
 		//make join
 		$query = $query.makeJoins($gender, $region, $country, $program, '0');
 		$query = $query." where";
 		//input where conditions
 		$query = $query.makeQuery($level, $gender, $region, $country, $program, 'All');
 		//finish query
 		$pos = strpos($query, '=');
 		if ($pos === false){
 			$query = $query." year=".$year." and semester.major_code = academic_info.major_code and academic_info.school_name = 'school of social work'
 			group by academic_info.school_name;";
 		} else {
 			$query = $query." and year=".$year." and semester.major_code = academic_info.major_code and academic_info.school_name = 'school of social work'
 			group by academic_info.school_name;"; 
 		}
 		
 		echo ($query."       ");
 		
 		//execute query and store results
 		$stmt = $db_server->query($query);
 		$queryResult = $stmt->fetch_array(MYSQLI_ASSOC);
 		$socialWorkNum = ($queryResult['count'] > 0 ? $queryResult['count'] : 0);
 		$total += $socialWorkNum;
 			
 		//obtain Undergraduate Studies
 		$query = "select academic_info.school_name as school, count(*) as count from semester, academic_info";
 		//make join
 		$query = $query.makeJoins($gender, $region, $country, $program, '0');
 		$query = $query." where";
 		//input where conditions
 		$query = $query.makeQuery($level, $gender, $region, $country, $program, 'All');
 		//finish query
 		$pos = strpos($query, '=');
 		if ($pos === false){
 			$query = $query." year=".$year." and semester.major_code = academic_info.major_code and academic_info.school_name = 'school of undergraduate studies'
 			group by academic_info.school_name;";
 		} else {
 			$query = $query." and year=".$year." and semester.major_code = academic_info.major_code and academic_info.school_name = 'school of undergraduate studies'
 			group by academic_info.school_name;"; 
 		}
 		
 		//echo ($query."       ");
 		
 		//execute query and store results
 		$stmt = $db_server->query($query);
 		$queryResult = $stmt->fetch_array(MYSQLI_ASSOC);
 		$undergraduateNum = ($queryResult['count'] > 0 ? $queryResult['count'] : 0);
 		$total += $undergraduateNum;
		
		//create chart
		$response = $response."Highcharts.setOptions({ colors:".$colors." });"
		."$('#graphContainer').highcharts({ chart: { type: 'column' }, credits: { position: { align: 'right', verticalAlign: 'bottom', y: .45},"
		."text: 'Total Students:".$total."', href: '#', style: { cursor: 'cursor', color: '#3E576F', fontSize: '15px'} }," 
		."title: { text: '".$reportName." (".$year.")' },"
		."xAxis: { categories: ['Schools and Colleges'] }, yAxis: { title: { text: 'Number of Students'} },"
		."series: [{name: 'Architecture', data: [".$architectureNum."]}, { name: 'Business', data: [".$businessNum."] },"
		." { name: 'Communication', data: [".$communicationNum."] }, { name: 'Education', data: [".$educationNum."] }, { name: 'Engineering', data: [".$engineeringNum."] },"
		." { name: 'Fine Arts', data: [".$fineArtsNum."] }, { name: 'Geosciences', data: [".$geosciencesNum."] }, { name: 'Graduate', data: [".$graduateNum."] }, { name: 'Information', data: [".$informationNum."] },"
		."{ name: 'Law', data: [".$lawNum."] }, { name: 'Liberal Arts', data: [".$liberalArtsNum."] }, { name: 'Medical', data: [".$medSchoolNum."] }, { name: 'Natural Sciences', data: [".$naturalSciencesNum."] }, 
		{ name: 'Nursing', data: [".$nursingNum."] }, { name: 'Pharmacy', data: [".$pharmacyNum."] }, { name: 'Public Affairs', data: [".$publicAffarisNum."] },
		{ name: 'Social Work', data: [".$socialWorkNum."] }, { name: 'Undergraduate', data: [".$undergraduateNum."] }  ]});";
  		
 		
 	}
	
	//student distribution by region
 	else if ($report == '5'){
		$total = 0;
		
		//obtain Asia
		$query = "select country.Region_of_citizenship as region, count(*) as count from semester, student, country";
		//make join
 		$query = $query.makeJoins('0', '0', '0', $program, $college);
 		$query = $query." where";
 		//input where conditions
 		$query = $query.makeQuery($level, $gender, 'All', 'All', $program, $college);
 		//finish query
 		$pos = strpos($query, '=');
		if ($pos === false){
 			$query = $query." year=".$year." and semester.ut_eid = student.ut_eid and country.country_code = student.country_code and country.Region_of_citizenship = 'asia' and semester = 'Fall' group by country.Region_of_citizenship;";
 		} else {
 			$query = $query." and year=".$year." and semester.ut_eid = student.ut_eid and country.country_code = student.country_code and country.Region_of_citizenship = 'asia' and semester = 'Fall' group by country.Region_of_citizenship;"; 
 		}
		
		//echo ($query."       ");
 		
 		//execute query and store results
 		$stmt = $db_server->query($query);
 		$queryResult = $stmt->fetch_array(MYSQLI_ASSOC);
 		$asiaNum = ($queryResult['count'] > 0 ? $queryResult['count'] : 0);
 		$total += $asiaNum;
				
		//obtain Africa
		$query = "select country.Region_of_citizenship as region, count(*) as count from semester, student, country";
		//make join
 		$query = $query.makeJoins('0', '0', '0', $program, $college);
 		$query = $query." where";
 		//input where conditions
 		$query = $query.makeQuery($level, $gender, 'All', 'All', $program, $college);
 		//finish query
 		$pos = strpos($query, '=');
		if ($pos === false){
 			$query = $query." year=".$year." and semester.ut_eid = student.ut_eid and country.country_code = student.country_code and country.Region_of_citizenship = 'africa' and semester = 'Fall' group by country.Region_of_citizenship;";
 		} else {
 			$query = $query." and year=".$year." and semester.ut_eid = student.ut_eid and country.country_code = student.country_code and country.Region_of_citizenship = 'africa' and semester = 'Fall' group by country.Region_of_citizenship;"; 
 		}
		
		//echo ($query."       ");
 		
 		//execute query and store results
 		$stmt = $db_server->query($query);
 		$queryResult = $stmt->fetch_array(MYSQLI_ASSOC);
 		$africaNum = ($queryResult['count'] > 0 ? $queryResult['count'] : 0);
 		$total += $africaNum;
		
		//obtain Latin America and Caribbean
		$query = "select country.Region_of_citizenship as region, count(*) as count from semester, student, country";
		//make join
 		$query = $query.makeJoins('0', '0', '0', $program, $college);
 		$query = $query." where";
 		//input where conditions
 		$query = $query.makeQuery($level, $gender, 'All', 'All', $program, $college);
 		//finish query
 		$pos = strpos($query, '=');
		if ($pos === false){
 			$query = $query." year=".$year." and semester.ut_eid = student.ut_eid and country.country_code = student.country_code and country.Region_of_citizenship = 'latin america and caribbean' and semester = 'Fall' group by country.Region_of_citizenship;";
 		} else {
 			$query = $query." and year=".$year." and semester.ut_eid = student.ut_eid and country.country_code = student.country_code and country.Region_of_citizenship = 'latin america and caribbean' and semester = 'Fall' group by country.Region_of_citizenship;"; 
 		}
		
		//echo ($query."       ");
 		
 		//execute query and store results
 		$stmt = $db_server->query($query);
 		$queryResult = $stmt->fetch_array(MYSQLI_ASSOC);
 		$latinNum = ($queryResult['count'] > 0 ? $queryResult['count'] : 0);
 		$total += $latinNum;
		
		//obtain Middle East
		$query = "select country.Region_of_citizenship as region, count(*) as count from semester, student, country";
		//make join
 		$query = $query.makeJoins('0', '0', '0', $program, $college);
 		$query = $query." where";
 		//input where conditions
 		$query = $query.makeQuery($level, $gender, 'All', 'All', $program, $college);
 		//finish query
 		$pos = strpos($query, '=');
		if ($pos === false){
 			$query = $query." year=".$year." and semester.ut_eid = student.ut_eid and country.country_code = student.country_code and country.Region_of_citizenship = 'middle east' and semester = 'Fall' group by country.Region_of_citizenship;";
 		} else {
 			$query = $query." and year=".$year." and semester.ut_eid = student.ut_eid and country.country_code = student.country_code and country.Region_of_citizenship = 'middle east' and semester = 'Fall' group by country.Region_of_citizenship;"; 
 		}
		
		//echo ($query."       ");
 		
 		//execute query and store results
 		$stmt = $db_server->query($query);
 		$queryResult = $stmt->fetch_array(MYSQLI_ASSOC);
 		$middleEastNum = ($queryResult['count'] > 0 ? $queryResult['count'] : 0);
 		$total += $middleEastNum;
		
		//obtain North America
		$query = "select country.Region_of_citizenship as region, count(*) as count from semester, student, country";
		//make join
 		$query = $query.makeJoins('0', '0', '0', $program, $college);
 		$query = $query." where";
 		//input where conditions
 		$query = $query.makeQuery($level, $gender, 'All', 'All', $program, $college);
 		//finish query
 		$pos = strpos($query, '=');
		if ($pos === false){
 			$query = $query." year=".$year." and semester.ut_eid = student.ut_eid and country.country_code = student.country_code and country.Region_of_citizenship = 'north america' and semester = 'Fall' group by country.Region_of_citizenship;";
 		} else {
 			$query = $query." and year=".$year." and semester.ut_eid = student.ut_eid and country.country_code = student.country_code and country.Region_of_citizenship = 'north america' and semester = 'Fall' group by country.Region_of_citizenship;"; 
 		}
		
		//echo ($query."       ");
 		
 		//execute query and store results
 		$stmt = $db_server->query($query);
 		$queryResult = $stmt->fetch_array(MYSQLI_ASSOC);
 		$northAmericaNum = ($queryResult['count'] > 0 ? $queryResult['count'] : 0);
 		$total += $northAmericaNum;
		
		//obtian Oceana
		$query = "select country.Region_of_citizenship as region, count(*) as count from semester, student, country";
		//make join
 		$query = $query.makeJoins('0', '0', '0', $program, $college);
 		$query = $query." where";
 		//input where conditions
 		$query = $query.makeQuery($level, $gender, 'All', 'All', $program, $college);
 		//finish query
 		$pos = strpos($query, '=');
		if ($pos === false){
 			$query = $query." year=".$year." and semester.ut_eid = student.ut_eid and country.country_code = student.country_code and country.Region_of_citizenship = 'oceana' and semester = 'Fall' group by country.Region_of_citizenship;";
 		} else {
 			$query = $query." and year=".$year." and semester.ut_eid = student.ut_eid and country.country_code = student.country_code and country.Region_of_citizenship = 'oceana' and semester = 'Fall' group by country.Region_of_citizenship;"; 
 		}
		
		//echo ($query."       ");
 		
 		//execute query and store results
 		$stmt = $db_server->query($query);
 		$queryResult = $stmt->fetch_array(MYSQLI_ASSOC);
 		$oceanaNum = ($queryResult['count'] > 0 ? $queryResult['count'] : 0);
 		$total += $oceanaNum;
		
		//obtian Europe
		$query = "select country.Region_of_citizenship as region, count(*) as count from semester, student, country";
		//make join
 		$query = $query.makeJoins('0', '0', '0', $program, $college);
 		$query = $query." where";
 		//input where conditions
 		$query = $query.makeQuery($level, $gender, 'All', 'All', $program, $college);
 		//finish query
 		$pos = strpos($query, '=');
		if ($pos === false){
 			$query = $query." year=".$year." and semester.ut_eid = student.ut_eid and country.country_code = student.country_code and country.Region_of_citizenship = 'europe' and semester = 'Fall' group by country.Region_of_citizenship;";
 		} else {
 			$query = $query." and year=".$year." and semester.ut_eid = student.ut_eid and country.country_code = student.country_code and country.Region_of_citizenship = 'europe' and semester = 'Fall' group by country.Region_of_citizenship;"; 
 		}
		
		//echo ($query."       ");
 		
 		//execute query and store results
 		$stmt = $db_server->query($query);
 		$queryResult = $stmt->fetch_array(MYSQLI_ASSOC);
 		$europeNum = ($queryResult['count'] > 0 ? $queryResult['count'] : 0);
 		$total += $europeNum;
		
		//create chart
		$response = $response."Highcharts.setOptions({ colors:".$colors." });"
		."$('#graphContainer').highcharts({
        chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false
            },
            title: {
                text: '".$reportName." (".$year.")'
            },
            tooltip: {
        	    pointFormat: '{series.name}: <b>{point.percentage:.0f}%</b> <br /> Number of Students: <b>{point.y}</b>',
            	valueDecimals: 0
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true,
                        color: '#000000',
                        connectorColor: '#000000',
                        formatter: function() {
                            return '<b>'+ this.point.name +'</b>: '+ Math.round(this.percentage) +' %';
                        }
                    }
                }
            },
            series: [{
                type: 'pie',
                name: 'Percent of Students',
                data: [
                    {
                        name: 'Asia',
                        y: ".$asiaNum.",
                        sliced: true,
                        selected: true
                    },
					['Africa', ".$africaNum."],
					['Latin America and Caribbean', ".$latinNum."],
					['Middle East', ".$middleEastNum."],
					['North America', ".$northAmericaNum."],
					['Oceana', ".$oceanaNum."],
					['Europe', ".$europeNum."]
                ]
            }], credits: { position: { align: 'right', verticalAlign: 'bottom'},"
		."text: 'Total Students:".$total."', href: '#', style: { cursor: 'cursor', color: '#3E576F', fontSize: '15px'} }
        });";
 	}
	
 	//distribution by top 10 countries
 	else if ($report == '6') {
 		//obtain top 10 countries
 		$query = "select country.country_name as country, count(*) as count from semester,student,country";
 		//make join
 		$query = $query.makeJoins('0', '0', '0', $program, $college);
 		$query = $query." where";
 		//input where conditions
 		$query = $query.makeQuery($level, $gender, $region, 'All', $program, $college);
 		//finish query
 		$pos = strpos($query, '=');
 		if ($pos === false){
 			$query = $query." year=".$year."  and semester.ut_eid = student.ut_eid and student.country_code = country.country_code 
 			and semester='Fall' group by country.country_name order by count(*) DESC;";
 		} else {
 			$query = $query." and year=".$year."  and semester.ut_eid = student.ut_eid and student.country_code = country.country_code 
 			and semester='Fall' group by country.country_name order by count(*) DESC;";
 		}
 		
 		echo ($query."       ");
 		
 		$countArray = new SplFixedArray(10);
 		$nameArray = new SplFixedArray(10);
 		$total = 0;
 		
 		//execute query and store results
 		$stmt = $db_server->query($query);
 		for ($i = 0; $i < 10; $i++) {
 			$queryResult = $stmt->fetch_array(MYSQLI_ASSOC);
 			$countArray[$i] = ($queryResult['count'] > 0 ? $queryResult['count'] : 0);
 			$total += $countArray[$i];
 			$nameArray[$i] = $queryResult['country'];
 		}
 		
 		//create chart
		$response = $response."Highcharts.setOptions({ colors:".$colors." });"
		."$('#graphContainer').highcharts({ chart: { type: 'column' }, credits: { position: { align: 'right', verticalAlign: 'bottom', y: .45},"
		."text: 'Total Students:".$total."', href: '#', style: { cursor: 'cursor', color: '#3E576F', fontSize: '15px'} }," 
		."title: { text: '".$reportName." (".$year.")' },"
		."xAxis: { categories: ['Countries'] }, yAxis: { title: { text: 'Number of Students'} },"
		."series: [{name: '".$nameArray[0]."', data: [".$countArray[0]."]}, { name: '".$nameArray[1]."', data: [".$countArray[1]."] },"
		." { name: '".$nameArray[2]."', data: [".$countArray[2]."] }, { name: '".$nameArray[3]."', data: [".$countArray[3]."] },"
		." { name: '".$nameArray[4]."', data: [".$countArray[4]."] }, { name: '".$nameArray[5]."', data: [".$countArray[5]."] }, 
		{ name: '".$nameArray[6]."', data: [".$countArray[6]."] }, { name: '".$nameArray[7]."', data: [".$countArray[7]."] },
		{ name: '".$nameArray[8]."', data: [".$countArray[8]."] }, { name: '".$nameArray[9]."', data: [".$countArray[9]."] } ]});";
 	}	
	//enrollement trends
 	else if ($report == '7'){
 		//obtain trends for last 5 years
 		$query = "select semester.year as year, count(*) as count from semester";
 		//make join
 		$query = $query.makeJoins($gender, $region, $country, $program, $college);
 		$query = $query." where";
 		//input where conditions
 		$query = $query.makeQuery($level, $gender, $region, $country, $program, $college);
 		//finish query
 		$pos = strpos($query, '=');
 		if ($pos === false){
 			$query = $query." semester='Fall' group by semester.year DESC;";
 		} else {
 			$query = $query." and semester='Fall' group by semester.year DESC;";
 		}
 		
 		echo ($query."       ");
 		
 		$countArray = array();
 		$nameArray = array();
 		$total = 0;
 		
 		//execute query and store results
 		$stmt = $db_server->query($query);
 		$j = 0;
 		while($queryResult = $stmt->fetch_array(MYSQLI_ASSOC)) {
 			$countArray[$j] = ($queryResult['count'] > 0 ? $queryResult['count'] : 0);
 			$total += $countArray[$j];
 			$nameArray[$j] = $queryResult['year'];
 			$j = $j + 1;
 		}
 		
 		//create chart
	 	$response = $response."Highcharts.setOptions({ colors:".$colors." });"
		."$('#graphContainer').highcharts({
            chart: {
                type: 'line',
                marginRight: 130,
                marginBottom: 25
            },
            title: {
                text: '".$reportName."',
            },
            xAxis: {
                categories: [";
                for ($i = 0; $i < count($nameArray); $i++){
                	$response = $response."'".$nameArray[$i]."', ";
                }
                $response = substr($response, 0, -2);
                $response = $response."]
            },
            yAxis: {
                title: {
                    text: 'Number of Students'
                },
                plotLines: [{
                    value: 0,
                    width: 1,
                    color: '#808080'
                }]
            },
            legend: {
                layout: 'vertical',
                align: 'right',
                verticalAlign: 'top',
                x: -10,
                y: 100,
                borderWidth: 0
            },
            series: [{
                name: 'Enrollment',
                data: [";
                for ($i = 0; $i < count($countArray); $i++){
                	$response = $response.$countArray[$i].", ";
                }
                $response = substr($response, 0, -2);
                $response = $response."]
            }]
        });";
 		
 	}
 	
 	$response = $response.'} </script>';
	
	echo ($response);
?>