<?php	
#**************************************************************************
#  openSIS is a free student information system for public and non-public 
#  schools from Open Solutions for Education, Inc. It is  web-based, 
#  open source, and comes packed with features that include student 
#  demographic info, scheduling, grade book, attendance, 
#  report cards, eligibility, transcripts, parent portal, 
#  student portal and more.   
#
#  Visit the openSIS web site at http://www.opensis.com to learn more.
#  If you have question regarding this system or the license, please send 
#  an email to info@os4ed.com.
#
#  Copyright (C) 2007-2008, Open Solutions for Education, Inc.
#
#*************************************************************************
#  This program is free software: you can redistribute it and/or modify
#  it under the terms of the GNU General Public License as published by
#  the Free Software Foundation, version 2 of the License. See license.txt.
#
#  This program is distributed in the hope that it will be useful,
#  but WITHOUT ANY WARRANTY; without even the implied warranty of
#  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#  GNU General Public License for more details.
#
#  You should have received a copy of the GNU General Public License
#  along with this program.  If not, see <http://www.gnu.org/licenses/>.
#**************************************************************************
#UPDATE APP SET VALUE='4.3' WHERE NAME='version';
#UPDATE APP SET VALUE='04302009000' WHERE NAME='build';
include('../../Redirect_modules.php');
DrawBC("Tools >> ".ProgramTitle());

if(isset($_REQUEST['del']))	
{
	
	if($_REQUEST['day_start'] && $_REQUEST['month_start'] && $_REQUEST['year_start'])
	{
		$start_date = $_REQUEST['day_start'].'-'.$_REQUEST['month_start'].'-'.substr($_REQUEST['year_start'],2,4);
		$org_start_date = $_REQUEST['day_start'].'-'.$_REQUEST['month_start'].'-'.$_REQUEST['year_start'];
		
		$conv_st_date = con_date($org_start_date);
	}
	
	if($_REQUEST['day_end'] && $_REQUEST['month_end'] && $_REQUEST['year_end'])
	{
		$end_date = $_REQUEST['day_end'].'-'.$_REQUEST['month_end'].'-'.substr($_REQUEST['year_end'],2,4);
		$org_end_date = $_REQUEST['day_end'].'-'.$_REQUEST['month_end'].'-'.$_REQUEST['year_end'];
		
		$conv_end_date = con_date_end($org_end_date);
	}
	
	
	# ------------------------------- Deletion Of Log Records ----------------------------- #
	if(isset($conv_st_date) && isset($conv_end_date))
	{
		$sql_del = DBQuery("DELETE FROM LOGIN_RECORDS WHERE LOGIN_TIME >='".$conv_st_date."' AND LOGIN_TIME <='".$conv_end_date."' AND SYEAR='".UserSyear()."'");
		echo '<center><font color="red"><b>Log deleted successfully</b></font></center>';
	}
	
	if(isset($conv_st_date) && !isset($conv_end_date))
	{
		$sql_del = DBQuery("DELETE FROM LOGIN_RECORDS WHERE LOGIN_TIME >='".$conv_st_date."' AND SYEAR='".UserSyear()."'");
		echo '<center><font color="red"><b>Log deleted successfully</b></font></center>';
	}

	if(!isset($conv_st_date) && isset($conv_end_date))
	{
		$sql_del = DBQuery("DELETE FROM LOGIN_RECORDS WHERE LOGIN_TIME <='".$conv_end_date."' AND SYEAR='".UserSyear()."'");
		echo '<center><font color="red"><b>Log deleted successfully</b></font></center>';
	}
	
	if(!isset($conv_st_date) && !isset($conv_end_date))
	{
		echo '<center><font color="red"><b>You have to select atleast one date from the date range</b></font></center>';
		#$sql_del = DBQuery("DELETE FROM LOGIN_RECORDS WHERE SYEAR='".UserSyear()."'");
		#echo '<center><font color="red"><b>Log Deleted Successfully</b></font></center>';
	}
	# ------------------------------------------------------------------------------------- #
	
}	
	
	echo "<FORM name=del id=del action=Modules.php?modname=$_REQUEST[modname] method=POST>";
		PopTable ('header', 'Delete Log');
		
	echo '<div align=center style="padding-top:10px; font-size:14px;"><strong>Please Select Date Range</strong></div>
	<TABLE border=0 width=100% align=center><tr><TD valign=middle style=padding-top:25px;>';
		
		
	echo '<strong>From :</strong> </TD><TD valign=middle>';
	DrawHeader(PrepareDate($start_date,'_start'));
	echo '</TD><TD valign=middle style=padding-top:25px;><strong>To :</strong> </TD><TD valign=middle>';
	DrawHeader(PrepareDate($end_date,'_end'));

		
	echo '</TD></TR></TABLE><div style=height:10px></div>';
			
			echo '<center><input type="submit" class=btn_medium value="Delete" name="del"></center>';
		
		PopTable('footer');
	echo '</FORM>';
	
	
	
function con_date($date)
{
	$mother_date = $date;
	$year = substr($mother_date, 7);
	$temp_month = substr($mother_date, 3, 3);
	
		if($temp_month == 'JAN')
			$month = '01';
		elseif($temp_month == 'FEB')
			$month = '02';
		elseif($temp_month == 'MAR')
			$month = '03';
		elseif($temp_month == 'APR')
			$month = '04';
		elseif($temp_month == 'MAY')
			$month = '05';
		elseif($temp_month == 'JUN')
			$month = '06';
		elseif($temp_month == 'JUL')
			$month = '07';
		elseif($temp_month == 'AUG')
			$month = '08';
		elseif($temp_month == 'SEP')
			$month = '09';
		elseif($temp_month == 'OCT')
			$month = '10';
		elseif($temp_month == 'NOV')
			$month = '11';
		elseif($temp_month == 'DEC')
			$month = '12';
			
	$day = substr($mother_date, 0, 2);
	
	$select_date = $year.'-'.$month.'-'.$day.' '.'00:00:00';
	return $select_date;
}




function con_date_end($date)
{
	$mother_date = $date;
	$year = substr($mother_date, 7);
	$temp_month = substr($mother_date, 3, 3);
	
		if($temp_month == 'JAN')
			$month = '01';
		elseif($temp_month == 'FEB')
			$month = '02';
		elseif($temp_month == 'MAR')
			$month = '03';
		elseif($temp_month == 'APR')
			$month = '04';
		elseif($temp_month == 'MAY')
			$month = '05';
		elseif($temp_month == 'JUN')
			$month = '06';
		elseif($temp_month == 'JUL')
			$month = '07';
		elseif($temp_month == 'AUG')
			$month = '08';
		elseif($temp_month == 'SEP')
			$month = '09';
		elseif($temp_month == 'OCT')
			$month = '10';
		elseif($temp_month == 'NOV')
			$month = '11';
		elseif($temp_month == 'DEC')
			$month = '12';
			
	$day = substr($mother_date, 0, 2);
	
	$select_date = $year.'-'.$month.'-'.$day.' '.'23:59:59';
	return $select_date;
}


	
	
?>	