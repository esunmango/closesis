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
#DrawHeader('Gradebook - '.ProgramTitle());
include('../../Redirect_modules.php');
$tmp_REQUEST = $_REQUEST;
unset($tmp_REQUEST['include_inactive']);

echo "<FORM action=Modules.php?modname=$_REQUEST[modname] method=POST>";
DrawHeaderHome('<INPUT type=checkbox name=include_inactive value=Y'.($_REQUEST['include_inactive']=='Y'?" CHECKED onclick='document.location.href=\"".PreparePHP_SELF($tmp_REQUEST)."&include_inactive=\";'":" onclick='document.location.href=\"".PreparePHP_SELF($tmp_REQUEST)."&include_inactive=Y\";'").'>Include Inactive Students');
echo '</FORM>';

$course_period_id = UserCoursePeriod();
$course_id = DBGet(DBQuery("SELECT COURSE_ID FROM COURSE_PERIODS WHERE COURSE_PERIOD_ID='$course_period_id'"));
$course_id = $course_id[1]['COURSE_ID'];

$max_allowed = Preferences('ANOMALOUS_MAX','Gradebook')/100;

$extra['SELECT'] = ",ga.ASSIGNMENT_ID,gt.TITLE AS TYPE_TITLE,ga.TITLE,ga.POINTS AS TOTAL_POINTS,'' AS LETTER_GRADE";
$extra['SELECT'] .= ',(SELECT POINTS FROM GRADEBOOK_GRADES WHERE STUDENT_ID=s.STUDENT_ID AND ASSIGNMENT_ID=ga.ASSIGNMENT_ID) AS POINTS';
$extra['SELECT'] .= ',(SELECT COMMENT FROM GRADEBOOK_GRADES WHERE STUDENT_ID=s.STUDENT_ID AND ASSIGNMENT_ID=ga.ASSIGNMENT_ID) AS COMMENT';
$extra['FROM'] = ",GRADEBOOK_ASSIGNMENTS ga,GRADEBOOK_ASSIGNMENT_TYPES gt";
$extra['WHERE'] = 'AND ((SELECT POINTS FROM GRADEBOOK_GRADES WHERE STUDENT_ID=s.STUDENT_ID AND ASSIGNMENT_ID=ga.ASSIGNMENT_ID) IS NULL AND (ga.ASSIGNED_DATE IS NULL OR CURRENT_DATE>=ga.ASSIGNED_DATE) AND (ga.DUE_DATE IS NULL OR CURRENT_DATE>=ga.DUE_DATE) OR (SELECT POINTS FROM GRADEBOOK_GRADES WHERE STUDENT_ID=s.STUDENT_ID AND ASSIGNMENT_ID=ga.ASSIGNMENT_ID)<0 OR (SELECT POINTS FROM GRADEBOOK_GRADES WHERE STUDENT_ID=s.STUDENT_ID AND ASSIGNMENT_ID=ga.ASSIGNMENT_ID)>ga.POINTS*'.$max_allowed.') AND ((ga.COURSE_ID=\''.$course_id.'\' AND ga.STAFF_ID=\''.User('STAFF_ID').'\') OR ga.COURSE_PERIOD_ID=\''.$course_period_id.'\') AND ga.MARKING_PERIOD_ID=\''.UserMP().'\' AND gt.ASSIGNMENT_TYPE_ID=ga.ASSIGNMENT_TYPE_ID';

$extra['functions'] = array('POINTS'=>'_makePoints');
$students_RET = GetStuList($extra);
//echo '<pre>'; var_dump($students_RET); echo '</pre>';

if(AllowUse('Grades/Grades.php'))
	$link = array('FULL_NAME'=>array('link'=>"Modules.php?modname=Grades/Grades.php&include_ianctive=$_REQUEST[include_inactive]&assignment_id=all",'variables'=>array('student_id'=>'STUDENT_ID')),'TITLE'=>array('link'=>"Modules.php?modname=Grades/Grades.php&include_inactive=$_REQUEST[include_inactive]",'variables'=>array('assignment_id'=>'ASSIGNMENT_ID','student_id'=>'STUDENT_ID')));
$columns = array('FULL_NAME'=>'Name','STUDENT_ID'=>'Student ID','POINTS'=>'Problem','TYPE_TITLE'=>'Category','TITLE'=>'Assignment','COMMENT'=>'Comment');
ListOutput($students_RET,$columns,'Anomalous Grade','Anomalous Grades',$link,array(),array('center'=>false,'save'=>false,'search'=>false));

function _makePoints($value,$column)
{	global $THIS_RET;

	if($value=='')
		return '<FONT class=red>Missing</FONT>';
	elseif($value=='-1')
		return '<FONT color=#00a000>Excused</FONT>';
	elseif($value<0)
		return '<FONT class=red>Negative!</FONT>';
	elseif($THIS_RET['TOTAL_POINTS']==0)
		return '<FONT color=#0000ff>Extra Credit</FONT>';
	return Percent($value/$THIS_RET['TOTAL_POINTS'],0);
}
?>