<?php

/**
 * file:    index.php
 * version: 10.0
 * package: Simple Phishing Toolkit (spt)
 * component:   Editor
 * copyright:   Copyright (C) 2011 The SPT Project. All rights reserved.
 * license: GNU/GPL, see license.htm.
 * 
 * This file is part of the Simple Phishing Toolkit (spt).
 * 
 * spt is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, under version 3 of the License.
 *
 * spt is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with spt.  If not, see <http://www.gnu.org/licenses/>.
 * */

//turn off PHP error reporting, some platforms report error on form post url, but post url is correct
error_reporting ( 0 );

// verify session is authenticated and not hijacked
$includeContent = "../includes/is_authenticated.php";
if ( file_exists ( $includeContent ) ) {
    require_once $includeContent;
} else {
    header ( 'location:../errors/404_is_authenticated.php' );
}
?>

<!DOCTYPE HTML> 
<html>
    <head>
        <title>spt - summary</title>
        <!--meta-->
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="description" content="welcome to spt - simple phishing toolkit.  spt is a super simple but powerful phishing toolkit." />
        <!--favicon-->
        <link rel="shortcut icon" href="../images/favicon.ico" />
        <!--css-->
        <link rel="stylesheet" href="../includes/spt.css" type="text/css" />
        <link rel="stylesheet" href="spt_summary.css" type="text/css" />
        <!--script-->
        <script language="Javascript" type="text/javascript">
            function selectTemplate(template_id,file) 
            { 
                //re-direct
                window.location = ".?t="+template_id+"&f="+file;                                
            }
        </script>
        <script language="Javascript" type="text/javascript">
            function selectPackage(package_id,file) 
            { 
                //re-direct
                window.location = ".?p="+package_id+"&f="+file;                             
            }
        </script>
        <script type="text/javascript" src="../includes/escape.js"></script>
        <script src="../includes/jquery-1.7.min.js"></script>
        <script src="../includes/jquery-ui.min.js"></script>
        <script type="text/javascript" src="../includes/highcharts/js/highcharts.js"></script>
        <!--<script type="text/javascript" src="../includes/highcharts/js/modules/exporting.js"></script>-->
        <script type="text/javascript">







        var link_educated;
        $(document).ready(function() {
                link_educated = new Highcharts.Chart({
                    chart: {
                        renderTo: 'link_educated_container',
                        plotBackgroundColor: null,
                        plotBorderWidth: null,
                        plotShadow: false
                    },
                    title: {
                        text: 'Education Rate of Those Who Clicked Link'
                    },
                    tooltip: {
                        formatter: function() {
                            return '<b>'+ this.point.name +'</b>: '+ Math.round(this.percentage*Math.pow(10,2))/Math.pow(10,2) +'% (' + this.y + ')';
                        }
                    },   
                    plotOptions: {
                        pie: {
                            allowPointSelect: true,
                            cursor: 'pointer',
                            dataLabels: {
                                enabled: true,
                                formatter: function() {
                                    return Math.round(this.percentage*Math.pow(10,0))/Math.pow(10,0) +'% (' + this.y + ')' ;
                                }
                            },
                            showInLegend: true
                        } 
                    },
                    series: [{
                            type: 'pie',
                            name: 'Phish Pie',
                            data: [
                                <?php
                                    //connect to database
                                    include('../spt_config/mysql_config.php');
                                    //get filters if they are set
                                    //campaign id
                                    if (isset($_REQUEST['bt_campaign']) && $_REQUEST['bt_campaign'] != 'All') {
                                        $bt_campaign_id = $_REQUEST['bt_campaign'];
                                        //get all campaign ids
                                        $r = mysql_query("SELECT id FROM campaigns");
                                        while ($ra = mysql_fetch_assoc($r)) {
                                            if ($bt_campaign_id == $ra['id']) {
                                                $match = 1;
                                            }
                                        }
                                        //validate its a valid campaign id
                                        if (!isset($match)) {
                                            $_SESSION['alert_message'] = "Please specify a valid campaign";
                                            header('location:./#alert');
                                            exit;
                                        }
                                        //reset match
                                        unset($match);
                                    }
                                    //browser
                                    if (isset($_REQUEST['bt_browser']) && $_REQUEST['bt_browser'] != 'All') {

                                        $bt_browser = $_REQUEST['bt_browser'];
                                        //get all types of browsers
                                        $r = mysql_query("SELECT DISTINCT browser FROM campaigns_responses");
                                        while ($ra = mysql_fetch_assoc($r)) {
                                            if ($bt_browser == $ra['browser']) {
                                                $match = 1;
                                            }
                                        }
                                        //validate its a valid browser
                                        if (!isset($match)) {
                                            $_SESSION['alert_message'] = "Please specify a selectable browser";
                                            header('location:./#alert');
                                            exit;
                                        }
                                        //reset match
                                        unset($match);
                                    }

                                    //group
                                    if (isset($_REQUEST['bt_group']) && $_REQUEST['bt_group'] != 'All') {
                                        $bt_group_name = $_REQUEST['bt_group'];
                                        
                                        $r = mysql_query("SELECT DISTINCT group_name FROM targets");
                                        while ($ra = mysql_fetch_assoc($r)) {
                                            if ($bt_group_name == $ra['group_name']) {
                                                $match = 1;
                                            }
                                        }
                                        //validate its a valid browser
                                        if (!isset($match)) {
                                            $_SESSION['alert_message'] = "Please specify a valid group name";
                                            header('location:./#alert');
                                            exit;
                                        }
                                        //reset match
                                        unset($match);
                                    }
                                    //set SQL statements
                                    /*$total_phishes_sql = "SELECT target_id FROM campaigns_responses WHERE sent = 2 AND sent_time IS NOT NULL";
                                    $total_sql = "SELECT target_id FROM campaigns_responses WHERE post IS NOT NULL AND sent = 2 AND sent_time IS NOT NULL";
                                    $total_link_only_sql = "SELECT target_id FROM campaigns_responses WHERE post IS NULL AND link != 0 AND sent = 2 AND sent_time IS NOT NULL";
                                    //append any filters if necessary
                                    if (isset($bt_campaign_id)) {
                                        $total_phishes_sql .= " AND campaign_id = " . $bt_campaign_id;
                                        $total_sql .= " AND campaign_id = " . $bt_campaign_id;
                                        $total_link_only_sql .= " AND campaign_id = " . $bt_campaign_id;
                                    }
                                    //append any filters if necessary
                                    if (isset($bt_browser)) {
                                        $total_phishes_sql .= " AND browser = '" . $bt_browser . "'";
                                        $total_sql .= " AND browser = '" . $bt_browser . "'";
                                        $total_link_only_sql .= " AND browser = '" . $bt_browser . "'";
                                    }
                                    //get total number of successful phishes sent
                                    $r = mysql_query($total_phishes_sql);
                                    $total_phishes = mysql_num_rows($r);
                                    //get total number of people who posted data
                                    $r = mysql_query($total_sql);
                                    $total_posts = mysql_num_rows($r);
                                    //get total number of people who clicked the link but didn't post data
                                    $r = mysql_query($total_link_only_sql);
                                    $total_link_only = mysql_num_rows($r);
                                    //calculate no reponse
                                    $total_no_response = $total_phishes - $total_posts - $total_link_only;
                                    if ($total_link_only == 0 && $total_no_response == 0 && $total_posts == 0) {
                                        echo "['No Responses Yet', 0]";
                                    } else {
                                        //print results in highcharts format
                                        echo "['Did Not Click', " . $total_no_response . "],";
                                        echo "['Followed Link', " . $total_link_only . "],";
                                        echo "['Submitted Form', " . $total_posts . "],";
                                    }*/

                                    $noneducated_query="SELECT DISTINCT(campaign_id), CONCAT(targets.fname,' ',targets.lname) as name,targets.group_name, SUM(link) as link, SUM(trained) as trained, COUNT(campaigns_responses.post) as post FROM campaigns_responses JOIN targets ON campaigns_responses.target_id = targets.id WHERE sent=2";
                                    //append any filters if necessary
                                    if (isset($bt_campaign_id)) {
                                        $noneducated_query .= " AND campaign_id = " . $bt_campaign_id;
                                    }
                                    //append any filters if necessary
                                    if (isset($bt_browser)) {
                                        $noneducated_query .= " AND browser = '" . $bt_browser . "'";
                                    }

                                    if(isset($bt_group_name)) {
                                        $noneducated_query .= " AND targets.group_name = '".$bt_group_name."'";
                                    }
                                    $noneducated_query.=" GROUP BY campaign_id";

                                    $r=mysql_query($noneducated_query);
                                    $noneducated = 0;
                                    $educated = 0;
                                    while ($ra = mysql_fetch_assoc($r)) {
                                        
                                        if ($ra['link'] != 0 && $ra['trained'] == 0){
                                            $noneducated+=1;
                                        }elseif($ra['link']!=0 && $ra['trained']!=0){
                                            $educated+=1;
                                        }

                                    }
                                    if ($noneducated == 0 && $educated == 0) {
                                        echo "['No Responses Yet', 0]";
                                    } else {
                                        //print results in highcharts format
                                        echo "['Educated', " . $educated . "],";
                                        echo "['NonEducated', " . $noneducated . "],";
                                    }


                                ?>
                            ]
                    }]
                });
            });

        var post_educated;
        $(document).ready(function() {
                post_educated = new Highcharts.Chart({
                    chart: {
                        renderTo: 'post_educated_container',
                        plotBackgroundColor: null,
                        plotBorderWidth: null,
                        plotShadow: false
                    },
                    color: {
                        colors: [
                           '#FF0000',
                           '#00FF00'
                        ]
                    },
                    title: {
                        text: 'Education Rate of Those Who Posted'
                    },
                    tooltip: {
                        formatter: function() {
                            return '<b>'+ this.point.name +'</b>: '+ Math.round(this.percentage*Math.pow(10,2))/Math.pow(10,2) +'% (' + this.y + ')';
                        }
                    },   
                    plotOptions: {
                        pie: {
                            allowPointSelect: true,
                            cursor: 'pointer',
                            dataLabels: {
                                enabled: true,
                                formatter: function() {
                                    return Math.round(this.percentage*Math.pow(10,0))/Math.pow(10,0) +'% (' + this.y + ')' ;
                                }
                            },
                            showInLegend: true
                        } 
                    },
                    series: [{
                            type: 'pie',
                            name: 'Phish Pie',
                            data: [
                                <?php
                                    //connect to database
                                    include('../spt_config/mysql_config.php');
                                    //get filters if they are set
                                    //campaign id
                                    if (isset($_REQUEST['bt_campaign']) && $_REQUEST['bt_campaign'] != 'All') {
                                        $bt_campaign_id = $_REQUEST['bt_campaign'];
                                        //get all campaign ids
                                        $r = mysql_query("SELECT id FROM campaigns");
                                        while ($ra = mysql_fetch_assoc($r)) {
                                            if ($bt_campaign_id == $ra['id']) {
                                                $match = 1;
                                            }
                                        }
                                        //validate its a valid campaign id
                                        if (!isset($match)) {
                                            $_SESSION['alert_message'] = "Please specify a valid campaign";
                                            header('location:./#alert');
                                            exit;
                                        }
                                        //reset match
                                        unset($match);
                                    }
                                    //browser
                                    if (isset($_REQUEST['bt_browser']) && $_REQUEST['bt_browser'] != 'All') {

                                        $bt_browser = $_REQUEST['bt_browser'];
                                        //get all types of browsers
                                        $r = mysql_query("SELECT DISTINCT browser FROM campaigns_responses");
                                        while ($ra = mysql_fetch_assoc($r)) {
                                            if ($bt_browser == $ra['browser']) {
                                                $match = 1;
                                            }
                                        }
                                        //validate its a valid browser
                                        if (!isset($match)) {
                                            $_SESSION['alert_message'] = "Please specify a selectable browser";
                                            header('location:./#alert');
                                            exit;
                                        }
                                        //reset match
                                        unset($match);
                                    }
                                    //group
                                    if (isset($_REQUEST['bt_group']) && $_REQUEST['bt_group'] != 'All') {
                                        $bt_group_name = $_REQUEST['bt_group'];
    
                                        $r = mysql_query("SELECT DISTINCT group_name FROM targets");
                                        while ($ra = mysql_fetch_assoc($r)) {
                                            if ($bt_group_name == $ra['group_name']) {
                                                $match = 1;
                                            }
                                        }
                                        //validate its a valid browser
                                        if (!isset($match)) {
                                            $_SESSION['alert_message'] = "Please specify a valid group name";
                                            header('location:./#alert');
                                            exit;
                                        }
                                        //reset match
                                        unset($match);
                                    }

                                    //set SQL statements
                                    /*$total_phishes_sql = "SELECT target_id FROM campaigns_responses WHERE sent = 2 AND sent_time IS NOT NULL";
                                    $total_sql = "SELECT target_id FROM campaigns_responses WHERE post IS NOT NULL AND sent = 2 AND sent_time IS NOT NULL";
                                    $total_link_only_sql = "SELECT target_id FROM campaigns_responses WHERE post IS NULL AND link != 0 AND sent = 2 AND sent_time IS NOT NULL";
                                    //append any filters if necessary
                                    if (isset($bt_campaign_id)) {
                                        $total_phishes_sql .= " AND campaign_id = " . $bt_campaign_id;
                                        $total_sql .= " AND campaign_id = " . $bt_campaign_id;
                                        $total_link_only_sql .= " AND campaign_id = " . $bt_campaign_id;
                                    }
                                    //append any filters if necessary
                                    if (isset($bt_browser)) {
                                        $total_phishes_sql .= " AND browser = '" . $bt_browser . "'";
                                        $total_sql .= " AND browser = '" . $bt_browser . "'";
                                        $total_link_only_sql .= " AND browser = '" . $bt_browser . "'";
                                    }
                                    //get total number of successful phishes sent
                                    $r = mysql_query($total_phishes_sql);
                                    $total_phishes = mysql_num_rows($r);
                                    //get total number of people who posted data
                                    $r = mysql_query($total_sql);
                                    $total_posts = mysql_num_rows($r);
                                    //get total number of people who clicked the link but didn't post data
                                    $r = mysql_query($total_link_only_sql);
                                    $total_link_only = mysql_num_rows($r);
                                    //calculate no reponse
                                    $total_no_response = $total_phishes - $total_posts - $total_link_only;
                                    if ($total_link_only == 0 && $total_no_response == 0 && $total_posts == 0) {
                                        echo "['No Responses Yet', 0]";
                                    } else {
                                        //print results in highcharts format
                                        echo "['Did Not Click', " . $total_no_response . "],";
                                        echo "['Followed Link', " . $total_link_only . "],";
                                        echo "['Submitted Form', " . $total_posts . "],";
                                    }*/

                                    $noneducated_query="SELECT DISTINCT(campaign_id), CONCAT(targets.fname,' ',targets.lname) as name, targets.group_name,SUM(link) as link, SUM(trained) as trained, COUNT(campaigns_responses.post) as post FROM campaigns_responses JOIN targets ON campaigns_responses.target_id = targets.id WHERE sent=2";
                                    //append any filters if necessary
                                    if (isset($bt_campaign_id)) {
                                        $noneducated_query .= " AND campaign_id = " . $bt_campaign_id;
                                    }
                                    //append any filters if necessary
                                    if (isset($bt_browser)) {
                                        $noneducated_query .= " AND browser = '" . $bt_browser . "'";
                                    }
                                    if(isset($bt_group_name)){
                                        $noneducated_query .= " AND targets.group_name = '".$bt_group_name."'";
                                    }
                                    $noneducated_query.=" GROUP BY campaign_id";

                                    $r=mysql_query($noneducated_query);
                                    $noneducated = 0;
                                    $educated = 0;
                                    while ($ra = mysql_fetch_assoc($r)) {
                                        
                                        if ($ra['post'] != 0 && $ra['trained'] == 0){
                                            $noneducated+=1;
                                        }elseif($ra['post']!=0 && $ra['trained']!=0){
                                            $educated+=1;
                                        }

                                    }
                                    if ($noneducated == 0 && $educated == 0) {
                                        echo "['No Responses Yet', 0]";
                                    } else {
                                        //print results in highcharts format
                                        echo "['Educated', " . $educated . "],";
                                        echo "['NonEducated', " . $noneducated . "],";
                                    }


                                ?>
                            ]
                    }]
                });
            });



        var phish_pie;
            $(document).ready(function() {
                pish_pie = new Highcharts.Chart({
                    chart: {
                        renderTo: 'phish_pie_container',
                        plotBackgroundColor: null,
                        plotBorderWidth: null,
                        plotShadow: false
                    },
                    title: {
                        text: 'Phish Pie'
                    },
                    tooltip: {
                        formatter: function() {
                            return '<b>'+ this.point.name +'</b>: '+ Math.round(this.percentage*Math.pow(10,2))/Math.pow(10,2) +'% (' + this.y + ')';
                        }
                    },   
                    plotOptions: {
                        pie: {
                            allowPointSelect: true,
                            cursor: 'pointer',
                            dataLabels: {
                                enabled: true,
                                formatter: function() {
                                    return Math.round(this.percentage*Math.pow(10,0))/Math.pow(10,0) +'% (' + this.y + ')' ;
                                }
                            },
                            showInLegend: true
                        } 
                    },
                    series: [{
                            type: 'pie',
                            name: 'Phish Pie',
                            data: [
                                <?php
                                    //connect to database
                                    include('../spt_config/mysql_config.php');
                                    //get filters if they are set
                                    //campaign id
                                    if (isset($_REQUEST['bt_campaign']) && $_REQUEST['bt_campaign'] != 'All') {
                                        $bt_campaign_id = $_REQUEST['bt_campaign'];
                                        //get all campaign ids
                                        $r = mysql_query("SELECT id FROM campaigns");
                                        while ($ra = mysql_fetch_assoc($r)) {
                                            if ($bt_campaign_id == $ra['id']) {
                                                $match = 1;
                                            }
                                        }
                                        //validate its a valid campaign id
                                        if (!isset($match)) {
                                            $_SESSION['alert_message'] = "Please specify a valid campaign";
                                            header('location:./#alert');
                                            exit;
                                        }
                                        //reset match
                                        unset($match);
                                    }
                                    //browser
                                    if (isset($_REQUEST['bt_browser']) && $_REQUEST['bt_browser'] != 'All') {
                                        $bt_browser = $_REQUEST['bt_browser'];
                                        //get all types of browsers
                                        $r = mysql_query("SELECT DISTINCT browser FROM campaigns_responses");
                                        while ($ra = mysql_fetch_assoc($r)) {
                                            if ($bt_browser == $ra['browser']) {
                                                $match = 1;
                                            }
                                        }
                                        //validate its a valid browser
                                        if (!isset($match)) {
                                            $_SESSION['alert_message'] = "Please specify a selectable browser";
                                            header('location:./#alert');
                                            exit;
                                        }
                                        //reset match
                                        unset($match);
                                    }
                                    //group
                                    if (isset($_REQUEST['bt_group']) && $_REQUEST['bt_group'] != 'All') {
                                        $bt_group_name = $_REQUEST['bt_group'];
    
                                        $r = mysql_query("SELECT DISTINCT group_name FROM targets");
                                        while ($ra = mysql_fetch_assoc($r)) {
                                            if ($bt_group_name == $ra['group_name']) {
                                                $match = 1;
                                            }
                                        }
                                        //validate its a valid browser
                                        if (!isset($match)) {
                                            $_SESSION['alert_message'] = "Please specify a valid group name";
                                            header('location:./#alert');
                                            exit;
                                        }
                                        //reset match
                                        unset($match);
                                    }
                                    //set SQL statements
                                    $total_phishes_sql = "SELECT target_id, targets.group_name FROM campaigns_responses JOIN targets ON campaigns_responses.target_id = targets.id WHERE sent = 2 AND sent_time IS NOT NULL";
                                    $total_sql = "SELECT target_id, targets.group_name FROM campaigns_responses JOIN targets ON campaigns_responses.target_id = targets.id WHERE post IS NOT NULL AND sent = 2 AND sent_time IS NOT NULL";
                                    $total_link_only_sql = "SELECT target_id, targets.group_name FROM campaigns_responses JOIN targets ON campaigns_responses.target_id = targets.id WHERE post IS NULL AND link != 0 AND sent = 2 AND sent_time IS NOT NULL";
                                    //append any filters if necessary
                                    if (isset($bt_campaign_id)) {
                                        $total_phishes_sql .= " AND campaign_id = " . $bt_campaign_id;
                                        $total_sql .= " AND campaign_id = " . $bt_campaign_id;
                                        $total_link_only_sql .= " AND campaign_id = " . $bt_campaign_id;
                                    }
                                    //append any filters if necessary
                                    if (isset($bt_browser)) {
                                        $total_phishes_sql .= " AND browser = '" . $bt_browser . "'";
                                        $total_sql .= " AND browser = '" . $bt_browser . "'";
                                        $total_link_only_sql .= " AND browser = '" . $bt_browser . "'";
                                    }

                                    if (isset($bt_group_name)) {
                                        $total_phishes_sql .=" AND targets.group_name = '".$bt_group_name."'";
                                        $total_sql .=" AND targets.group_name = '".$bt_group_name."'";
                                        $total_link_only_sql .=" AND targets.group_name = '".$bt_group_name."'";
                                    }
                                    //get total number of successful phishes sent
                                    $r = mysql_query($total_phishes_sql);
                                    $total_phishes = mysql_num_rows($r);
                                    //get total number of people who posted data
                                    $r = mysql_query($total_sql);
                                    $total_posts = mysql_num_rows($r);
                                    //get total number of people who clicked the link but didn't post data
                                    $r = mysql_query($total_link_only_sql);
                                    $total_link_only = mysql_num_rows($r);
                                    //calculate no reponse
                                    $total_no_response = $total_phishes - $total_posts - $total_link_only;
                                    if ($total_link_only == 0 && $total_no_response == 0 && $total_posts == 0) {
                                        echo "['No Responses Yet', 0]";
                                    } else {
                                        //print results in highcharts format
                                        echo "['Did Not Click', " . $total_no_response . "],";
                                        echo "['Followed Link', " . $total_link_only . "],";
                                        echo "['Submitted Form', " . $total_posts . "],";
                                    }
                                ?>
                            ]
                    }]
                });
            });



        var bad_targets;
            $(document).ready(function() {
                bad_targets = new Highcharts.Chart({
                    chart: {
                        renderTo: 'bad_targets_container',
                        type: 'bar'
                    },
                    title: {
                        text: 'Top 10 High Risk Targets'
                    },
                    tooltip: {
                        formatter: function() {
                            return '<b>'+ this.series.name +': '+ this.y +'';
                        }
                    },                      
                    <?php
                        //connect to database
                        include('../spt_config/mysql_config.php');
                        //get filters if they are set
                        //campaign id
                        if (isset($_POST['bt_campaign']) && $_POST['bt_campaign'] != 'All') {
                            $bt_campaign_id = $_POST['bt_campaign'];
                            //get all campaign ids
                            $r = mysql_query("SELECT id FROM campaigns");
                            while ($ra = mysql_fetch_assoc($r)) {
                                if ($bt_campaign_id == $ra['id']) {
                                    $match = 1;
                                }
                            }
                            //validate its a valid campaign id
                            if (!isset($match)) {
                                $_SESSION['alert_message'] = "Please specify a valid campaign";
                                header('location:./#alert');
                                exit;
                            }
                            //reset match
                            unset($match);
                        }
                        //browser
                        if (isset($_REQUEST['bt_browser']) && $_REQUEST['bt_browser'] != 'All') {
                            $bt_browser = $_REQUEST['bt_browser'];
                            //get all types of browsers
                            $r = mysql_query("SELECT DISTINCT browser FROM campaigns_responses");
                            while ($ra = mysql_fetch_assoc($r)) {
                                if ($bt_browser == $ra['browser']) {
                                    $match = 1;
                                }
                            }
                            //validate its a valid browser
                            if (!isset($match)) {
                                $_SESSION['alert_message'] = "Please specify a selectable browser";
                                header('location:./#alert');
                                exit;
                            }
                            //reset match
                            unset($match);
                        }
                        //group
                        if (isset($_REQUEST['bt_group']) && $_REQUEST['bt_group'] != 'All') {
                            $bt_group_name = $_REQUEST['bt_group'];

                            $r = mysql_query("SELECT DISTINCT group_name FROM targets");
                            while ($ra = mysql_fetch_assoc($r)) {
                                if ($bt_group_name == $ra['group_name']) {
                                    $match = 1;
                                }
                            }
                            //validate its a valid browser
                            if (!isset($match)) {
                                $_SESSION['alert_message'] = "Please specify a valid group name";
                                header('location:./#alert');
                                exit;
                            }
                            //reset match
                            unset($match);
                        }
                        //set SQL statements
                        $bad_targets = "SELECT CONCAT(targets.fname, ' ',targets.lname) AS name, SUM(campaigns_responses.link) AS links, COUNT(campaigns_responses.post) AS posts, SUM(campaigns_responses.trained) AS trained,((SUM(campaigns_responses.link))+(COUNT(campaigns_responses.post))) AS total_response FROM campaigns_responses JOIN targets ON campaigns_responses.target_id = targets.id WHERE sent = 2";
                        //append any filters if necessary
                        //campaign
                        if (isset($bt_campaign_id)) {
                            $bad_targets .= " AND campaigns_responses.campaign_id = " . $bt_campaign_id;
                        }
                        //browser
                        if (isset($bt_browser)) {
                            $bad_targets .= " AND campaigns_responses.browser = '" . $bt_browser . "'";
                        }
                        //group
                        if (isset($bt_group_name)) {
                            $bad_targets .= " AND targets.group_name = '" . $bt_group_name . "'";
                        }
                        $bad_targets .= " GROUP BY name HAVING posts IS NOT NULL ORDER BY posts DESC, links DESC LIMIT 10";
                        //echo xAxix header for chart
                        echo "xAxis: {categories: [";
                        //get bad targets
                        $r = mysql_query($bad_targets);
                        $count = mysql_num_rows($r);
                        while ($ra = mysql_fetch_assoc($r)) {
                            //get name
                            $target_name = $ra['name'];

                            //echo xAxis data
                            echo "'" . $target_name . "'";

                            //echo comma if not the last one
                            if ($count > 1) {
                                echo ",";
                            }
                            --$count;
                        }
                        //echo xAxis closing
                        echo "]},";
                        //echo yAxis
                        echo "yAxis:{min: 0,title: {text: 'Links & Posts'}},";
                        //echo plot options
                        //echo "plotOptions:{series: {stacking: 'normal'}},";
                        //echo link only header
                        echo "series: [{name: 'Link Only',data:[";
                        //echo link only data
                        $r = mysql_query($bad_targets);
                        $count = mysql_num_rows($r);
                        while ($ra = mysql_fetch_assoc($r)) {
                            
                            $link_only = $ra['links'] - $ra['posts'];

                            echo $link_only;
                            if ($count > 1) {
                                echo ",";
                            }
                            --$count;
                        }
                        //echo link only footer
                        echo "]},";
                        //echo posts header
                        echo "{name: 'Posts',data:[";
                        //echo posts data
                        $r = mysql_query($bad_targets);
                        $count = mysql_num_rows($r);
                        while ($ra = mysql_fetch_assoc($r)) {
                            $posts = $ra['posts'];
                            echo $posts;
                            if ($count > 1) {
                                echo ",";
                            }
                            --$count;
                        }
                        //echo posts footer
                        echo "]},";

                        //echo educationsheader
                        echo "{name: 'Educated',data:[";
                        //echo posts data
                        $r = mysql_query($bad_targets);
                        $count = mysql_num_rows($r);
                        while ($ra = mysql_fetch_assoc($r)) {
                            $trained = $ra['trained'];
                            echo $trained;
                            if ($count > 1) {
                                echo ",";
                            }
                            --$count;
                        }

                        echo "]}]";
                    ?>
                });
            });
    
        var line_graph;
        $(document).ready(function() {
                line_graph = new Highcharts.Chart({
                    chart: {
                        renderTo: 'timing_container',
                        type: 'line'
                    },
                    title: {
                        text: 'Link Click Timing'
                    },
                    tooltip: {
                        formatter: function() {
                            return '<b>'+ this.series.name +': '+ this.y +' hour '+this.x;
                        }
                    },
                    <?php
                        //connect to database
                        include('../spt_config/mysql_config.php');
                        //echo xAxix header for chart
                        echo "xAxis: {title:{text:'Hours Since Sent'},categories: [";
                        
                        for($i=0; $i <=24; $i++){
                            echo $i;
                            if($i<24)
                                echo ",";
                            //else
                              //  echo "+";
                        }
                        //echo xAxis closing
                        echo "]},";
                        //echo yAxis
                        echo "yAxis:{title: {text: 'Link Clicks per Hour'},";
                        //echo plot options
                        //echo "plotOptions:{series: {stacking: 'normal'}},";
                        //echo link only header
                        echo "plotLines: [{value: 0, width:1,color:'#808080'}],";
                        //echo link only footer
                        echo "},";
                        
                        echo "series: [";

                         if (isset($_POST['bt_campaign']) && $_POST['bt_campaign'] != 'All') {
                            $bt_campaign_id = $_POST['bt_campaign'];
                            //get all campaign ids
                            $r = mysql_query("SELECT id FROM campaigns");
                            while ($ra = mysql_fetch_assoc($r)) {
                                if ($bt_campaign_id == $ra['id']) {
                                    $match = 1;
                                }
                            }
                            //validate its a valid campaign id
                            if (!isset($match)) {
                                $_SESSION['alert_message'] = "Please specify a valid campaign";
                                header('location:./#alert');
                                exit;
                            }
                            //reset match
                            unset($match);
                        }
                        //browser
                        if (isset($_REQUEST['bt_browser']) && $_REQUEST['bt_browser'] != 'All') {
                            $bt_browser = $_REQUEST['bt_browser'];
                            //get all types of browsers
                            $r = mysql_query("SELECT DISTINCT browser FROM campaigns_responses");
                            while ($ra = mysql_fetch_assoc($r)) {
                                if ($bt_browser == $ra['browser']) {
                                    $match = 1;
                                }
                            }
                            //validate its a valid browser
                            if (!isset($match)) {
                                $_SESSION['alert_message'] = "Please specify a selectable browser";
                                header('location:./#alert');
                                exit;
                            }
                            //reset match
                            unset($match);
                        }
//group
                        if (isset($_REQUEST['bt_group']) && $_REQUEST['bt_group'] != 'All') {
                            $bt_group_name = $_REQUEST['bt_group'];

                            $r = mysql_query("SELECT DISTINCT group_name FROM targets");
                            while ($ra = mysql_fetch_assoc($r)) {
                                if ($bt_group_name == $ra['group_name']) {
                                    $match = 1;
                                }
                            }
                            //validate its a valid browser
                            if (!isset($match)) {
                                $_SESSION['alert_message'] = "Please specify a valid group name";
                                header('location:./#alert');
                                exit;
                            }
                            //reset match
                            unset($match);
                        }
                        $campaign_name_query = "SELECT id, campaign_name, date_sent FROM campaigns JOIN campaigns_and_groups ON campaigns.id=campaigns_and_groups.campaign_id WHERE 1";
                        if (isset($bt_campaign_id)) {
                            $campaign_name_query .= " AND campaigns.id = " . $bt_campaign_id;
                        }
                        if(isset($bt_group_name)) {
                            $campaign_name_query .= " AND campaigns_and_groups.group_name='".$bt_group_name."'";
                        }
                        
                        $r = mysql_query($campaign_name_query);
                        $count = mysql_num_rows($r);
                        while($ra=mysql_fetch_assoc($r)){
                            $name = $ra['campaign_name'];
                            $id = $ra['id'];
                            $date_sent = $ra['date_sent'];
                            $date=new DateTime($date_sent);
                            echo "{
                                name: '$name',";
                            $link_click_query = "SELECT link_time FROM campaigns_responses JOIN targets ON campaigns_responses.target_id=targets.id WHERE campaign_id =".$id;
                            //browser
                            if (isset($bt_browser)) {
                                $link_click_query .= " AND browser = '" . $bt_browser . "'";
                            }
                            if (isset($bt_group_name)){
                                $link_click_query .= " AND targets.group_name = '".$bt_group_name."'";
                            }
                            file_put_contents("sql.log", $link_click_query);
                            $data=mysql_query($link_click_query);
                            $click_count=mysql_num_rows($data);
                            $timing_array=array(0,0,0,0,
                                                0,0,0,0,
                                                0,0,0,0,
                                                0,0,0,0,
                                                0,0,0,0,
                                                0,0,0,0,0);

                            while($data_assoc=mysql_fetch_assoc($data)){
                                if($data_assoc['link_time']==null){
                                    file_put_contents("null.log", "NULL");
                                    continue;
                                }
                                $time_clicked = new DateTime($data_assoc['link_time']);
                                $diff = date_diff($date,$time_clicked);
                                $days = $diff->d;
                                $hours = $diff->h;
                                if($days > 0){
                                    $timing_array[24]++;
                                }else{
                                    $timing_array[$hours]++;
                                }
                                
                                $click_count--;
                            }

                            echo "data: [";
                            for($i = 0; $i <=24; $i++){
                                echo $timing_array[$i];
                                if($i<24){
                                    echo",";
                                }
                            }
                            //echo "7.0, 6.9, 9.5, 14.5, 18.2, 21.5, 25.2, 26.5, 23.3, 18.3, 13.9, 9.6";
                            echo "]}";
                            if($count > 1){
                                echo",";
                            }
                            $count--;

                        }
                        /*echo "{
                            name: 'Tokyo',
                            data: [7.0, 6.9, 9.5, 14.5, 18.2, 21.5, 25.2, 26.5, 23.3, 18.3, 13.9, 9.6]
                        }, {
                            name: 'New York',
                            data: [-0.2, 0.8, 5.7, 11.3, 17.0, 22.0, 24.8, 24.1, 20.1, 14.1, 8.6, 2.5]
                        }, {
                            name: 'Berlin',
                            data: [-0.9, 0.6, 3.5, 8.4, 13.5, 17.0, 18.6, 17.9, 14.3, 9.0, 3.9, 1.0]
                        }, {
                            name: 'London',
                            data: [3.9, 4.2, 5.7, 8.5, 11.9, 15.2, 17.0, 16.6, 14.2, 10.3, 6.6, 4.8]
                        }";*/
                        echo "]";
                    ?>
                });
            });                
        </script>
        <script type="text/javascript">
        function populateTable(){
            var tbody = $('#good_targets_container tbody')[0];
            <?php
            include('../spt_config/mysql_config.php');

            if (isset($_POST['bt_campaign']) && $_POST['bt_campaign'] != 'All') {
                $bt_campaign_id = $_POST['bt_campaign'];
                //get all campaign ids
                $r = mysql_query("SELECT id FROM campaigns");
                while ($ra = mysql_fetch_assoc($r)) {
                    if ($bt_campaign_id == $ra['id']) {
                        $match = 1;
                    }
                }
                //validate its a valid campaign id
                if (!isset($match)) {
                    $_SESSION['alert_message'] = "Please specify a valid campaign";
                    header('location:./#alert');
                    exit;
                }
                //reset match
                unset($match);
            }
            //group
            if (isset($_REQUEST['bt_group']) && $_REQUEST['bt_group'] != 'All') {
                $bt_group_name = $_REQUEST['bt_group'];

                $r = mysql_query("SELECT DISTINCT group_name FROM targets");
                while ($ra = mysql_fetch_assoc($r)) {
                    if ($bt_group_name == $ra['group_name']) {
                        $match = 1;
                    }
                }
                //validate its a valid browser
                if (!isset($match)) {
                    $_SESSION['alert_message'] = "Please specify a valid group name";
                    header('location:./#alert');
                    exit;
                }
                //reset match
                unset($match);
            }
            $good_targets = "SELECT CONCAT(targets.fname, ' ',targets.lname) AS name, SUM(campaigns_responses.link) AS links, targets.group_name FROM campaigns_responses JOIN targets ON campaigns_responses.target_id = targets.id WHERE 1";
            //campaign
            if (isset($bt_campaign_id)) {
                $good_targets .= " AND campaigns_responses.campaign_id = " . $bt_campaign_id;
            }
            //group
            if (isset($bt_group_name)) {
                $good_targets .= " AND targets.group_name = '" . $bt_group_name . "'";
            }
            $good_targets .=" GROUP BY name";
            $r = mysql_query($good_targets);
            $count =0;
            while($ra=mysql_fetch_assoc($r)){
                if($ra['links']!=0)
                    continue;
                $count++;
                echo "var row = document.createElement('tr');";
                echo "row.innerHTML ='<td>".$ra['name']."</td><td>".$ra['group_name']."</td>';";
                echo "tbody.appendChild(row);";
            }
            //$good_targets .= " GROUP BY name HAVING posts IS NOT NULL ORDER BY posts DESC, links DESC LIMIT 10";
            ?>
        }

        var isPrinting = false;
        function print(){
            /*if(isPrinting){
                return;
            }
            isPrinting = true;

            console.log("PRINT");
            var div = document.getElementById("printable");
            console.log(div);
            var body = document.body;
            console.log(body);

            body.innerHTML="";
            body.appendChild(div);
            window.print();*/

            var divText = document.getElementById("printable").outerHTML;
            var myWindow = window.open('','_blank','fullscreen=yes');
            var doc = myWindow.document;
            doc.open();
            doc.write("<head><title>spt - summary</title><link rel='stylesheet' href='../includes/spt.css' type='text/css' /></head>");
            doc.write(divText);
            doc.close();
        }
            
        </script>
    </head>
    <body onload="populateTable();" >
