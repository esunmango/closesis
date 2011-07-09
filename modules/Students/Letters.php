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
include('../../Redirect_modules.php');

$extra['action'] .= "&_openSIS_PDF=true";
Widgets('mailing_labels');
Widgets('course');
Widgets('request');
Widgets('activity');
Widgets('absences');
Widgets('gpa');
Widgets('class_rank');
Widgets('letter_grade');
Widgets('eligibility');
$extra['force_search'] = true;

$extra['search'] .= '<TR><TD valign=top align=right>Letter Text</TD><TD><TEXTAREA name=letter_text rows=5 cols=40></TEXTAREA>';
if(!$_REQUEST['search_modfunc'] || $_openSIS['modules_search'])
{
	DrawBC("Students -> ".ProgramTitle());

	$extra['new'] = true;
	$extra['pdf'] = 'true';
	Search('student_id',$extra);
}
else
{
	$RET = GetStuList($extra);
	
	if(count($RET))
	{
		$_REQUEST['letter_text'] = nl2br(str_replace("\'","'",str_replace('  ',' &nbsp;',$_REQUEST['letter_text'])));

		$handle = PDFStart();
			
		foreach($RET as $student)
		{
			$student_points = $total_points = 0;
			unset($_openSIS['DrawHeader']);

			if($_REQUEST['mailing_labels']=='Y')
			{
			echo "<tr><td colspan=2 style=\"height:18px\"></td></tr>";
			echo "<table width=100%  style=\" font-family:Arial; font-size:12px;\" >";
			echo "<tr><td  style=\"font-size:15px; font-weight:bold; padding-top:20px;\">". GetSchool(UserSchool())."<div style=\"font-size:12px;\">Student Letter</div></td><td align=right style=\"padding-top:20px;\">". ProperDate(DBDate()) ."<br />Powered by openSIS</td></tr><tr><td colspan=2 style=\"border-top:1px solid #333;\">&nbsp;</td></tr></table>";			echo '<table border=0 style=\" font-family:Arial; font-size:12px;\">';
			echo '<tr>';
			echo '<td>'.$student['FULL_NAME'].', #'.$student['STUDENT_ID'].'</td></tr>';
			echo '<tr>';
			echo '<td>'.$student['GRADE_ID'].' Grade</td></tr>';
			echo '<tr>';
			echo '<td>Course: '.$course_title . "". GetMP(GetCurrentMP('QTR',DBDate())).'</td></tr>';
			if($student['MAILING_LABEL'] !='')
			{
			echo '<tr>';
			echo '<td >'.$student['MAILING_LABEL'].'</td></tr>';
			}
			#echo '</table>';

			if($_REQUEST['mailing_labels']=='Y')
			$letter_text = $_REQUEST['letter_text'];
			foreach($student as $column=>$value)
				$letter_text = str_replace('__'.$column.'__',$value,$letter_text);
				echo "<tr><td style=\"height:18px\"></td></tr>";
				echo '<tr><td>'.$letter_text.'</td></tr>';
				echo "<tr><td colspan=2 style=\"height:18px;\">&nbsp;</td></tr>";
				echo "</table>";
				echo "<div style=\"page-break-before: always;\"></div>";
		}
		else
		{
		unset($_openSIS['DrawHeader']);
		
	        echo "<tr><td colspan=2 style=\"height:18px\"></td></tr>";
			echo "<table width=100%  style=\" font-family:Arial; font-size:12px;\" >";
			echo "<tr><td  style=\"font-size:15px; font-weight:bold; padding-top:20px;\">". GetSchool(UserSchool())."<div style=\"font-size:12px;\">Student Letter</div></td><td align=right style=\"padding-top:20px;\">". ProperDate(DBDate()) ."<br \>Powered by openSIS</td></tr><tr><td colspan=2 style=\"border-top:1px solid #333;\">&nbsp;</td></tr></table>";
			echo '<table border=0 style=\" font-family:Arial; font-size:12px;\">';
			echo '<tr>';
			echo '<td>'.$student['FULL_NAME'].', #'.$student['STUDENT_ID'].'</td></tr>';
			echo '<tr>';
			echo '<td>'.$student['GRADE_ID'].' Grade</td></tr>';
			echo '<tr>';
			echo '<td>Course: '.$course_title . "". GetMP(GetCurrentMP('QTR',DBDate())).'</td></tr>';
			echo '</table>';
			echo '<br>';
			$letter_text = $_REQUEST['letter_text'];
			foreach($student as $column=>$value)
				$letter_text = str_replace('__'.$column.'__',$value,$letter_text);
				echo "<tr><td colspan=2 style=\"height:18px\"></td></tr>";
				echo '<tr><td>'.$letter_text.'</td></tr>';
				echo "<tr><td colspan=2 style=\"height:18px;\">&nbsp;</td></tr>";
				echo "</table>";
				echo "<div style=\"page-break-before: always;\"></div>";

		}
		}
		PDFStop($handle);
	}
	else
		BackPrompt('No Students were found.');
}
?>