<?php
#**************************************************************************
#  openSIS is a free student information system for public and non-public 
#  schools from Open Solutions for Education, Inc. web: www.os4ed.com
#
#  openSIS is  web-based, open source, and comes packed with features that 
#  include student demographic info, scheduling, grade book, attendance, 
#  report cards, eligibility, transcripts, parent portal, 
#  student portal and more.   
#
#  Visit the openSIS web site at http://www.opensis.com to learn more.
#  If you have question regarding this system or the license, please send 
#  an email to info@os4ed.com.
#
#  This program is released under the terms of the GNU General Public License as  
#  published by the Free Software Foundation, version 2 of the License. 
#  See license.txt.
#
#  This program is distributed in the hope that it will be useful,
#  but WITHOUT ANY WARRANTY; without even the implied warranty of
#  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#  GNU General Public License for more details.
#
#  You should have received a copy of the GNU General Public License
#  along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
#***************************************************************************************

function UpdateAttendanceDaily($student_id,$date='',$comment=false)
{
	if(!$date)
		$date = DBDate();

	$sql = "SELECT
				sum(sp.LENGTH) AS TOTAL
			FROM SCHEDULE s,COURSE_PERIODS cp,SCHOOL_PERIODS sp,ATTENDANCE_CALENDAR ac
			WHERE
				s.COURSE_PERIOD_ID = cp.COURSE_PERIOD_ID AND cp.DOES_ATTENDANCE='Y'
				AND ac.SCHOOL_DATE='$date' AND (ac.BLOCK=sp.BLOCK OR sp.BLOCK IS NULL)
				AND ac.CALENDAR_ID=cp.CALENDAR_ID AND ac.SCHOOL_ID=s.SCHOOL_ID AND ac.SYEAR=s.SYEAR
				AND s.SYEAR = cp.SYEAR AND sp.PERIOD_ID = cp.PERIOD_ID
				AND position(substring('UMTWHFS' FROM DAYOFWEEK('$date')  FOR 1) IN cp.DAYS)>0
				AND s.STUDENT_ID='$student_id'
				AND s.SYEAR='".UserSyear()."'
				AND ('$date' BETWEEN s.START_DATE AND s.END_DATE OR (s.END_DATE IS NULL AND '$date'>=s.START_DATE))
				AND s.MARKING_PERIOD_ID IN (".GetAllMP('QTR',GetCurrentMP('QTR',$date)).")
			";
	$RET = DBGet(DBQuery($sql));
	$total = $RET[1]['TOTAL'];
	if($total==0)
		return;

	$sql = "SELECT sum(sp.LENGTH) AS TOTAL
			FROM ATTENDANCE_PERIOD ap,SCHOOL_PERIODS sp,ATTENDANCE_CODES ac
			WHERE ap.STUDENT_ID='$student_id' AND ap.SCHOOL_DATE='$date' AND ap.PERIOD_ID=sp.PERIOD_ID AND ac.ID = ap.ATTENDANCE_CODE AND ac.STATE_CODE='A'
			AND sp.SYEAR='".UserSyear()."'";
	$RET = DBGet(DBQuery($sql));
	$total -= $RET[1]['TOTAL'];

	$sql = "SELECT sum(sp.LENGTH) AS TOTAL
			FROM ATTENDANCE_PERIOD ap,SCHOOL_PERIODS sp,ATTENDANCE_CODES ac
			WHERE ap.STUDENT_ID='$student_id' AND ap.SCHOOL_DATE='$date' AND ap.PERIOD_ID=sp.PERIOD_ID AND ac.ID = ap.ATTENDANCE_CODE AND ac.STATE_CODE='H'
			AND sp.SYEAR='".UserSyear()."'";
	$RET = DBGet(DBQuery($sql));
	$total -= $RET[1]['TOTAL']*.5;

	/*
	if($total>=300)
		$length = '1.0';
	elseif($total>=150)
		$length = '.5';
	else
		$length = '0.0';
	
	*/

        if(stripos($_SERVER['SERVER_SOFTWARE'], 'linux')){
          $comment=  str_replace("'","\'",$comment);
        }
	$sys_pref = DBGet(DBQuery("SELECT * FROM SYSTEM_PREFERENCE WHERE SCHOOL_ID=".UserSchool()));
	$fdm = $sys_pref[1]['FULL_DAY_MINUTE'];
	$hdm = $sys_pref[1]['HALF_DAY_MINUTE'];

	if($total>=$fdm)
		$length = '1.0';
	elseif($total>=$hdm)
		$length = '.5';
	else
		$length = '0.0';

	$current_RET = DBGet(DBQuery("SELECT MINUTES_PRESENT,STATE_VALUE,COMMENT FROM ATTENDANCE_DAY WHERE STUDENT_ID='$student_id' AND SCHOOL_DATE='$date'"));
	if(count($current_RET) && $current_RET[1]['MINUTES_PRESENT']!=$total)
		DBQuery("UPDATE ATTENDANCE_DAY SET MINUTES_PRESENT='$total',STATE_VALUE='$length'".($comment!==false?",COMMENT='".str_replace("","",$comment)."'":'')." WHERE STUDENT_ID='$student_id' AND SCHOOL_DATE='$date'");
	elseif(count($current_RET) && $comment!==false && $current_RET[1]['COMMENT']!=$comment)
		DBQuery("UPDATE ATTENDANCE_DAY SET COMMENT='".str_replace("","",$comment)."' WHERE STUDENT_ID='$student_id' AND SCHOOL_DATE='$date'");
	elseif(count($current_RET)==0)
		DBQuery("INSERT INTO ATTENDANCE_DAY (SYEAR,STUDENT_ID,SCHOOL_DATE,MINUTES_PRESENT,STATE_VALUE,MARKING_PERIOD_ID,COMMENT) values('".UserSyear()."','$student_id','$date','$total','$length','".GetCurrentMP('QTR',$date)."','".str_replace("","",$comment)."')");
}

?>