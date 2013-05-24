<?php
	
	//establish connection to database
	require_once 'database/DBInfo.php';
	$db_server = new mysqli($db_hostname, $db_username, $db_password, $db_database);
	
	if ($db_server->connect_errno) {
		// connect_error returns the a string of the error from the latest sql command
		print ("<h1> There was an error:</h1> <p> " . $db_server->connect_error . "</p>");
	}
	
	$latestYear = 0;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-gb" lang="en-gb" >
	
	<head>
	
		<title> ISSS Statistical Reports </title>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
  		<meta name="robots" content="index, follow" />
  		<meta name="keywords" content="ut, utexas, university of texas, international office, austin, world, global, study abroad, isss, esl, passport, safety" />
  		<meta name="title" content="International Student & Scholar Services" />
  	
  		<!-- scripts -->
  		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
		<script src="js/highcharts.js"></script>
		<script src="js/exporting.js"></script>
		<script src="js/excanvas.compiled.js"></script>
	
		
  		<script language="javascript">
  			var shown = false;
  			
  			//shows filters in UI when a report is selected
  			function showFilters (reportVal){
  				//show or hide filters based on report request state
  				if ($('#reportOptions').val() === ""){
  					$('#classificationFilters').fadeOut('fast');
  					$('#graphContainer').fadeOut('fast');
  					$('#reports').fadeOut('fast');
  					$('#worldMap').fadeIn('slow');
  					$('#reports').fadeIn('slow');
  					shown = false;
  				}
  				else if (!shown) {
  					$("#year").val(<?php echo("'".$latestYear."'"); ?>);
					$("#academicLevel").val('All');
					$("#gender").val('All');
					$("#region").val('All');
					$("#country").val('All');
					$("#program").val('All');
					$("#college").val('All');
					$('#worldMap').fadeOut('fast');
					$('#classificationFilters').fadeIn('slow');
  					shown = true;
  				}
  				
  				//disable filters that are not applicable to current query
				if (reportVal == '1'){
					$("#academicLevel").prop("disabled", true);
				} else if (reportVal == '3'){
					$("#gender").prop("disabled", true);
				} else if (reportVal == '4'){
					$("#college").prop("disabled", true);
				} else if (reportVal == '5'){
					$("#region").prop("disabled", true);
					$("#country").prop("disabled", true);
				} else if (reportVal == '6'){
					$("#country").prop("disabled", true);
				} else if (reportVal == '7'){
					$("#year").prop("disabled", true);
				}
  			}
  			
  			function revertFilters() {
  				$("#year").prop("disabled", false);
  				$("#academicLevel").prop("disabled", false);
  				$("#gender").prop("disabled", false);
  				$("#college").prop("disabled", false);
  				$("#region").prop("disabled", false);
				$("#country").prop("disabled", false);
  				$("#academicLevel").val('All');
  				$("#year").val(<?php echo("'".$latestYear."'"); ?>);
  				$("#gender").val('All');
				$("#region").val('All');
				$("#country").val('All');
				$("#program").val('All');
				$("#program").val('All');
				$("#college").val('All');
				$('#classificationFilters').fadeOut('fast');
				$('#classificationFilters').fadeIn('slow');
  			}
  			
  			function showReports(){
  				$('#reports').fadeIn('slow');
  				$('#worldMap').fadeIn('slow');
  			}
  			
  			
  			function runReport(){
  			
  				//$('#processing').fadeIn('slow');
  				var reportName = $("#reportOptions option:selected").text();
  				var reportVal = $("#reportOptions option:selected").val();
  				var yearVal = $("#year option:selected").val();
  				var academicLevelVal = $("#academicLevel option:selected").val();
  				var genderVal = $("#gender option:selected").val();
  				var regionVal = $("#region option:selected").val();
  				var countryVal = $("#country option:selected").text();
  				var programVal = $("#program option:selected").val();
  				var collegeVal = $("#college option:selected").text();
  				
  				if (reportVal != "") {
  					var executed = false;
  					var update_request = new XMLHttpRequest();
  					update_request.open('POST', './createChart.php');
  					var params = "reportName=" + reportName + "&reportVal=" + reportVal + "&year=" + yearVal + "&academicLevel=" + academicLevelVal + ""
  					+ "&gender=" + genderVal + "&region=" + regionVal + "&country=" + countryVal 
  					+ "&program=" + programVal + "&college=" + collegeVal + "";
  					update_request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
					update_request.setRequestHeader("Content-length", params.length);
					update_request.setRequestHeader("Connection", "close");
					update_request.send(params);
					
					update_request.onreadystatechange = 
					function() {
      			    	if (!executed) {
      			  			//alert ("ready state is " + update_request.readyState);
							if (update_request.readyState === 4) {
								//alert ("status is " + update_request.status);
								//alert ("response is " + update_request.responseText);
	  							if (update_request.status === 200) {
									//$('#processing').fadeOut('slow');
									var response = update_request.responseText;
									var graphContainer = document.getElementById('graphContainer');
									graphContainer.style.height = '400px';
									graphContainer.style.width = '100%';
									$('#graphContainer').fadeIn('fast');
									$('#graphContainer').empty();
									$('#graphContainer').append(response);
									graph();
									$('#graphContainer').fadeIn('slow');
									executed = true;
								} else { 
									//$('#processing').fadeOut('slow');
									$('#graphContainer').empty();
									var error = '<p style="color: #CC5500; text-align: center;"> There was an error constructing your graph <p>';
									$('#graphContainer').append(error);
									$('#graphContainer').fadeIn('slow');
									executed = true;
	  							}
      						}
      					}
      			  	}
  				}
  				showFilters(reportVal);
  				return false;	
  			}
  		</script>
  		
  		<!-- stylesheets -->
  		<link rel="stylesheet" href="styles/default.css" type="text/css" />
  		<link rel="stylesheet" href="styles/application.css" type="text/css" />
  		<!-- <link rel="stylesheet" href="http://world.utexas.edu/modules/mod_superfishmenu/tmpl/css/superfish.css" type="text/css" /> -->

  	</head>
  	
  	<body onload="showReports()">
  		<div id="container">
  			<div id="mainContent">
  				<header>
  					<!-- header images -->
  					<div class="moduletable">
  						<a href="http://www.utexas.edu"><img src="images/ut.png" alt="the univeristy of texas at austin" border="0" /></a>
  						<br /> <br />
  						<a href="http://world.utexas.edu/isss"><img src="images/isss_logo.gif" border="0" alt="ut isss" width="408" height="49" style="vertical-align: middle; border: 0px;" /></a>
  						<a href="http://world.utexas.edu/home"><img src="images/international-office.gif" border="0" alt="io" width="288" height="58" style="vertical-align: middle; margin-left: 20px; margin-right: 20px;" /></a>
  						<br /> <br />
  						<fieldset id="banner">
  							<a id="bannerTitle" href="isss_statistics.php"> ISSS STATISTICAL REPORTS </a>
  						</fieldset>
  					</div> <!-- end moduletable -->
  				</header>
  				<br /> <br />
  				
  				<div id="graphs">
  					<br />
  					<img src="images/orange-world-map.png" id="worldMap" style="vertical-align: middle; border: 0px; display: none; margin-left: 160px;" />
  					<div id="processing" style="display: none;">
  						<img src="images/processing.gif" id="processingImg" width="75" height="75" style="vertical-align: middle; border: 0px; margin-left: 420px;" />
  					</div> <!-- end processing -->
  					<div id="graphContainer">
  			
  					</div> <!-- end graphContainer -->
  					<br />
  				</div> <!-- end graphs -->
  				
  		
				<!-- filters -->
  				<div id="classificationFilters" style="display: none;">
  					<br /> <br />
  					<fieldset>
  					<legend> <span class="hoverable">Please Select Filters</span> </legend>
  					<span id="yearText" class="hoverable">Year</span> 
  						<select id="year">
  							<?php
  								$query = "select distinct year from semester order by year DESC;";
  								$stmt = $db_server->query($query);
  								$options = "";
								while ($queryResult = $stmt->fetch_array(MYSQLI_ASSOC)) {
									if ($queryResult['year'] > $latestYear){
										$latestYear = $queryResult['year'];
									}
									$options = $options.'<option value="'.$queryResult['year'].'">'.$queryResult['year'].'</option>';
								}
								echo('<html>'.$options.'</html>');
  							?>
  						</select>
  					<span id="populationText" class="hoverable">Population</span>
						<select id="program">
							<option value="All">Students & Scholars</option>
							<option value="1">Students</option>
							<option value="2">Scholars</option>
							<option value="3">Exchange Students Only</option>
							<option value="4">Sponsored Students Only</option>
						</select>
  					<span id="academicLevelText" class="hoverable">Academic Level</span>
  						<select id="academicLevel">
  							<option value="All">All</option>
  							<option value="UG">Undergraduate</option>
  							<option value="G">Graduate</option>
  						</select>
  					<span id="collegeText" class="hoverable">College</span>
  						<select id="college">
							<option value="All">All</option>
							<option>Cockrell School of Engineering</option>
							<option>College of Communication</option>
							<option>College of Education</option>
							<option>College of Fine Arts</option>
							<option>College of Liberal Arts</option>
							<option>College of Natural Sciences</option>
							<option>College of Pharmacy</option>
							<option>Dell Medical School</option>
							<option>Graduate School</option>
							<option>Jackson School of Geosciences</option>
							<option>Lyndon B. Johnson School of Public Affairs</option>
							<option>McCombs School of Business</option>
							<option>School of Architecture</option>
							<option>School of Information</option>
							<option>School of Law</option>
							<option>School of Nursing</option>
							<option>School of Social Work</option>
							<option>School of Undergraduate Studies</option>
						</select>
  						<br /> <br />
  					<span id="genderText" class="hoverable">Gender</span>
  						<select id="gender">
  							<option value="All">All</option>
  							<option value="m">Male</option>
  							<option value="f">Female</option>
  						</select>
  					<span id="RegionText" class="hoverable">Region</span>
  						<select id="region">
  							<option value="All">All</option>
  							<option value="Asia">Asia</option>
  							<option value="Africa">Africa</option>
  							<option value="Latin America and Caribbean">Latin America and Caribbean</option>
  							<option value="Middle East">Middle East</option>
  							<option value="North America">North America</option>
  							<option value="Oceana">Oceana</option>
  							<option value="Europe">Europe</option>
  						</select>
  					<span id="countryText" class="hoverable">Country</span>
  						<select id="country">
							<option value="All">All</option>					
							<option value="AF">Afghanistan</option>
							<option value="AX">Åland Islands</option>
							<option value="AL">Albania</option>
							<option value="DZ">Algeria</option>
							<option value="AS">American Samoa</option>
							<option value="AD">Andorra</option>
							<option value="AO">Angola</option>
							<option value="AI">Anguilla</option>
							<option value="AQ">Antarctica</option>
							<option value="AG">Antigua and Barbuda</option>
							<option value="AR">Argentina</option>
							<option value="AM">Armenia</option>
							<option value="AW">Aruba</option>
							<option value="AU">Australia</option>
							<option value="AT">Austria</option>
							<option value="AZ">Azerbaijan</option>
							<option value="BS">Bahamas</option>
							<option value="BH">Bahrain</option>
							<option value="BD">Bangladesh</option>
							<option value="BB">Barbados</option>
							<option value="BY">Belarus</option>
							<option value="BE">Belgium</option>
							<option value="BZ">Belize</option>
							<option value="BJ">Benin</option>
							<option value="BM">Bermuda</option>
							<option value="BT">Bhutan</option>
							<option value="BO">Bolivia, Plurinational State of</option>
							<option value="BQ">Bonaire, Sint Eustatius and Saba</option>
							<option value="BA">Bosnia and Herzegovina</option>
							<option value="BW">Botswana</option>
							<option value="BV">Bouvet Island</option>
							<option value="BR">Brazil</option>
							<option value="IO">British Indian Ocean Territory</option>
							<option value="BN">Brunei Darussalam</option>
							<option value="BG">Bulgaria</option>
							<option value="BF">Burkina Faso</option>
							<option value="BI">Burundi</option>
							<option value="KH">Cambodia</option>
							<option value="CM">Cameroon</option>
							<option value="CA">Canada</option>
							<option value="CV">Cape Verde</option>
							<option value="KY">Cayman Islands</option>
							<option value="CF">Central African Republic</option>
							<option value="TD">Chad</option>
							<option value="CL">Chile</option>
							<option value="CN">China</option>
							<option value="CX">Christmas Island</option>
							<option value="CC">Cocos (Keeling) Islands</option>
							<option value="CO">Colombia</option>
							<option value="KM">Comoros</option>
							<option value="CG">Congo</option>
							<option value="CD">Congo, the Democratic Republic of the</option>
							<option value="CK">Cook Islands</option>
							<option value="CR">Costa Rica</option>
							<option value="CI">Côte d'Ivoire</option>
							<option value="HR">Croatia</option>
							<option value="CU">Cuba</option>
							<option value="CW">Curaçao</option>
							<option value="CY">Cyprus</option>
							<option value="CZ">Czech Republic</option>
							<option value="DK">Denmark</option>
							<option value="DJ">Djibouti</option>
							<option value="DM">Dominica</option>
							<option value="DO">Dominican Republic</option>
							<option value="EC">Ecuador</option>
							<option value="EG">Egypt</option>
							<option value="SV">El Salvador</option>
							<option value="GQ">Equatorial Guinea</option>
							<option value="ER">Eritrea</option>
							<option value="EE">Estonia</option>
							<option value="ET">Ethiopia</option>
							<option value="FK">Falkland Islands (Malvinas)</option>
							<option value="FO">Faroe Islands</option>
							<option value="FJ">Fiji</option>
							<option value="FI">Finland</option>
							<option value="FR">France</option>
							<option value="GF">French Guiana</option>
							<option value="PF">French Polynesia</option>
							<option value="TF">French Southern Territories</option>
							<option value="GA">Gabon</option>
							<option value="GM">Gambia</option>
							<option value="GE">Georgia</option>
							<option value="DE">Germany</option>
							<option value="GH">Ghana</option>
							<option value="GI">Gibraltar</option>
							<option value="GR">Greece</option>
							<option value="GL">Greenland</option>
							<option value="GD">Grenada</option>
							<option value="GP">Guadeloupe</option>
							<option value="GU">Guam</option>
							<option value="GT">Guatemala</option>
							<option value="GG">Guernsey</option>
							<option value="GN">Guinea</option>
							<option value="GW">Guinea-Bissau</option>
							<option value="GY">Guyana</option>
							<option value="HT">Haiti</option>
							<option value="HM">Heard Island and McDonald Islands</option>
							<option value="VA">Holy See (Vatican City State)</option>
							<option value="HN">Honduras</option>
							<option value="HK">Hong Kong</option>
							<option value="HU">Hungary</option>
							<option value="IS">Iceland</option>
							<option value="IN">India</option>
							<option value="ID">Indonesia</option>
							<option value="IR">Iran, Islamic Republic of</option>
							<option value="IQ">Iraq</option>
							<option value="IE">Ireland</option>
							<option value="IM">Isle of Man</option>
							<option value="IL">Israel</option>
							<option value="IT">Italy</option>
							<option value="JM">Jamaica</option>
							<option value="JP">Japan</option>
							<option value="JE">Jersey</option>
							<option value="JO">Jordan</option>
							<option value="KZ">Kazakhstan</option>
							<option value="KE">Kenya</option>
							<option value="KI">Kiribati</option>
							<option value="KP">Korea, Democratic People's Republic of</option>
							<option value="KR">Korea, Republic of</option>
							<option value="KW">Kuwait</option>
							<option value="KG">Kyrgyzstan</option>
							<option value="LA">Lao People's Democratic Republic</option>
							<option value="LV">Latvia</option>
							<option value="LB">Lebanon</option>
							<option value="LS">Lesotho</option>
							<option value="LR">Liberia</option>
							<option value="LY">Libya</option>
							<option value="LI">Liechtenstein</option>
							<option value="LT">Lithuania</option>
							<option value="LU">Luxembourg</option>
							<option value="MO">Macao</option>
							<option value="MK">Macedonia, the former Yugoslav Republic of</option>
							<option value="MG">Madagascar</option>
							<option value="MW">Malawi</option>
							<option value="MY">Malaysia</option>
							<option value="MV">Maldives</option>
							<option value="ML">Mali</option>
							<option value="MT">Malta</option>
							<option value="MH">Marshall Islands</option>
							<option value="MQ">Martinique</option>
							<option value="MR">Mauritania</option>
							<option value="MU">Mauritius</option>
							<option value="YT">Mayotte</option>
							<option value="MX">Mexico</option>
							<option value="FM">Micronesia, Federated States of</option>
							<option value="MD">Moldova, Republic of</option>
							<option value="MC">Monaco</option>
							<option value="MN">Mongolia</option>
							<option value="ME">Montenegro</option>
							<option value="MS">Montserrat</option>
							<option value="MA">Morocco</option>
							<option value="MZ">Mozambique</option>
							<option value="MM">Myanmar</option>
							<option value="NA">Namibia</option>
							<option value="NR">Nauru</option>
							<option value="NP">Nepal</option>
							<option value="NL">Netherlands</option>
							<option value="NC">New Caledonia</option>
							<option value="NZ">New Zealand</option>
							<option value="NI">Nicaragua</option>
							<option value="NE">Niger</option>
							<option value="NG">Nigeria</option>
							<option value="NU">Niue</option>
							<option value="NF">Norfolk Island</option>
							<option value="MP">Northern Mariana Islands</option>
							<option value="NO">Norway</option>
							<option value="OM">Oman</option>
							<option value="PK">Pakistan</option>
							<option value="PW">Palau</option>
							<option value="PS">Palestinian Territory, Occupied</option>
							<option value="PA">Panama</option>
							<option value="PG">Papua New Guinea</option>
							<option value="PY">Paraguay</option>
							<option value="PE">Peru</option>
							<option value="PH">Philippines</option>
							<option value="PN">Pitcairn</option>
							<option value="PL">Poland</option>
							<option value="PT">Portugal</option>
							<option value="PR">Puerto Rico</option>
							<option value="QA">Qatar</option>
							<option value="RE">Réunion</option>
							<option value="RO">Romania</option>
							<option value="RU">Russian Federation</option>
							<option value="RW">Rwanda</option>
							<option value="BL">Saint Barthélemy</option>
							<option value="SH">Saint Helena, Ascension and Tristan da Cunha</option>
							<option value="KN">Saint Kitts and Nevis</option>
							<option value="LC">Saint Lucia</option>
							<option value="MF">Saint Martin (French part)</option>
							<option value="PM">Saint Pierre and Miquelon</option>
							<option value="VC">Saint Vincent and the Grenadines</option>
							<option value="WS">Samoa</option>
							<option value="SM">San Marino</option>
							<option value="ST">Sao Tome and Principe</option>
							<option value="SA">Saudi Arabia</option>
							<option value="SN">Senegal</option>
							<option value="RS">Serbia</option>
							<option value="SC">Seychelles</option>
							<option value="SL">Sierra Leone</option>
							<option value="SG">Singapore</option>
							<option value="SX">Sint Maarten (Dutch part)</option>
							<option value="SK">Slovakia</option>
							<option value="SI">Slovenia</option>
							<option value="SB">Solomon Islands</option>
							<option value="SO">Somalia</option>
							<option value="ZA">South Africa</option>
							<option value="GS">South Georgia and the South Sandwich Islands</option>
							<option value="SS">South Sudan</option>
							<option value="ES">Spain</option>
							<option value="LK">Sri Lanka</option>
							<option value="SD">Sudan</option>
							<option value="SR">Suriname</option>
							<option value="SJ">Svalbard and Jan Mayen</option>
							<option value="SZ">Swaziland</option>
							<option value="SE">Sweden</option>
							<option value="CH">Switzerland</option>
							<option value="SY">Syrian Arab Republic</option>
							<option value="TW">Taiwan, Province of China</option>
							<option value="TJ">Tajikistan</option>
							<option value="TZ">Tanzania, United Republic of</option>
							<option value="TH">Thailand</option>
							<option value="TL">Timor-Leste</option>
							<option value="TG">Togo</option>
							<option value="TK">Tokelau</option>
							<option value="TO">Tonga</option>
							<option value="TT">Trinidad and Tobago</option>
							<option value="TN">Tunisia</option>
							<option value="TR">Turkey</option>
							<option value="TM">Turkmenistan</option>
							<option value="TC">Turks and Caicos Islands</option>
							<option value="TV">Tuvalu</option>
							<option value="UG">Uganda</option>
							<option value="UA">Ukraine</option>
							<option value="AE">United Arab Emirates</option>
							<option value="GB">United Kingdom</option>
							<option value="US">United States</option>
							<option value="UM">United States Minor Outlying Islands</option>
							<option value="UY">Uruguay</option>
							<option value="UZ">Uzbekistan</option>
							<option value="VU">Vanuatu</option>
							<option value="VE">Venezuela, Bolivarian Republic of</option>
							<option value="VN">Viet Nam</option>
							<option value="VG">Virgin Islands, British</option>
							<option value="VI">Virgin Islands, U.S.</option>
							<option value="WF">Wallis and Futuna</option>
							<option value="EH">Western Sahara</option>
							<option value="YE">Yemen</option>
							<option value="ZM">Zambia</option>
							<option value="ZW">Zimbabwe</option>
						</select>
						<input type="button" value="ָApply" style="float: right;" onclick="runReport()"></input>
					</fieldset>
  					</div><!-- end classificationFilters -->
  					
  					<br /> <br />
  				
  				<div id="reports" style="display: none;">
  				<fieldset>
  					<!-- report types -->
  					<legend><span class="hoverable">Please Select a Report </span></legend>
  						<span class="hoverable"> Reports </span>
  						<select id="reportOptions" onchange="revertFilters(); runReport();">
  							<option value=""></option>
  							<option value="1">Student Distribution by Academic Level</option>
  							<option value="2">Student Distribution by Classification</option>
  							<option value="3">Student Distribution by Gender</option>
  							<option value="4">Student Distribution by College</option>
  							<option value="5">Student Distribution by World Region</option>
  							<option value="6">Distribution by Top 10 Countries</option>
  							<option value="7">Enrollment Trends</option>
  						</select>
  				</fieldset>
  				</div> <!-- end reports -->
  				
  				<!-- potential room for footer -->
  				<br /> <br />
  				<!-- <?php echo ('<html><p>'.$_SERVER['SERVER_ADDR'].'</p></html>'); ?> -->
  			</div> <!-- end mainContent -->
  		</div> <!-- end container -->
  	</body>

</html>