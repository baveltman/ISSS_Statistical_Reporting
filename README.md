ISSS_Reporting_Application Documentation
=========================================

The full contents of the application are found in the ISSS_Reporting_Application Folder.
This file provides basic installation instructions along with a description of the application's components.

The ISSS_Reporting_Application Folder should contains the following folders and files:

- isss_statistics.php
- createChart.php
- parser.jar
- database folder
- images folder
- js folder
- Parser folder
- styles folder 
- Readme file

Installation
==============
- The demo application is installed on UT's z server. You can access it by going to the following URL:
https://zweb.cs.utexas.edu/users/cs105-s13/bveltman/ISSS_Application/isss_statistics.php

- If you are interested in installing the application on your server please follow the instructions below:
  1. Ensure that you have the latest version of PHP and MySQL on your server. Installation instructions for PHP can be found here: http://php.net/manual/en/install.php. While, installation instructions for MySQL can be found here: http://dev.mysql.com/doc/refman/5.1/en/installing.html.
	2. After installing MySQL on your server, go to the database folder and update DBinfo.php with the login credentials to your MySQL database.
	3. Open your MySQL database and run the script found in ISSS_mysqlDDL.sql.
	4. Go to Parser/src and open ISSS_Parser.java. Update lines 137-139 with the login credentials for your MySQL database. Note, the string url should look as followed: "jdbc:mysql://Address of your server:3306/name of your database"
	5. Now you can run the desktop parser application from root folder by running:

```
$ java -jar parser.java
```


	6. Currently, the desktop application parser csv files in the following format name,eid,stdnt type,lse,gender,class,major code,school code,school name,country code,special,irreg

	example: "Veltman, Boris",eid001,REG,2010,M,FRESHMAN,1,1,Cockrell School of Engineering,1,x,

	Note, a sample csv file can be found under Parser/sample.csv

isss_statistics.php
===================
This file contains the code for the web application user interface (hereafter, UI). The UI is supported
by Google's jquery library (http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js) and the highcharts javascript library 
(http://www.highcharts.com). There are 4 javascript functions that have been written to support the UI.

showFilters (reportVal) - is called from within runReport(), which is activated on selection change 
from select list #reportOptions. Based on variable reportVal the function displays the advanced filters
list #classificationFilters available for the current report option. 

revertFilters() - is called on selection change from select list #reportOptions. Reverts all filters in 
#classificationFilters to their default values.

ShowReports() - is called once, only on page load and displays region #reports which contains #reportOptions.
Also displays #worldMap. 

runReport() - based on selection from #reportOptions and #classificationFilters, runReport() uses ajax to communicate
with script createChart.php and send it all the information necessary to process a query from the database. 
createChart.php responds with a javascript function graph() containing the code for the chart requested. 
The code returned by createChart.php is dynamically inserted into div #graphContainer and graph() is
then called, rendering the chart on screen for the user. 


createChart.php
================
This file contains the script which interacts with the database and sends isss_statistics.php javascript code
to render a highchart on the web UI. Using ajax the script retrieves the information sent by the user while
interacting with the web UI. Based on this information the script decides which report to build a chart for 
and fulfills the user's request. There are 7 report options on the UI, each is associated with a value sent to the script.
Two functions are defined for createChart.php:

makeJoins($gender, $region, $country, $program, $college) - makes joins for a mysql query for a given report based on
the information requested by the user. The variables of the function contain this information.

makeQuery($level, $gender, $region, $country, $program, $college) - constructs the where conditions for a query based on 
the information requested by the user. The variables of the function contain this information.

The syntax of the code for the charts generated within each query is based on the highcharts API. 

parser.jar
=============
parser.jar is an executable for the desktop application which parser ISSS data passed in a csv file and
populates the database behind the web application. 

database folder
===============
The database folder contains files that are relevant to the mysql database associated with the application
The following files should be found within the database folder:

- DBInfo.php - contains the hostname, username, database name, and password for the mysql database associated with the application.
This file must be current, as both  isss_statistics.php and createChart.php rely on this file to make connection with the database. 

- ISSS_projectDDL.sql - contains the ddl to create the schema of the database queried by the application. The file also contains the 
dll to prime the program, country, and immigration_info tables. 

- projectLogicalModel - an image describing the logical model of the database in Information Engineering notation. 

- projectRelationalModel - an image describing the relational model of the database, 
which is derived from the logical model described in projectLogicalModel

- projectLogicalModel.dmd - an interactive file which can be loaded using OracleDataModeler. 
This file contains both the logical and relational models of the database and can be edited/manipulated with 
OracleDataModeler when designing additions to the database.

Note that each table in the database contains 6 flex columns (2 Int, 1 Date, 2 Varchar) in the event more data needs to 
be placed in any of the tables of the database. 

images folder
=============
contains the images found on the web UI. This folder and its contents must remain as they are to ensure the UI's images load correctly. 

js folder
==========
This folder contains several javascript files utilized by the web UI. 

- excanvas.compiled.js - a javascript library by highcharts which contains scripts needed for using the highcharts API.

- exporting.js - a javascript library by highcharts which allows users to export charts as a JPG, PNG, or vector image. 

- gray.js - a javascript library which manipulates the look of highcharts. Not currently used in this application as
it does not match the desired color palate.

- highcharts.js -  The main javascript library by highcharts which contains scripts needed for using the highcharts API.

Note, highcharts.js and exporting.js are absolutely necessary for the application to run correctly. 

Parser folder
==============
The Parser folder contains code for the desktop application that takes ISSS information as a csv and populates the database
with it. Several things are contained in the Parser folder:

- sample.csv - a sample csv file with which the database can be populated

- mysql-connector-java-5.1.24 folder - this folder contains code for the JDBC driver created by Oracle. 
The JDBC driver allows java applications to communicate with a mysql database. 

- bin - the bin folder contains the ISSS_Parser.class file which run the desktop application
To run this file on the command line from the bin folder (note, the $ is the command line prompt and not part of the command):

```
$ export CLASSPATH=$CLASSPATH:"you fill the explicit path to here/Parser/mysql-connector-java-5.1.24/mysql-connector-java-5.1.24-bin.jar"
```

this sets the classPath for java to find the JDBC driver needed to run the application.

```
$ java ISSS_Parser
```
- src - contains ISSS_Parser.java the source code for the desktop application
look at the documentation at docs/index.html for a detailed description of the application.
Note, to auto-generate this documentation some private methods were temporarily marked as public. 
However, in the documentation all of these methods have PRIVATE METHOD in their description. 

- docs - contains the documentation for the parser. open index.html in a browser to view the documentation

styles folder
==============
contains the style sheets for the application. 

- default.css - is a style sheet used by ISSS for their website. It is utilized by the application
to maintain an identical style to the ISSS webstie. 

- application.css - contains css for elements that are application specific. 

