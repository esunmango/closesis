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
function ListOutputFloat($result,$column_names,$singular='',$plural='',$link=false,$group=false,$options=false)
{
	if(!isset($options['save']))
		$options['save'] = true;
	if(!isset($options['print']))
		$options['print'] = true;
	if(!isset($options['search']))
		$options['search'] = true;
	if(!isset($options['center']))
		$options['center'] = true;
	if(!isset($options['count']))
		$options['count'] = true;
	if(!isset($options['sort']))
		$options['sort'] = true;
	if(!$link)
		$link = array();

	if(!isset($options['add']))
	{
		if(!AllowEdit() || $_REQUEST['_openSIS_PDF'])
		{
			if($link)
			{
				unset($link['add']);
				unset($link['remove']);
			}
		}
	}

	// PREPARE LINKS ---
	$result_count = $display_count = count($result);
	$num_displayed = 100000;
	$extra = "page=$_REQUEST[page]&LO_sort=$_REQUEST[LO_sort]&LO_direction=$_REQUEST[LO_direction]&LO_search=".urlencode($_REQUEST['LO_search']);

	$tmp_REQUEST = $_REQUEST;
	unset($tmp_REQUEST['page']);
	unset($tmp_REQUEST['LO_sort']);
	unset($tmp_REQUEST['LO_direction']);
	unset($tmp_REQUEST['LO_search']);
	unset($tmp_REQUEST['remove_prompt']);
	unset($tmp_REQUEST['remove_name']);
	unset($tmp_REQUEST['LO_save']);
	unset($tmp_REQUEST['PHPSESSID']);

	$PHP_tmp_SELF = PreparePHP_SELF($tmp_REQUEST);

	// END PREPARE LINKS ---

	// UN-GROUPING
	$group_count = count($group);
	if(!is_array($group))
		$group_count = false;

	//$side_color = Preferences('COLOR');
	
	$side_color = '#dfe8ee';

	if($group_count && $result_count)
	{
		$color = '#f5f5f5';
		$group_result = $result;
		unset($result);
		$result[0] = '';

		foreach($group_result as $item1)
		{
			if($group_count==1)
			{
				if($color=='#F8F8F9')
					$color = $side_color;
				else
					$color = '#F8F8F9';
			}

			foreach($item1 as $item2)
			{
				if($group_count==1)
				{
					$i++;
					if(count($group[0]) && $i!=1)
					{
						foreach($group[0] as $column)
							$item2[$column] = str_replace('<!-- <!--','<!--','<!-- '.str_replace('-->','--><!--',$item2[$column])).' -->';
					}
					$item2['row_color'] = $color;
					$result[] = $item2;
				}
				else
				{
					if($group_count==2)
					{
						if($color=='#F8F8F9')
							$color = $side_color;
						else
							$color = '#F8F8F9';
					}

					foreach($item2 as $item3)
					{
						if($group_count==2)
						{
							$i++;
							if(count($group[0]) && $i!=1)
							{
								foreach($group[0] as $column)
									$item3[$column] = '<!-- '.$item3[$column].' -->';
							}
							if(count($group[1]) && $i!=1)
							{
								foreach($group[1] as $column)
									$item3[$column] = '<!-- '.$item3[$column].' -->';
							}
							$item3['row_color'] = $color;
							$result[] = $item3;
						}
						else
						{
							if($group_count==3)
							{
								if($color=='#F8F8F9')
									$color = $side_color;
								else
									$color = '#F8F8F9';
							}

							foreach($item3 as $item4)
							{
								if($group_count==3)
								{
									$i++;
									if(count($group[2]) && $i!=1)
									{
										foreach($group[2] as $column)
											unset($item4[$column]);
									}
									$item4['row_color'] = $color;
									$result[] = $item4;
								}
							}
						}
					}
				}
			}
			$i = 0;
		}
		unset($result[0]);
		$result_count = count($result);

		unset($_REQUEST['LO_sort']);
	}
	// END UN-GROUPING
	$_LIST['output'] = true;


	// PRINT HEADINGS, PREPARE PDF, AND SORT THE LIST ---
	if($_LIST['output']!=false)
	{
		if($result_count != 0)
		{
			$count = 0;
			$remove = count($link['remove']['variables']);
			$cols = count($column_names);

			// HANDLE SEARCHES ---
			if($result_count && $_REQUEST['LO_search'] && $_REQUEST['LO_search']!='Search')
			{
				$_REQUEST['LO_search'] = $search_term = str_replace('\\\"','"',$_REQUEST['LO_search']);
				$_REQUEST['LO_search'] = $search_term = ereg_replace('[^a-zA-Z0-9 _"]*','',strtolower($search_term));

				if(substr($search_term,0,0)!='"' && substr($search_term,-1)!='"')
				{
					$search_term = ereg_replace('"','',$search_term);
					while($space_pos = strpos($search_term,' '))
					{
						$terms[strtolower(substr($search_term,0,$space_pos))] = 1;
						$search_term = substr($search_term,($space_pos+1));
					}
					$terms[trim($search_term)] = 1;
				}
				else
				{
					$search_term = ereg_replace('"','',$search_term);
					$terms[trim($search_term)] = 1;
				}

				unset($terms['of']); unset($terms['the']); unset($terms['a']); unset($terms['an']); unset($terms['in']);

				foreach($result as $key=>$value)
				{
					$values[$key] = 0;
					foreach($value as $name=>$val)
					{
						$val = ereg_replace('[^a-zA-Z0-9 _]+','',strtolower($val));
						if(strtolower($_REQUEST['LO_search'])==$val)
							$values[$key] += 25;
						foreach($terms as $term=>$one)
						{
							if(ereg($term,$val))
								$values[$key] += 3;
						}
					}
					if($values[$key]==0)
					{
						unset($values[$key]);
						unset($result[$key]);
						$result_count--;
						$display_count--;
					}
				}
				if($result_count)
				{
					array_multisort($values,SORT_DESC,$result);
					$result = ReindexResults($result);
					$values = ReindexResults($values);

					$last_value = 1;
					$scale = (100/$values[$last_value]);

					for($i=$last_value;$i<=$result_count;$i++)
						$result[$i]['RELEVANCE'] = '<!--' . ((int) ($values[$i]*$scale)) . '--><IMG SRC="assets/pixel_grey.gif" width=' . ((int) ($values[$i]*$scale)) . ' height=10>';
				}
				$column_names['RELEVANCE'] = "Relevance";

				if(is_array($group) && count($group))
				{
					$options['count'] == false;
					$display_zero = true;
				}
			}

			// END SEARCHES ---

			if($_REQUEST['LO_sort'])
			{
				foreach($result as $sort)
				{
					if(substr($sort[$_REQUEST['LO_sort']],0,4)!='<!--')
						$sort_array[] = $sort[$_REQUEST['LO_sort']];
					else
						$sort_array[] = substr($sort[$_REQUEST['LO_sort']],4,strpos($sort[$_REQUEST['LO_sort']],'-->')-5);
				}
				if($_REQUEST['LO_direction']==-1)
					$dir = SORT_DESC;
				else
					$dir = SORT_ASC;

				if($result_count>1)
				{
					if(is_int($sort_array[1]) || is_double($sort_array[1]))
						array_multisort($sort_array,$dir,SORT_NUMERIC,$result);
					else
						array_multisort($sort_array,$dir,$result);
					for($i=$result_count-1;$i>=0;$i--)
						$result[$i+1] = $result[$i];
					unset($result[0]);
				}
			}
		}
		// HANDLE SAVING THE LIST ---

		if($_REQUEST['LO_save']=='1')
		{
			if(!$options['save_delimiter'] && Preferences('DELIMITER')=='CSV')
				$options['save_delimiter'] = 'comma';
			switch($options['save_delimiter'])
			{
				case 'comma':
					$extension = 'csv';
				break;
				case 'xml':
					$extension = 'xml';
				break;
				default:
					$extension = 'xls';
				break;
			}
			ob_end_clean();
			if($options['save_delimiter']!='xml')
			{
				foreach($column_names as $key=>$value)
					$output .= str_replace('&nbsp;',' ',eregi_replace('<BR>',' ',ereg_replace('<!--.*-->','',$value))) . ($options['save_delimiter']=='comma'?',':"\t");
				$output .= "\n";
			}
			foreach($result as $item)
			{
				foreach($column_names as $key=>$value)
				{
					if($options['save_delimiter']=='comma' && !$options['save_quotes'])
						$item[$key] = str_replace(',',';',$item[$key]);
					$item[$key] = eregi_replace('<SELECT.*SELECTED\>([^<]+)<.*</SELECT\>','\\1',$item[$key]);
					$item[$key] = eregi_replace('<SELECT.*</SELECT\>','',$item[$key]);
					$output .= ($options['save_quotes']?'"':'') . ($options['save_delimiter']=='xml'?'<'.str_replace(' ','',$value).'>':'') . ereg_replace('<[^>]+>','',ereg_replace("<div onclick='[^']+'>",'',ereg_replace(' +',' ',ereg_replace('&[^;]+;','',str_replace('<BR>&middot;',' : ',str_replace('&nbsp;',' ',$item[$key])))))) . ($options['save_delimiter']=='xml'?'</'.str_replace(' ','',$value).'>'."\n":'') . ($options['save_quotes']?'"':'') . ($options['save_delimiter']=='comma'?',':"\t");
				}
				$output .= "\n";
			}
			header("Cache-Control: public");
			header("Pragma: ");
			header("Content-Type: application/$extension");
			header("Content-Disposition: inline; filename=\"".ProgramTitle().".$extension\"\n");
			if($options['save_eval'])
				eval($options['save_eval']);
			echo $output;
			exit();
		}
		// END SAVING THE LIST ---
		if($options['center'])
			echo '<CENTER>';

		if(($result_count > $num_displayed) || (($options['count'] || $display_zero) && ((($result_count==0 || $display_count==0) && $plural) || ($result_count==0 || $display_count==0))))
		{
			echo "<TABLE width=100% border=0 cellspacing=1" ;
			if(isset($_REQUEST['_openSIS_PDF']))
			//	echo " width=100%";
			echo "><TR><TD align=center>";
		}

		if($options['count'] || $display_zero)
		{
			if(($result_count==0 || $display_count==0) && $plural)
				echo "<table class=alert_center><tr><td class=alert_center_padding><b>No $plural were found.</b></td></tr></table>";
			elseif($result_count==0 || $display_count==0)
				echo '<table class=alert_center><tr><td class=alert_center_padding><b>None were found.</b></td></tr></table>';
		}
		if($result_count != 0 || ($_REQUEST['LO_search'] && $_REQUEST['LO_search']!='Search'))
		{
			if(!isset($_REQUEST['_openSIS_PDF']))
			{
				if(!$_REQUEST['page'])
					$_REQUEST['page'] = 1;
				if(!$_REQUEST['LO_direction'])
					$_REQUEST['LO_direction'] = 1;
				$start = ($_REQUEST['page'] - 1) * $num_displayed + 1;
				$stop = $start + ($num_displayed-1);
				if($stop > $result_count)
					$stop = $result_count;

				if($result_count > $num_displayed)
				{
					$where_message = "<SMALL>Displaying $start through $stop</SMALL>";
					echo "Go to Page ";
					if(ceil($result_count/$num_displayed) <= 10)
					{
						for($i=1;$i<=ceil($result_count/$num_displayed);$i++)
						{
							if($i!=$_REQUEST['page'])
								$pages .= "<A HREF=$PHP_tmp_SELF&LO_sort=$_REQUEST[LO_sort]&LO_direction=$_REQUEST[LO_direction]&LO_search=".urlencode($_REQUEST['LO_search'])."&page=$i>$i</A>, ";
							else
								$pages .= "$i, ";
						}
						$pages = substr($pages,0,-2) . "<BR>";
					}
					else
					{
						for($i=1;$i<=7;$i++)
						{
							if($i!=$_REQUEST['page'])
								$pages .= "<A HREF=$PHP_tmp_SELF&LO_sort=$_REQUEST[LO_sort]&LO_direction=$_REQUEST[LO_direction]&LO_search=".urlencode($_REQUEST['LO_search'])."&page=$i>$i</A>, ";
							else
								$pages .= "$i, ";
						}
						$pages = substr($pages,0,-2) . " ... ";
						for($i=ceil($result_count/$num_displayed)-2;$i<=ceil($result_count/$num_displayed);$i++)
						{
							if($i!=$_REQUEST['page'])
								$pages .= "<A HREF=$PHP_tmp_SELF&LO_sort=$_REQUEST[LO_sort]&LO_direction=$_REQUEST[LO_direction]&LO_search=".urlencode($_REQUEST['LO_search'])."&page=$i>$i</A>, ";
							else
								$pages .= "$i, ";
						}
						$pages = substr($pages,0,-2) . " &nbsp;<A HREF=$PHP_tmp_SELF&LO_sort=$_REQUEST[LO_sort]&LO_direction=$_REQUEST[LO_direction]&LO_search=".urlencode($_REQUEST['LO_search'])."&page=" . ($_REQUEST['page'] +1) . ">Next Page</A><BR>";
					}
					echo $pages;
					echo '</TD></TR></TABLE>';
					echo '<BR>';
				}
			}
			else
			{
				$start = 1;
				$stop = $result_count;
				if($cols>8 || $_REQUEST['expanded_view'])
				{
					$_SESSION['orientation'] = 'landscape';
					$repeat_headers = 17;
				}
				else
					$repeat_headers = 28;
				if($options['print'])
				{
					$html = explode('<!-- new page -->',strtolower(ob_get_contents()));
					$html = $html[count($html)-1];
					echo '</TD></TR></TABLE>';
					$br = (substr_count($html,'<br>')) + (substr_count($html,'</p>')) + (substr_count($html,'</tr>')) + (substr_count($html,'</h1>')) + (substr_count($html,'</h2>')) + (substr_count($html,'</h3>')) + (substr_count($html,'</h4>')) + (substr_count($html,'</h5>'));
					if($br%2!=0)
					{
						$br++;
						echo '<BR>';
					}
				}
				else
					echo '</TD></TR></TABLE>';
			}
			// END MISC ---

			// WIDTH = 100%
			echo '<TABLE class=grid cellspacing=0 cellpadding=0  style="border-left:1px solid #787878; border-top:1px solid #787878;">';

			// SEARCH BOX & MORE HEADERS
			if($where_message || ($singular && $plural) || (!isset($_REQUEST['_openSIS_PDF']) && $options['search']))
			{
			/*
				echo '<TD width=100% >';
				echo '<TABLE class=\"grid\" cellpadding=1 >';
				echo "<TR><TD align=left>";
				if($singular && $plural && $options['count'])
				{
					if($display_count > 1)
						echo "<b>$display_count $plural were found.</b> &nbsp; &nbsp;";
					elseif($display_count==1)
						echo "<b>1 $singular was found.</b> &nbsp; &nbsp;";
					if($where_message)
						echo '<BR>'.$where_message;
						
				}
				if($options['save'] && !isset($_REQUEST['_openSIS_PDF']) && $result_count > 0)
					echo "<A HREF=$PHP_tmp_SELF&$extra&LO_save=1&_openSIS_PDF=true><IMG SRC=assets/download.gif border=0 vspace=0 hspace=0></A>";
				echo '</TD>';
				$colspan = 1;
				if(!isset($_REQUEST['_openSIS_PDF']) && $options['search'])
				{
					$tmp_REQUEST = $_REQUEST;
					unset($tmp_REQUEST['LO_search']);
					unset($tmp_REQUEST['page']);
					echo '<TD align=right>';
					echo "<INPUT type=text id=LO_search name=LO_search value='".(($_REQUEST['LO_search'] && $_REQUEST['LO_search']!='Search')?$_REQUEST['LO_search']:'Search\' style=\'color:BBBBBB\''),"' onfocus='if(this.value==\"Search\") this.value=\"\"; this.style.color=\"000000\";' onblur='if(this.value==\"\") {this.value=\"Search\"; this.style.color=\"BBBBBB\";}' onkeypress='if(event.keyCode==13){document.location.href=\"".PreparePHP_SELF($tmp_REQUEST)."&LO_search=\"+this.value; return false;} '><INPUT type=button value=Go class=btn_go onclick='document.location.href=\"".PreparePHP_SELF($tmp_REQUEST)."&LO_search=\"+document.getElementById(\"LO_search\").value;'></TD>";
					$colspan++;
					
				}
				echo "</TR>";
				echo '<TR style="height:0;"><TD width=100% style="height:0;" height=0 align=right colspan='.$colspan.'><DIV id=LOx'.(count($column_names)+(($result_count!=0 && $cols && !isset($_REQUEST['_openSIS_PDF']))?1:0)+(($remove && !isset($_REQUEST['_openSIS_PDF']))?1:0)).' style="width:0; position: relative; height:0;"></DIV></TD></TR></TABLE>';
				*/
			}
			else
				echo '<TR><TD width=100% align=right><DIV id=LOx'.(count($column_names)+(($result_count!=0 && $cols && !isset($_REQUEST['_openSIS_PDF']))?1:0)+(($remove && !isset($_REQUEST['_openSIS_PDF']))?1:0)).' style="width:0; position: relative; height:0;"></DIV></TD></TR>';
			// END SEARCH BOX ----
			echo '<TR><TD>';

			// SHADOW
			if(!isset($_REQUEST['_openSIS_PDF']))
				echo '<TABLE cellpadding=0 cellspacing=0 width=100%><TR><TD>';
			echo "<TABLE class=\"grid\" cellpadding=6 cellspacing=0 width=100%>";
			if(!isset($_REQUEST['_openSIS_PDF']) && ($stop-$start)>10)
				echo '<THEAD>';
			if(!isset($_REQUEST['_openSIS_PDF']))
				echo '<TR>';

			$i =1;
			if($remove && !isset($_REQUEST['_openSIS_PDF']) && $result_count!=0)
			{
				echo "<TD class=\"subtabs\"><DIV id=LOx$i style='position: relative;'></DIV></TD>";
				$i++;
			}

			if($result_count!=0 && $cols && !isset($_REQUEST['_openSIS_PDF']))
			{
				foreach($column_names as $key=>$value)
				{
					if($_REQUEST['LO_sort'] == $key)
						$direction = -1 * $_REQUEST['LO_direction'];
					else
						$direction = 1;
					echo "<TD class=\"subtabs\"><DIV id=LOx$i style='position: relative;'></DIV>";
					echo "<A ";
					if($options['sort'])
						echo "HREF=$PHP_tmp_SELF&page=$_REQUEST[page]&LO_sort=$key&LO_direction=$direction&LO_search=".urlencode($_REQUEST['LO_search']);
					echo " class=column_heading><b>$value</b></A>";
					if($i==1)
						echo "<DIV id=LOy0 style='position: relative;'></DIV>";
					echo "</TD>";
					$i++;
				}
				//echo '<TD width=0><DIV id=LO'.$i.'></DIV></TD>';
				echo "</TR>";
			}

			$color = '#F8F8F9';
			//style="height: 300px; overflow: auto; padding-right: 16px;"
			if(!isset($_REQUEST['_openSIS_PDF']) && ($stop-$start)>10)
				echo '</THEAD><TBODY>';


			// mab - enable add link as first or last
			if($result_count!=0 && $link['add']['first'] && ($stop-$start)>=$link['add']['first'])
			{
				//if($remove && !isset($_REQUEST['_openSIS_PDF']))
				//	$cols++;
				if($link['add']['link'] && !isset($_REQUEST['_openSIS_PDF']))
					echo "<TR><TD colspan=".($remove?$cols+1:$cols)." align=left >".button('add',$link['add']['title'],$link['add']['link'])."</TD></TR>";
				elseif($link['add']['span'] && !isset($_REQUEST['_openSIS_PDF']))
					echo "<TR><TD colspan=".($remove?$cols+1:$cols)." align=left >".button('add').$link['add']['span']."</TD></TR>";
				elseif($link['add']['html'] && $cols)
				{
					echo "<TR bgcolor=$color>";
					if($remove && !isset($_REQUEST['_openSIS_PDF']) && $link['add']['html']['remove'])
						echo "<TD >".$link['add']['html']['remove']."</TD>";
					elseif($remove && !isset($_REQUEST['_openSIS_PDF']))
						echo "<TD >".button('add')."</TD>";

					foreach($column_names as $key=>$value)
					{
						echo "<TD >".$link['add']['html'][$key]."</TD>";
					}
					echo "</TR>";
					$count++;
				}
			}


			for($i=$start;$i<=$stop;$i++)
			{
				$item = $result[$i];
				if(isset($_REQUEST['_openSIS_PDF']) && $options['print'] && count($item))
				{
					foreach($item as $key=>$value)
					{
						$value = eregi_replace('<SELECT.*SELECTED\>([^<]+)<.*</SELECT\>','\\1',$value);
						$value = eregi_replace('<SELECT.*</SELECT\>','',$value);

						if(strpos($value,'LO_field')===false)
							$item[$key] = str_replace(' ','&nbsp;',ereg_replace("<div onclick='[^']+'>",'',$value));
						else
							$item[$key] = ereg_replace("<div onclick='[^']+'>",'',$value);
					}
				}

				if($item['row_color'])
					$color = $item['row_color'];
				elseif($color=='#F8F8F9')
					$color = $side_color;
				else
					$color = '#F8F8F9';
					//$color = '#EDF3FE';
				if(isset($_REQUEST['_openSIS_PDF']) && $count%$repeat_headers==0)
				{
					if($count!=0)
					{
						echo '</TABLE><TABLE class=\"grid\" cellspacing=1 cellpadding=6 >';
						echo '<!-- NEW PAGE -->';
					}
					echo "<TR>";
					if($remove && !isset($_REQUEST['_openSIS_PDF']))
						echo "<TD class=\"subtabs\"></TD>";

					if($cols)
					{
						foreach($column_names as $key=>$value)
						{
							echo "<TD class=\"subtabs\" class=LO_field><b>" . str_replace(' ','&nbsp;',$value) . "</b></FONT></TD>";
						}
					}
					echo "</TR>";
				}
				if($count==0)
					$count = $br;

				echo "<TR bgcolor=$color>";
				$count++;
				if($remove && !isset($_REQUEST['_openSIS_PDF']))
				{
					$button_title = $link['remove']['title'];
					//if(!$button_title)
						//$button_title = 'Remove';
					$button_link = $link['remove']['link'];
					if(count($link['remove']['variables']))
					{
						foreach($link['remove']['variables'] as $var=>$val)
							$button_link .= "&$var=" . urlencode($item[$val]);
					}

					echo "<TD bgcolor=$color>" . button('remove',$button_title,$button_link) . "</TD>";
				}
				if($cols)
				{
					foreach($column_names as $key=>$value)
					{
						if($link[$key] && !isset($_REQUEST['_openSIS_PDF']))
						{
							echo "<TD bgcolor=$color class=LO_field>";
							if($key=='FULL_NAME')
								echo '<DIV id=LOy'.($count-$br).' height=20 style="height: 20; min-height: 20; position: relative;">';
							if($link[$key]['js']===true)
							{
								echo "<A HREF=# onclick='window.open(\"{$link[$key][link]}";
								if(count($link[$key]['variables']))
								{
									foreach($link[$key]['variables'] as $var=>$val)
										echo "&$var=".urlencode($item[$val]);
								}
								echo "\",\"\",\"scrollbars=yes,resizable=yes,width=800,height=400\");'";
								if($link[$key]['extra'])
									echo ' '.$link[$key]['extra'];
								echo ">";
							}
							else
							{
								echo "<A HREF={$link[$key][link]}";
								if(count($link[$key]['variables']))
								{
									foreach($link[$key]['variables'] as $var=>$val)
										echo "&$var=".urlencode($item[$val]);
								}
								if($link[$key]['extra'])
									echo ' '.$link[$key]['extra'];
								echo ">";
							}
							if($color==Preferences('HIGHLIGHT'))
								echo '';
							else
								echo '';
							echo $item[$key];
							echo '';
							if(!$item[$key])
								echo '***';
							echo "</A>";
							if($key=='FULL_NAME')
								echo '</DIV>';
							echo "</TD>";
						}
						else
						{
							echo "<TD bgcolor=$color class=LO_field>";
							if($key=='FULL_NAME')
								echo '<DIV id=LOy'.($count-$br).' height=20 style="position: relative;">';
							if($color==Preferences('HIGHLIGHT'))
								echo '';
							echo $item[$key];
							if(!$item[$key])
								echo '&nbsp;';
							if($key=='FULL_NAME')
								echo '<DIV>';
							echo "</TD>";
						}
					}
				}
				echo "</TR>";
			}

			if($result_count!=0 && (!$link['add']['first'] || $link['add']['first'] && ($stop-$start)<$link['add']['first']))
			{
				//if($remove && !isset($_REQUEST['_openSIS_PDF']))
				//	$cols++;
				if($link['add']['link'] && !isset($_REQUEST['_openSIS_PDF']))
					echo "<TR><TD colspan=".($remove?$cols+1:$cols)." align=left bgcolor=#FFFFFF>".button('add',$link['add']['title'],$link['add']['link'])."</TD></TR>";
				elseif($link['add']['span'] && !isset($_REQUEST['_openSIS_PDF']))
					echo "<TR><TD colspan=".($remove?$cols+1:$cols)." align=left bgcolor=#FFFFFF>".button('add').$link['add']['span']."</TD></TR>";
				elseif($link['add']['html'] && $cols)
				{
					if($count%2)
						$color = '#F8F8F9';
					else
						$color = $side_color;

					echo "<TR bgcolor=$color>";
					if($remove && !isset($_REQUEST['_openSIS_PDF']) && $link['add']['html']['remove'])
						echo "<TD bgcolor=$color>".$link['add']['html']['remove']."</TD>";
					elseif($remove && !isset($_REQUEST['_openSIS_PDF']))
						echo "<TD bgcolor=$color>".button('add')."</TD>";

					foreach($column_names as $key=>$value)
					{
						echo "<TD bgcolor=$color class=LO_field>".$link['add']['html'][$key]."</TD>";
					}
					echo "</TR>";
				}
			}
			if($result_count!=0)
			{
				if(!isset($_REQUEST['_openSIS_PDF']) && ($stop-$start)>10)
					echo '</TBODY>';
				echo "</TABLE>";
				// SHADOW
				if(!isset($_REQUEST['_openSIS_PDF']))
					echo '</TD></TR></TABLE>';
				echo "</TD></TR>";
				echo "</TABLE>";

				if($options['center'])
					echo '</CENTER>';
			}

		// END PRINT THE LIST ---
		}
		if($result_count==0)
		{
			// mab - problem with table closing if not opened above - do same conditional?
			if(($result_count > $num_displayed) || (($options['count'] || $display_zero) && ((($result_count==0 || $display_count==0) && $plural) || ($result_count==0 || $display_count==0))))
				echo '</TD></TR></TABLE>';
			if($link['add']['link'] && !isset($_REQUEST['_openSIS_PDF']))
				echo '<div class=break style=width:300px></div><center>' . button('add',$link['add']['title'],$link['add']['link']) . '</center>';
			elseif(($link['add']['html'] || $link['add']['span']) && count($column_names) && !isset($_REQUEST['_openSIS_PDF']))
			{
				$color = $side_color;

				if($options['center'])
					echo '<CENTER>';
				// WIDTH=100%
				//echo "<TABLE cellpadding=1 bgcolor=#f8f8f9 width=100%><TR><TD>";
				// SHADOW
				echo '<TABLE class=\"grid\" cellpadding=0 cellspacing=0><TR><TD>';
				if($link['add']['html'])
				{
				 	/*Here also change the colour for left corner*/
					echo "<TABLE class=\"grid\" cellspacing=1 cellpadding=6 ><TR>";
					foreach($column_names as $key=>$value)
					{
					 //Here to change the ListOutput Header Colour
						echo "<TD ><A><b>" . str_replace(' ','&nbsp;',$value) . "</b></A></TD>";
					}
					echo "</TR>";

					echo "<TR bgcolor=$color>";

					if($link['add']['html']['remove'])
						echo "<TD bgcolor=$color>".$link['add']['html']['remove']."</TD>";
					else
						echo "<TD bgcolor=#F5F5F5>".button('add')."</TD>";

					foreach($column_names as $key=>$value)
					{
						echo "<TD bgcolor=#F5F5F5 class=LO_field>".$link['add']['html'][$key]."</TD>";
					}
					echo "</TR>";
					echo "</TABLE>";
				}
				elseif($link['add']['span'] && !isset($_REQUEST['_openSIS_PDF']))
					echo "<TABLE class=\"grid\" cellspacing=1><TR><TD align=left>".button('add').$link['add']['span']."</TD></TR></TABLE>";

				// SHADOW
				//echo '</TD></TR></TABLE>';
				echo "</TD></TR></TABLE>";
				if($options['center'])
					echo '</CENTER>';
			}
		}
		if($result_count!=0)
		{
			if($options['yscroll'])
			{
				echo '<div id="LOy_layer" style="position: absolute; top: 0; left: 0; visibility:hidden;">';
				echo '<TABLE class=\"grid\" cellspacing=1 cellpadding=6 id=LOy_table>';
				$i = 1;

				if($cols && !isset($_REQUEST['_openSIS_PDF']))
				{
					$color = $side_color;
					foreach($result as $item)
					{
						echo "<TR><TD bgcolor=$color class=LO_field id=LO_row$i>";
						if($color==Preferences('HIGHLIGHT'))
							echo '';
						echo $item['FULL_NAME'];
						if(!$item['FULL_NAME'])
							echo '&nbsp;';
						if($color==Preferences('HIGHLIGHT'))
							echo '';
						echo "</TD></TR>";
						$i++;

						if($item['row_color'])
							$color = $item['row_color'];
						elseif($color=='#F8F8F9')
							$color = $side_color;
						else
							$color = '#F8F8F9';
					}
				}
				echo '</TABLE>';
				echo '</div>';
			}

			echo '<div id="LOx_layer" style="position: absolute; top: 0; left: 0; visibility:hidden;">';
			echo '<TABLE class=\"grid\" cellspacing=1 cellpadding=6 id=LOx_table><TR>';
			$i = 1;
			if($remove && !isset($_REQUEST['_openSIS_PDF']) && $result_count!=0)
			{
				echo "<TD class=\"subtabs\" id=LO_col$i></TD>";
				$i++;
			}

			if($cols && !isset($_REQUEST['_openSIS_PDF']))
			{
				foreach($column_names as $key=>$value)
				{
					echo '<TD class=\"subtabs\" id=LO_col'.$i.'><A class=column_heading><b>'.str_replace('controller','',$value).'</b></A></TD>';
					$i++;
				}
			}
			echo '</TR></TABLE>';
			echo '</div>';
		}
	}
}
?>