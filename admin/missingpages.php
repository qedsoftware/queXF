<?

/*	Copyright Deakin University 2007,2008
 *	Written by Adam Zammit - adam.zammit@deakin.edu.au
 *	For the Deakin Computer Assisted Research Facility: http://www.deakin.edu.au/dcarf/
 *	
 *	This file is part of queXF
 *	
 *	queXF is free software; you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation; either version 2 of the License, or
 *	(at your option) any later version.
 *	
 *	queXF is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU General Public License for more details.
 *	
 *	You should have received a copy of the GNU General Public License
 *	along with queXF; if not, write to the Free Software
 *	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 */


//handle missing pages
//

include("../config.inc.php");

global $db;

if (isset($_GET['npid']) && isset($_GET['mpid']) && isset($_GET['fid']))
{
	$npid = $_GET['npid'];
	$fid = $_GET['fid'];
	$mpid = $_GET['mpid'];

	if (isset($_GET['delete']))
	{
		$sql = "DELETE 
		        FROM missingpages
			WHERE mpid = '$mpid'";

		$db->Execute($sql);
	}
	else if (isset($_GET['tryanother']))
	{

	}
	else if (isset($_GET['accept']))
	{
		$db->StartTrans();

		$sql = "INSERT INTO formpages
			(fid,pid,filename,image,offx,offy)
			SELECT '$fid','$npid','',image,'0','0'
			FROM missingpages where mpid = '$mpid'";

		$db->Execute($sql);

		$sql = "DELETE 
			FROM missingpages
			WHERE mpid = '$mpid'";

		$db->Execute($sql);

		$db->CompleteTrans();
	}
	
	

}


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
      <html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
<title>MISSING PAGES</title>
<style type="text/css">
#topper {
  position : fixed;
  width : 100%;
  height : 5%;
  top : 0;
  right : 0;
  bottom : auto;
  left : 0;
  border-bottom : 2px solid #cccccc;
  overflow : auto;
	text-align:center;
}

#bottom {
  position : fixed;
  width : 100%;
  height : 5%;
  top : 95%;
  right : 0;
  bottom : auto;
  left : 0;
  border-bottom : 2px solid #cccccc;
  overflow : auto;
	text-align:center;
}


#left {
  position : fixed;
  width : 50%;
  height : 90%;
  top : 5%;
  right : 0;
  bottom : auto;
  left : 0;
  border-bottom : 2px solid #cccccc;
  overflow : auto;
}
#right {
  position : fixed;
  top : 5%;
  left : 50%;
  bottom : auto;
  width : 50%;
  height : 90%;
  color : #000000;
  overflow : auto;
}

</style>
</head>
<body>

<?

//get forms with missing pages
//

$sql = "SELECT fid,mpid
	FROM missingpages";

$r = $db->GetRow($sql);

if (isset($r['fid']))
{

	$fid = $r['fid'];
	$mpid = $r['mpid'];
	$npid = "";
	$pid = "";

	
	print "<div id=\"left\">";
	print "<img src=\"../showmissingpage.php?mpid=$mpid\" style=\"width: 100%;\"/> ";
	print "</div>";

	//display possible pages within set to assign to
	$sql = "SELECT p.pid as pid, p.qid as qid
		FROM forms AS f, pages AS p
		LEFT JOIN formpages AS fp ON (fp.fid = '$fid' and fp.pid = p.pid )
		WHERE f.fid = '$fid'
		AND p.qid = f.qid
		AND fp.pid IS NULL";

	$n = $db->GetAll($sql);

	//print "FID: $fid possible missing page: ";

	if (isset($_GET['pid'])) $pid = $_GET['pid'];
	else
	{
		if (isset($n[0]))
			$pid = $n[0]['pid'];
	}
	

	print "<div id=\"right\">";
	
	if ($pid != "")
	{
		$p = 1;
		foreach($n as $np)
		{
			if ($pid == $np['pid'])
			{
				print " $p ";
			}
			else
			{
				print " <a href=\"{$_SERVER['PHP_SELF']}?pid={$np['pid']}&fid=$fid&mpid=$mpid\">$p</a> ";
			}
			$p++;
		}
	
		foreach($n as $np)
		{
			if ($pid == $np['pid'])
			{
				$npid = $np['pid'];
				$qid = $np['qid'];
				print "<img src=\"../showpage.php?qid=$qid&pid=$npid\" style=\"width: 100%;\"/>";
			}
		}
	}

	print "</div>";
	

	print "<div id=\"topper\">";
	print "Form: $fid";
	print "</div>";


	print "<div id=\"bottom\">";
	print "<a href=\"{$_SERVER['PHP_SELF']}?fid=$fid&mpid=$mpid&npid=$npid&accept=accept\">Accept</a>  <a href=\"{$_SERVER['PHP_SELF']}?fid=$fid&mpid=$mpid&npid=$npid&delete=delete\">Delete</a>";
	print "</div>";
}
else
{
	 print "<div id=\"topper\">No missing pages</div>";
}


?>


</body></html>