<?php
//check to see if the alert session is set
if ( isset ( $_SESSION['alert_message'] ) ) {
    //create alert popover
    echo "<div id=\"alert\">";

    //echo the alert message
    echo "<div>" . $_SESSION['alert_message'] . "<br />";

    //close the alert message
    echo "<br /><a href=\"\"><img src=\"../images/accept.png\" alt=\"close\" /></a></div>";

    //close alert popover
    echo "</div>";

    //unset the seession
    unset ( $_SESSION['alert_message'] );
}
?>
        <div id="wrapper">
            <!--sidebar-->
        <?php include '../includes/sidebar.php'; ?>                 

            <!--content-->
            <div id="content" style="overflow-x: hidden">
                <button style="float:right" id="print" onclick="print();">Printer Friendly Version</button>
                <form id="filter" method="POST">
                    <table class="standard_table" >
                        <tr>
                            <td ><h3>Filters</h3></td>
                            <td style="text-align: right;"><input type="image" src="../images/filter.png" alt="filter"/></td>
                        </tr>
                        <tr>
                            <td>Campaign</td>
                            <td>
                                <select name="bt_campaign" form="filter" onchange="this.form.submit()">
                                    <option value="All">All</option>
                                    <?php
                                        //connect to database
                                        include "../spt_config/mysql_config.php";

                                        //get all the campaign names
                                        $r = mysql_query("SELECT id,campaign_name FROM campaigns");
                                        while($ra = mysql_fetch_assoc ( $r)){
                                            if(isset($_REQUEST['bt_campaign']) && $_REQUEST['bt_campaign'] == $ra['id']){
                                                echo "<option value=\"".$ra['id']."\" selected >".$ra['campaign_name']."</option>";
                                            } else{
                                                echo "<option value=\"".$ra['id']."\">".$ra['campaign_name']."</option>";
                                            }
                                        }
                                    ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>Browser</td>
                            <td>
                                <select name="bt_browser" form="filter" onchange="this.form.submit()">
                                    <option value="All">All</option>
                                    <?php
                                        //connect to database
                                        include "../spt_config/mysql_config.php";

                                        //get all the browsers
                                        $r = mysql_query("SELECT DISTINCT(browser) as browser FROM campaigns_responses WHERE browser IS NOT NULL");
                                        while($ra = mysql_fetch_assoc ( $r)){
                                            if(isset($_REQUEST['bt_browser']) && $_REQUEST['bt_browser'] == $ra['browser']){
                                                echo "<option value=\"".$ra['browser']."\" selected>".$ra['browser']."</option>";
                                            }else{
                                                echo "<option value=\"".$ra['browser']."\">".$ra['browser']."</option>";
                                            }
                                        }
                                    ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>Group</td>
                            <td>
                                <select name="bt_group" form="filter" onchange="this.form.submit()">
                                    <option value="All">All</option>
                                    <?php
                                        //connect to database
                                        include "../spt_config/mysql_config.php";

                                        //get all the groups
                                        $r = mysql_query("SELECT DISTINCT(group_name) FROM targets WHERE group_name IS NOT NULL");
                                        while($ra = mysql_fetch_assoc ( $r)){
                                            if(isset($_REQUEST['bt_group']) && $_REQUEST['bt_group'] == $ra['group_name']){
                                                echo "<option value=\"".$ra['group_name']."\" selected>".$ra['group_name']."</option>";
                                            }else{
                                                echo "<option value=\"".$ra['group_name']."\">".$ra['group_name']."</option>";
                                            }
                                        }
                                    ?>
                                </select>
                            </td>
                        </tr>
                    </table>
                </form>
                <div id="printable">
                    <div id="phish_pie_container"></div>
                    <div style="width:98%; zoom: 1; overflow: hidden">
                        <div id="link_educated_container" style="float:left; width:50%"></div>
                        <div id="post_educated_container" style="float: right; width:50%"></div>
                    </div>
                    <br>
                    <div id="bad_targets_container"></div>
                    <table id="good_targets_container" class="standard_table">
                        <thead>
                            <h1 align="center">Low Risk Targets(No Links Followed)</h1>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <h3>Name</h3>
                                </td>
                                <td>
                                    <h3>Group</h3>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <br>
                    <div id="timing_container"></div>
                </div>
                
            </div>
        </div>  
    </body>
</html>
