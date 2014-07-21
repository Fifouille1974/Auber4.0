<?php

/* 
 * This program is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the 
 * Free Software Foundation; either version 2 of the License, or (at your
 * option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but 
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General
 * Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

 
// Version Information
define('LACE_VERSION', '0.1.3');
define('LACE_RELEASE_DATE', '10 Oct, 2005');

# 'Branding' Options

/** The name you'd like to appear in the main header, and anywhere
    the name of the chatroom is displayed. */
define('LACE_SITE_NAME', 'Lace');


# Lace Client Engine Settings

/** Default polling interval used when IntervalManager is disabled
    (in seconds).  */
define('LACE_INTERVAL', 20);

/** Timeout threshold after which Lace will stop its XMLHttpRequest
    cycle (in minutes) */
define('LACE_TIMEOUT', 10);

/** Timeout threshold after which Lace will consider a user idle
    (in minutes) */
define('LACE_ACTIVITY_IDLE', 10);


# URL Settings

/** Absolute URL to Lace including trailing slash 
   (e.g. 'http://www.myserver.com/lace/' */
// define('LACE_URL_ABS', 'http://127.0.0.1/lace/');

/** Relative URL to Lace including trailing slash
   (e.g. '/lace/' or simply '/') */
define('LACE_URL_REL', '');

/** Use dirified (pretty) URLs for the logs?
    (Requires mod_rewrite) */
define('LACE_LOGS_DIRIFIED', true);


# Cookie Settings

/** Name Lace's session cookie */
define('LACE_SESSION_COOKIE', 'chat');

/** Name Lace's nickname cookie */
define('LACE_NAME_COOKIE', LACE_SESSION_COOKIE.'_name');

/** Secret word that's hashed and used as part of a unique cookie value */
define('LACE_SECRET_WORD', 'xochatpnemyqrst');




# Data file handling

/** Maximum number of posts (lines) to display on the main page */
define('LACE_FILE_MAX_SIZE', 14);

/** Maximum age (in days) of logged conversations */
define('LACE_ARCHIVE_DAYS', 8);

/** Use MD5 change detection?
	
	If set enabled, Lace uses a (much slower) MD5 hashing 
	instead of file modification time and file size to detect
	changes in the main data file.
    
    Use MD5 hashing only if file mod time and size cannot be 
    trusted */
define('LACE_HASH_MD5', false);


# Message Settings

/** Whether to show hourly timestamps on the main page */
define('LACE_SHOW_HOUR', true);

/** Limit messages to this many characters
    NOTE: Keep in mind HTML markup counts towards
    message length */
define('LACE_MAX_TEXT_LENGTH', 1000);


# Data File Locations

/** Filesystem location of the datafile directory including trailing
    slash. Default is the /data directory beneath the directory
    this configuration file is in. 
    
    Note: this is the filesystem path, not the URL.
    */
//----- Debut ajout CI ----------
// define('LACE_DATADIR', dirname(__FILE__).'/ecrire/data/');

# le nom du repertoire tmp/
$ci_tmp = 'tmp/';
# sommes-nous a la racine du site (appel depuis spip.php) ?
if (is_dir($ci_tmp))
	$ci_chemin_tmp = $ci_tmp;
# sommes-nous a la racine du plugin (appel en ajax) ?
else
	$ci_chemin_tmp = "../../".$ci_tmp;

define('LACE_DATADIR', $ci_chemin_tmp);
//----- Fin ajout CI ----------



/** Location of the archived data files (logs) including
    trailing slash. */
// define('LACE_LOGDIR', LACE_DATADIR.'logs/');


$id_article = intval(postVar('id_article', false));
if (!$id_article) $id_article = intval(getVar('id_article', false));

/** Location and filename of the main data file. */
define('LACE_FILE', LACE_DATADIR . 'chat'.$id_article.'.txt');


// define('LACE_TEST', LACE_DATADIR.'testchat'.$id_article.'.txt');

/** Location and filename of the current log data file */
// define('LACE_LOGFILE', LACE_DATADIR.'log'.$id_article.'.dat');
/** Location and filename of the activity (user list) file */
// define('LACE_ACTIVITY_FILE', LACE_DATADIR.'chatactivity'.$id_article.'.dat');



# Display Options - You probably won't enable these.

/** Show the welcome message. */
define('LACE_SHOW_WELCOME', false);

/** Whether to show the About link on the main navbar */
define('LACE_SHOW_ABOUT_LINK', false);

/** Show the copyright notice in the footer? */
define('LACE_SHOW_COPYRIGHT', false);



if (!file_exists(LACE_FILE) AND $id_article) {
	$f = fopen(LACE_FILE, 'w');
	fclose($f);
}	


// Create the activity object
$A = &new LaceActivity;

fixMagicQuotes();
initializeSession();
	
// Feeble attempt at preventing caching
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: ' . gmdate( "D, d M Y H:i:s" ) . 'GMT'); 
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
//---------- Debut ajout CI ----------
header('Content-type: text/html; charset=utf-8');
//---------- Fin ajout CI ----------

class LaceActivity
{
	var $data;
	var $expire;
	
	function LaceActivity()
	{
		$this->expire = time() - (LACE_TIMEOUT * 60);
		$this->getData();
		register_shutdown_function(array(&$this, 'storeData'));
	}
	
	function newMessage($name, $event)
	{
/*
*/		
	}
	
	function update($name = '')
	{
		if (!isset($this->data[$name]))
		{
			$this->newMessage($name, 'joined');
		}
		$this->data[$name] = time();
	}

	function nameChange($from, $to)
	{
		$this->data[$to] = $this->data[$from];
		unset($this->data[$from]);
	}
	
	function remove($name, $addMsg = true)
	{
		if ($addMsg === true)
		{
			$last_post = $this->data[$name];
			$this->newMessage($name, 'left');
		}
		
		unset($this->data[$name]);
	}
	
	function getData()
	{
/*		
*/		
	}
	
	function purge()
	{
		foreach ($this->data as $name => $last_post)
		{
			if ($last_post < $this->expire)
			{
				$this->remove($name);
			}
		}
	}
	
	function storeData()
	{
/*		
*/		
	}

	function formatOutput()
	{
		$output = '';
		foreach($this->data as $name => $last_post)
		{
			$output .= $this->cleanStr($name).'|'.$last_post."\n";
		}
		
		return $output;
	}
	
	function cleanStr($string)
	{
		return str_replace('|', '&brvbar;', $string);
	}
}

	class lib_filter {

		var $tag_counts = array();

		#
		# tags and attributes that are allowed
		#
		
		var $allowed = array(
			'a'      => array('href', 'target', 'title', 'rel'),
			'strong' => array(),
			'em'     => array(),
			'code'   => array(),
			'u'      => array(),
			'b'      => array(),
			'i'      => array(),
			//'img' => array('src', 'width', 'height', 'alt'),
		);


		#
		# tags which should always be self-closing (e.g. "<img />")
		#

		var $no_close = array(
			//'img',
		);


		#
		# tags which must always have seperate opening and closing tags (e.g. "<b></b>")
		#

		var $always_close = array(
			'a',
			'u',
			'b',
			'i',
			'em',
			'code',
			'strong',
		);


		#
		# attributes which should be checked for valid protocols
		#

		var $protocol_attributes = array(
			//'src',
			'href',
		);


		#
		# protocols which are allowed
		#

		var $allowed_protocols = array(
			'http',
			'ftp',
			'mailto',
		);


		#
		# tags which should be removed if they contain no content (e.g. "<b></b>" or "<b />")
		#

		var $remove_blanks = array(
			'a',
			'u',
			'b',
			'i',
			'em',
			'code',
			'strong',
		);


		#
		# should we remove comments?
		#

		var $strip_comments = 1;


		#
		# should we try and make a b tag out of "b>"
		#

		var $always_make_tags = 1;


		###############################################################

		function go($data){

			$this->tag_counts = array();

			$data = $this->escape_comments($data);
			$data = $this->balance_html($data);
			$data = $this->check_tags($data);
			$data = $this->process_remove_blanks($data);

			return $data;
		}

		###############################################################

		function escape_comments($data){

			$data = preg_replace("/<!--(.*?)-->/se", "'<!--'.HtmlSpecialChars(StripSlashes('\\1')).'-->'", $data);

			return $data;
		}

		###############################################################

		function balance_html($data){

			if ($this->always_make_tags){

				#
				# try and form html
				#

				$data = preg_replace("/^>/", "", $data);
				$data = preg_replace("/<([^>]*?)(?=<|$)/", "<$1>", $data);
				$data = preg_replace("/(^|>)([^<]*?)(?=>)/", "$1<$2", $data);

			}else{

				#
				# escape stray brackets
				#

				$data = preg_replace("/<([^>]*?)(?=<|$)/", "&lt;$1", $data);
				$data = preg_replace("/(^|>)([^<]*?)(?=>)/", "$1$2&gt;<", $data);

				#
				# the last regexp causes '<>' entities to appear
				# (we need to do a lookahead assertion so that the last bracket can
				# be used in the next pass of the regexp)
				#

				$data = str_replace('<>', '', $data);
			}

			#echo "::".HtmlSpecialChars($data)."<br />\n";

			return $data;
		}

		###############################################################

		function check_tags($data){

			$data = preg_replace("/<(.*?)>/se", "\$this->process_tag(StripSlashes('\\1'))",	$data);

			foreach(array_keys($this->tag_counts) as $tag){
				for($i=0; $i<$this->tag_counts[$tag]; $i++){
					$data .= "</$tag>";
				}
			}

			return $data;
		}

		###############################################################

		function process_tag($data){

			$matches = '';
			# ending tags
			if (preg_match("/^\/([a-z0-9]+)/si", $data, $matches)){
				$name = StrToLower($matches[1]);
				if (in_array($name, array_keys($this->allowed))){
					if (!in_array($name, $this->no_close)){
						if ($this->tag_counts[$name]){
							$this->tag_counts[$name]--;
							return '</'.$name.'>';
						}
					}
				}else{
					return '';
				}
			}

			# starting tags
			if (preg_match("/^([a-z0-9]+)(.*?)(\/?)$/si", $data, $matches)){
				$name = StrToLower($matches[1]);
				$body = $matches[2];
				$ending = $matches[3];
				if (in_array($name, array_keys($this->allowed))){
					$params = "";
					$matches_2 = '';
					$matches_1 = '';
					preg_match_all("/([a-z0-9]+)=\"(.*?)\"/si", $body, $matches_2, PREG_SET_ORDER);
					preg_match_all("/([a-z0-9]+)=([^\"\s]+)/si", $body, $matches_1, PREG_SET_ORDER);
					$matches = array_merge($matches_1, $matches_2);
					foreach($matches as $match){
						$pname = StrToLower($match[1]);
						if (in_array($pname, $this->allowed[$name])){
							$value = $match[2];
							if (in_array($pname, $this->protocol_attributes)){
								$value = $this->process_param_protocol($value);
							}
							$params .= " $pname=\"$value\"";
						}
					}
					if (in_array($name, $this->no_close)){
						$ending = ' /';
					}
					if (in_array($name, $this->always_close)){
						$ending = '';
					}
					if (!$ending){
						if (isset($this->tag_counts[$name])){
							$this->tag_counts[$name]++;
						}else{
							$this->tag_counts[$name] = 1;
						}
					}
					if ($ending){
						$ending = ' /';
					}
					return '<'.$name.$params.$ending.'>';
				}else{
					return '';
				}
			}

			# comments
			if (preg_match("/^!--(.*)--$/si", $data)){
				if ($this->strip_comments){
					return '';
				}else{
					return '<'.$data.'>';
				}
			}


			# garbage, ignore it
			return '';
		}

		###############################################################

		function process_param_protocol($data){
			$matches = '';
			if (preg_match("/^([^:]+)\:/si", $data, $matches)){
				if (!in_array($matches[1], $this->allowed_protocols)){
					$data = '#'.substr($data, strlen($matches[1])+1);
				}
			}

			return $data;
		}

		###############################################################

		function process_remove_blanks($data){
			foreach($this->remove_blanks as $tag){

				$data = preg_replace("/<{$tag}(\s[^>]*)?><\\/{$tag}>/", '', $data);
				$data = preg_replace("/<{$tag}(\s[^>]*)?\\/>/", '', $data);
			}
			return $data;
		}

		###############################################################

		function fix_case($data){

			$data_notags = Strip_Tags($data);
			$data_notags = preg_replace('/[^a-zA-Z]/', '', $data_notags);

			if (strlen($data_notags)<5){
				return $data;
			}

			if (preg_match('/[a-z]/', $data_notags)){
				return $data;
			}

			return preg_replace(
				"/(>|^)([^<]+?)(<|$)/se",
					"StripSlashes('\\1').".
					"\$this->fix_case_inner(StripSlashes('\\2')).".
					"StripSlashes('\\3')",
				$data
			);
		}

		function fix_case_inner($data){

			$data = StrToLower($data);

			$data = preg_replace('/(^|[^\w\s])(\s*)([a-z])/e',"StripSlashes('\\1\\2').StrToUpper(StripSlashes('\\3'))", $data);

			return $data;
		}

		###############################################################

	}



/**
 * laceListener()
 * 
 * Checks POST variables for incoming messages or
 * update requests.
 */
function laceListener($fromListener = true)
{
	$cookie_name = cookieVar(LACE_NAME_COOKIE, false);
	
	if ($fromListener)
	{
		$post_hash = postVar('hash', false); // hash
		
		if ($post_hash)
		{
			if (validateSession(true) === false)
				return false;
			
			$hash = getMessageHash();
			
			if ($hash == $post_hash)
				return false; // no change

			return $hash.'||||'.getFileContentsRaw();
		}
	}
	
	$post_name = postVar('name', false); // name
	$post_text = postVar('text', false); // text
	
	// For those without XMLHttpRequest
	if ($post_name !== false && $post_text !== false)
	{
		if (validateSession() === false)
			return false;
			
		$message = prepareMessage($post_name, $post_text);
		
		if ($message !== false)
		{
			if ($cookie_name && $cookie_name != $post_name)
			{
				addNameChange($cookie_name, $post_name);
			} 
			else
			{
				global $A; // Activity object
				$A->update($post_name);
			}	
			
			// Reset $name just in case it has been changed
			global $name;
			$name = $post_name;
			setcookie(LACE_NAME_COOKIE, $post_name, time() + 259200, LACE_URL_REL);
			
			addMessage($message);
		}
	}
	
	return false;
}


/** 
 * getMessageHash()
 *
 * Hash the main file for detecting changes
 */
function getMessageHash()
{
	if (file_exists(LACE_FILE)) {
		// hash the main file
		if (LACE_HASH_MD5 === true)
			return md5(implode(file(LACE_FILE)));
		else
		{
			clearstatcache();
			return filemtime(LACE_FILE).':'.filesize(LACE_FILE);
		}
	}	
		
}

/** 
 * getFileContentsRaw()
 *
 * Retrieve raw file contents as one giant string
 * (Why? You can't pass arrays between PHP and Javascript,
 *  unless you use something like PHP-JSON/JSON-PHP, of course.)
 */
function getFileContentsRaw($file = LACE_FILE)
{	
	$today      = date('l');
	$dayString  = '';
    $hourString = '';
	$finalOutput  = '';
	$file = LACE_FILE;
	
	// Read the file
	$fileContents = file(LACE_FILE);
		
	if(is_array($fileContents) && count($fileContents) > 0)
	{
		// We want logfiles in reverse order.
//		if ($file != LACE_FILE)
//			$fileContents = array_reverse($fileContents);
		
		// Create the proper HTML for each line
		foreach ($fileContents as $line)
		{
			// Turn the record into an array full of info
			$line = extractMessageArray($line);
	        
			 $output = '';
/*			
			// Check for new Day

			if ($file == LACE_FILE)
			{
				if ($line['day'] != $dayString)
				    {
				        $first     = ($dayString == '') ? '*' : '';
				    	$dayString = $line['day'];
				    	$output   .= 'date-' . $line['timestamp'] . '||' . $first. $line['date_full'] . '||||';
				    }
			} 
			else
			{
				// Logfiles don't have multiple days
				if($hourString == '')
					$output .= 'date||*' . $line['date_full'] . '||||';
			}			
			
			// Check for new Hour
			if ( ($file == LACE_FILE && $line['day'] == $today && $line['hour'] != $hourString) 
			     || ($file != LACE_FILE && $line['hour'] != $hourString) )
			{
			    $first      = ($hourString == '') ? '*' : '';
				$hourString = $line['hour'];
				$output    .= 'hour-' . $line['hour'] . '||' . $first . $hourString . ':00||||';
			}
*/			
			// Check for Action
			$action  = ($line['action']) ? '*' : '';
			$timestr = ($file == LACE_FILE) ? 'Message du '.$line['date'].' - '.$line['time'] : $line['time'];
			$output .= $line['timestamp']. '||' . $timestr . '||' . $action . $line['name'] . '||' . $line['text'] . '||||';
			
			$finalOutput .= $output;
		}
	}
	else
	// $fileContents array is empty
	{
		$finalOutput .= 'date||*' . date('l, d F Y') . '||||';
		$finalOutput .= time() . '|| ||!Lace||';
		$welcome      = ($file == LACE_FILE) ? 'Bienvenue dans le chat. Il n\'y a aucun message pour l\'instant.' : 'Le chat est vide.';
		$finalOutput .= (file_exists(LACE_FILE)) ? $welcome : $welcome;
	}

	return rtrim($finalOutput, '|');
}

/** 
 * printFileContentsHTML()
 *
 * Grab the files's contents and format it with HTML
 * all in one step.
 */
function printFileContentsHTML($file = LACE_FILE) {
	echo formatFileContents(getFileContentsRaw($file));
}

/** 
 * formatFileContents()
 *
 * Wrap raw file contents in HTML for display
 */
function formatFileContents($rawFileContents)
{
	// break apart the file contents into records
	$items  = explode('||||', $rawFileContents);
	$count  = count($items);
	$output = '';
		
	for ($i = 0; $i < $count; $i++)
	{
		// break record into fields
		$fields = explode('||', $items[$i]);
/*		
*/		
		// show the message
		
		// $fields[0] = id attribute
		// $fields[1] = time string
		// $fields[2] = name (a * prefix denotes an action, a ! denotes a system notice)
		// $fields[3] = text  
		$action = ($fields[2]{0} == '*');
		$notice = ($fields[2]{0} == '!');
		$name   = ($action || $notice) ? substr($fields[2], 1) : $fields[2];
		
		// A system message is defined by an action by the user Lace
		$system = ($action && $name == 'Lace');
		
		$class  = ($action && !$system) ? 'message action' : 'message';

		// Message id attributes sometimes were non-unique because the messages shared
		// the same timestamp.  Until a solution is found - and one may not be necessary -
		// the id is simply ignored and is not needed.  (Also helps keep XHTML valid)
		//$output .= '<p id="msg-' . $fields[0] . '"';
		$output .= '<p';
		$output .= ($notice) ? ' class="notice"' : '';
		$output .= ($system) ? ' class="system"' : '';
		$output .= '><span class="name">';
		$output .= ($action && !$system) ? '&nbsp;' : $name . ' <a title="'. $fields[1] . '">::</a> ';
		$output .= '</span><span class="' . $class . '">';
		$output .= ($action && !$system) ? '<a title="' . $fields[1] . '">' . $name . '</a> ' : '';
		$output .= $fields[3] . '</span></p>' . "\n";
	}
	
	return $output;
}

/** 
 * getName()
 *
 * Attempt to find a user's name in the $_POST
 * and $_COOKIE variables
 */
function getName()
{
	global $cilogin;
	// Look for the name in $_POST then in 
	// $_COOKIE, or give a new generic name
	if (array_key_exists('name', $_POST) && strlen(trim($_POST['name'])) > 0) 
	{
		$name = $_POST['name'];
		setcookie(LACE_NAME_COOKIE, $name, time()+3600*24*30, LACE_URL_REL);
	} 
	else {
		if ($cilogin) 
		{
			$name = $cilogin;
			setcookie(LACE_NAME_COOKIE, $name, time()+3600*24*30, LACE_URL_REL);				
		} 
		else {	
			$name = cookieVar(LACE_NAME_COOKIE, 'Guest ' . substr(rand(), 0, 4));
		}
	}
	return urldecode($name);
}

/** 
 * extractMessageArray()
 *
 * Convert a record from the data file
 * into a usable array of data
 */
function extractMessageArray($line)
{
	$linearray = explode('||', $line);
	
	// Snag the unix timestamp and perform some date calculations
	$datetime = array_shift($linearray);
    
	// Time elapsed (e.g. 1.5 hours, 4 days, etc.)
	$age = duration_str(time() - $datetime, false, 2);
	
	// Long format date
	$date_full = date("l, d F Y", $datetime);
	
	// Short format date
	$date = date('d/m/Y', $datetime);
	
	// Time of day
	$time = date('H:i', $datetime);
	
	// Day of week
	$day = date('l', $datetime);
    
	// Hour
	$hour = date('H', $datetime);
	
	// Next snag the name
	$name = array_shift($linearray);
	
	// Check for action or system notice
	$action = ($name{0} == '*') ? true : false;
	$notice = ($name{0} == '!') ? true : false;
	
	if ($action || $notice)
		$name = substr($name, 1);
	
	// Now put the post back together
	$words = trim(implode(' ', $linearray));
	
	// return this mess of info
	return array
	(
		'timestamp' => $datetime,
		'date_full' => $date_full,
		'date'      => $date,
		'time'      => $time,
		'day'       => $day,
		'hour'      => $hour,
		'age'       => $age,
		'action'    => $action,
		'notice'    => $notice,
		'name'      => $name,		
		'text'      => $words
	);
}	

/** 
 * preFilterName()
 *
 * Perform custom filtering on names
 * that lib_filter doesn't cover
 */
function preFilterName($name) 
{	
	// Prevent long names from disrupting mesage flow.
	$name = substr($name, 0, 40);
	
	// System messages are from the user Lace.  No one
	// can use the name Lace.
	if ($name == 'Lace')
		$name = 'Not Lace';
	
	$name = htmlentities(trim($name));	
	
	// Lace uses an asterisk prefix in the name to denote actions,
	// so users can't have one in that position.
	if ($name{0} == '*')
		$name = substr($name, 1);
		
	// Lace uses a bang prefix in the name to denote system notices,
	// so users can't have one in that position
	if ($name{0} == '!')
		$name = substr($name, 1);
		
	// Sorry, Lace uses pipes as delimiters - broken vertical bar for you!
	$name = str_replace('|', '&brvbar;', $name);
		
	// No all caps names. CAR FREAK becomes Car Freak
	//if (strtoupper($name) == $name)
		//$name = ucwords(strtolower($name));
	
	return $name;
}

/** 
 * preFilterLink()
 *
 * Filter Link text
 */
function preFilterLink($text)
{
	// Separate the URL from the link text
	// and filter the link text.
	// If the URL somehow contains malicious
	// characters, they should be filtered out
	// by htmlentities() when the URL is output
	// as a link - but it might break the link...
	$array = explode(' ', $text);
	$url   = array_shift($array);
	$text  = implode(' ', $array);
	$text  = preFilterText($text);
	return $url . ' ' . $text;
}

/** 
 * codeTagFilter()
 *
 * Replace the contents of <code> tags with HTML Entities so 
 * that lib_filter will leave it alone.
 * 
 * Note:
 * If the closing <code> tag is missing, this step is skipped and
 * when lib_filter kicks in, malicious code will be stripped
 * and the closing tag added, which means the contents of any code 
 * tags will likely be missing or mangled.
 */
function codeTagFilter($text)
{
	return stripslashes(preg_replace('%(<code>)(.*?)(</code>)%se', "'\\1'.htmlentities(codeTagFilter('\\2')).'\\3'", $text));
}	

/** 
 * preFilterText()
 *
 * Perform custom filtering that lib_filter
 * normally misses.
 */
function preFilterText($text)
{

	// Make sure the submitted text isn't too long
	// This shouldn't affect valid URLs as AutoLinks
	if (strlen($text) > LACE_MAX_TEXT_LENGTH)
		$text = substr($text, 0, LACE_MAX_TEXT_LENGTH);
	
	// Wrap long lines if there are more than 35 characters
	// and less than three spaces.
	if (strlen($text) > 35 && substr_count($text, ' ') < 3)
		$text = real_wordwrap($text, 35, ' ');
	
 	// Filter the contents of <code> tags so that lib_filter
 	// doesn't interfere with them.
	$text = codeTagFilter($text);
	
	// Add rel attribute to links
	if (strpos($text, '<a') !== false && strpos($text, '<a rel=') === false)
		$text = str_replace('<a ', '<a rel="external" ', $text);
	
	// First pass at attempting to fix number comparisons before 
	// lib_filter can munge them.
	//
	// Input       Output
	// 800<=1000   800 &lt;= 1000
	// 400> 200    400 &gt; 200
	// 100 <>500   100 &lt;&gt; 500
	// etc...
	$text = preg_replace('%(\d)\s*([<>=]{1,2})\s*(\d)%se', "'\\1'.htmlentities(' \\2 ').'\\3'", $text);
		
	// Replace all orphaned < and > characters with entities to keep
	// lib_filter from hosing them...
	// And, sorry, Lace uses pipes as delimiters - broken vertical bar for you!	
	$search  = array(' < ', ' > ', '|');
	$replace = array(' &lt; ', ' &gt; ', '&brvbar;');
	$text    = str_replace($search, $replace, $text);
	
	return $text;
}

/**
 * getCommand()
 * Parse incoming message text for a command
 *
 * Commands must be within the first 4 characters
 * of the message
 *
 * The two supported commands are actions
 * (designated by '/me ') and links
 * (designated by 'http' or 'www.')
 */
function getCommand($text)
{
	$cmd = strtolower(substr($text, 0, 4));
	switch ($cmd)
	{
		case '/me ':
		case '\me ':
			$command = 'action';
			break;
		case 'http':
		case 'www.':
			$command = 'link';
			break;
		default:
			$command = false;
			break;
	}
	
	return $command;
}

/** 
 * prepareMessage()
 *
 * Prepare incoming message data for storage
 */
function prepareMessage(&$name, $text)
{
	$message = array();
	
	// Parse text for commands and format accordingly
	$cmd = getCommand($text);
	
	// Perform some custom prefiltering
	$name = prefilterName($name);
	$text = ($cmd == 'link' ) ? preFilterLink($text) : preFilterText($text);

	// HTML filter
	$filter = new lib_filter();
	
	$action = false;
	
	switch ($cmd)
	{
		case 'action':
			// Action
			$action = true;
			$text = $filter->go(substr($text, 4));
			break;
		case 'link':
			// AutoLink
			// Grab the URL from the message
			$input = explode(' ', trim($text));
			$url   = array_shift($input);
			if (substr($url, 0, 4) == 'www.')
				$url = 'http://'.$url;
			$urlparts = @parse_url($url);

			if (array_key_exists('host', $urlparts))
			{
				// Url is most likely valid (parse_url() is
				// not the best way to check this)

				if (count($input) > 0)
					// There is link text
					$urltext = implode(' ', $input);
				else 
					// the url becomes the link text, and is shotened if necessary
					$urltext = (strlen($url) > 40) ? str_shorten($url, 25) : $url;
					
				$text = '<a href="'.htmlentities($url).'" title="['.htmlentities($urlparts['host']).']">'.htmlentities($urltext).'</a>';
			} 
			else 
				// Url is most likely invalid
				return false;
			break;
		default:
			// No command
			$text = $filter->go($text);
			$text = cifiltre($text);

			break;
	}

	if (strlen(trim($text)) == 0)
		// Message text is invalid
		return false;
	
	$message['action'] = $action;
	$message['time']   = time();
	$message['name']   = $name;
	$message['text']   = $text;
	
	return $message;
}

/**
 * newLog()
 *
 * Create a new archive log file and delete
 * ones past their prime
 */
function newLog($log, $date)
{
/*	
*/   	
}

/**
 * logMessage()
 *
 * Add message to the logfile
 */
function logMessage($line) {
/*	
*/  	
}

/**
 * addNameChange()
 *
 * Add message to the main data file
 */
function addNameChange($from, $to)
{
/*	
*/
}

/**
 * addMessage()
 *
 * Add message to the main data file
 */
function addMessage($message)
{
	$name = ($message['action']) ? '*'.$message['name'] : $message['name'];
	$text = $message['text'];
//----- Debut ajout CI --------------	
//	$name = html_entity_decode(htmlentities($name, ENT_COMPAT, 'UTF-8'));
//	$text = html_entity_decode(htmlentities($message['text'], ENT_COMPAT, 'UTF-8'));
//----- Fin ajout CI --------------	
	$time = $message['time'];
	$line = $time.'||'.$name.'||'.$text;	
	
//----- Debut ajout CI (securite supplementaire) --------	
	if (isset($_COOKIE['spip_session'])) {
//----- Fin ajout CI --------------		
		// just write to file
		$file = fopen(LACE_FILE, 'a');
		fwrite($file, $line."\n");
		fclose($file);
//----- Debut ajout CI ------------	
	}
//----- Fin ajout CI --------------	
    
}

/**
 * printLogList()
 *
 * Display the log history navigation
 */
function printLogList($currentFile)
{
/*	
*/	
}
function validateSession($updateActivity = false) {
	if (cookieVar(LACE_SESSION_COOKIE) === getCookieString())
	{
		global $A; // Activity object
		$name = postVar('name');
		if (cookieVar(LACE_NAME_COOKIE) === $name)
		{
			$A->update($name);
		}
		if ($updateActivity)
		{
			$A->update(cookieVar(LACE_NAME_COOKIE));
		}
		setcookie(LACE_SESSION_COOKIE, getCookieString(), time() + 3600, LACE_URL_REL);
		return true;
	}
	return initializeSession();
}

/** 
 * getCookieString()
 *
 * Returns an MD5 hash of various unique info to
 * use as a unique identifier in a cookie
 */
function getCookieString() {
	return md5($_SERVER['HTTP_USER_AGENT'].LACE_SECRET_WORD);
}

/** 
 * initializeSession()
 *
 * Start a new session
 */
function initializeSession()
{
	// LACE_SESSION_INIT_BYPASS is defined in the Ajax listener, 
	// lace.php.  This is to help prevent abusing the listener.
	if (defined('LACE_SESSION_INIT_BYPASS') === true)
		return;

	/*
	global $name;
	global $A; // Activity object
	
	$_name = getName();
	$A->update($_name);
	*/
				
	if (cookieVar(LACE_SESSION_COOKIE, false) === false)
	{
		global $name;
		global $A; // Activity object
		
		$_name = getName();
		$A->update($_name);
		
		$name = $_name;	
		setcookie(LACE_SESSION_COOKIE, getCookieString(), time() + 600, LACE_URL_REL);
		setcookie(LACE_NAME_COOKIE, $name, time() + 2592000, LACE_URL_REL);
		
		return;
	} 
}

/** 
 * fixMagicQuotes()
 *
 * Remove slashes from all incoming GET/POST/COOKIE data
 *
 * Yoinked straight out of Ryan Grove's Poseidon
 * http://wiki.wonko.com/software/poseidon 
 */

function fixMagicQuotes()
{
//----- Debut ajout CI (php 5.3) ------ 
//	set_magic_quotes_runtime(0);
//----- Fin ajout CI ------

	if (get_magic_quotes_gpc() === false)
		return;

	function removeMagicSlashes($element)
	{
		if (is_array($element))
			return array_map('removeMagicSlashes', $element);
		else
			return stripslashes($element);
	}

	// Remove slashes from all incoming GET/POST/COOKIE data.
	$_GET    = array_map('removeMagicSlashes', $_GET);
	$_POST   = array_map('removeMagicSlashes', $_POST);
	$_COOKIE = array_map('removeMagicSlashes', $_COOKIE);
}


/** 
 * duration_str()
 *
 * Turn a given number of seconds into a human readable
 * duration statement (e.g. 100 seconds -> '1 minute, 40 seconds'
 */
function duration_str($seconds, $short_units = false, $min_units = false)
{
	// This craziness converts a given number of seconds
	// into a human readable time duration 
	//
	// $short_units: use short units ('6 m' rather than '6 minutes')
	// $min_units  : minimum units to return ('days' will remove hours, minutes, seconds)
	//
	// Example: 
	// 
	//		echo duration_str(time() - (time() - 3600));
	//		echo duration_str(time() - (time() - 3600 * 24 * 3.5));
	//		echo duration_str(time() - (time() - 60 * 250), true);
	//		echo duration_str(time() - (time() - 3600 * 24 * 500), false, 'weeks');
	//
	// Outputs:
	//
	//		1 hour
	//      3 days, 12 hours
	//      4 h, 10 m
	//		1 year, 4 months, 1 week	
	
	$seconds = abs((int)$seconds);
	
	$periods = array
	(
		'years'   => array ( 31557600,'y'),
		'months'  => array ( 2628000, 'mo'),
		'weeks'   => array ( 604800,  'w'),		
		'days'    => array ( 86400,   'd'),
		'hours'   => array ( 3600,    'h'),
		'minutes' => array ( 60,      'm'),
		'seconds' => array ( 1,       's'),
	);
	
	if ($min_units !== false)
	{
		if (is_int($min_units) === false)
		{
			$unit_keys = array_keys($periods);
			$key = array_keys($unit_keys, $min_units);
			for ($i = $key[0] + 1; $i < 7; $i++)
				array_pop($periods);
		}
	}
	
	foreach ($periods as $units => $data)
	{
		$count = floor($seconds / $data[0]);
		if ($count <= 0)
			continue;
		
		$units = ($short_units) ? $data[1] : $units;
		$values[$units] = $count;
		$seconds = $seconds % $data[0];
	}

	if (empty($values))
		return false;
		
	foreach ($values as $key => $value)
	{
		if ($short_units === false && $value == 1)
			$key = substr($key, 0, -1);
			
		$array[] = $value . ' ' . $key;
	}
	
	if (!empty($array))
	{
		if (is_int($min_units) === true) 
		{
			$count = count($array);
			if ($min_units > $count)
				$min_units = $count;
			
			for ($i = 0; $i < $min_units; $i++)
				$temp[] = $array[$i];
			
			$array = $temp;
		}
		
		return implode(', ', $array);
	}
	
	return false;
}

/** 
 * getVar()
 *
 * Retrieves the given variable from $_GET if it exists
 */
function getVar($var, $default = false)
{
	return (array_key_exists($var, $_GET)) ? trim($_GET[$var]) : $default;
}

/** 
 * postVar()
 *
 * Retrieves the given variable from $_POST if it exists
 */
function postVar($var, $default = false)
{
	return (array_key_exists($var, $_POST)) ? trim($_POST[$var]) : $default;
}

/** 
 * cifiltre()
 *
 * Enlève les sauts de ligne
 */
function cifiltre($texte)
{
	// supprimer les sauts de ligne
	$texte = preg_replace("/[\n\r]/s", " ", $texte);
	
	// sauts de ligne et paragraphes
	$texte = preg_replace("/\n/", " ", $texte);
	$texte = preg_replace("/<(p|br)( [^>]*)?".">/", "\n\n", $texte);
	
	return $texte;
}

/** 
 * cookieVar()
 *
 * Retrieves the given variable from $_COOKIE if it exists
 */
function cookieVar($var, $default = false)
{
	return (array_key_exists($var, $_COOKIE)) ? trim($_COOKIE[$var]) : $default;
}

/** 
 * real_wordwrap()
 *
 * Wraps words, but doesn't break tags
 */
function real_wordwrap($str, $cols, $cut)
{
	$len = strlen($str);
	$tag = 0;
	$wordlen = 0;
	$result  = '';
	
	for ($i = 0; $i < $len; $i++)
	{
		$chr = substr($str, $i, 1);
		if ($chr == '<')
			$tag++;
		elseif ($chr == '>')
			$tag--;
		elseif (!$tag && $chr == ' ')
			$wordlen = 0;
		elseif (!$tag)
			$wordlen++;
		
		if (!$tag && $wordlen > 0 && !($wordlen%$cols))
			$chr .= $cut;
		
		$result .= $chr;
	}
	
	return $result;
}

/** 
 * str_shorten()
 *
 * Chops a string into chunks and pastes the first and last
 * together with an ellipsis
 */
function str_shorten($str, $len)
{
	$separator = '~|~';
	$chunk = explode($separator, substr(chunk_split($str, $len, $separator), 0, -3));
	return $chunk[0]. '...' . $chunk[count($chunk) -1];
}

/** 
 * antispammer()
 *
 * Fancy email obfuscation script by the fine folks at Automatic.
 * To use, go to http://automaticlabs.com/products/enkoder
 * and replace the script below with your own.
 */
function antispammer()
{
	$script = <<< ENDSCRIPT
	
	<script type="text/javascript">
	//<![CDATA[
	function hiveware_enkoder() {
		var i,j,x,y,x=
	"x=\"783d227b402532353a3b36673535363936373639393936393636363a36383639393736" +
	"39363836393938363a363736353938363a363a363a36353639363c363a3637363936383635" +
	"363b363536353636393636393634363536333639363b363a36353639363836393639363639" +
	"37363839363635363536393937363936343639363c36393936363a36373639393936363934" +
	"36393635363a363536393638363a3637363a3637363a3636363a36373639363c3639393736" +
	"39393736393638363a3635363939373639363436393938363736333639363a363939373639" +
	"36343639363c36393936363539383639363636393939363939373638393636353635363536" +
	"33363a36373639363c363a363736393936363936383636393736383936363536353637363c" +
	"36353633363939363639363c36393935363936383635363336393638363939373639363436" +
	"39363c363939363635393836383936363536353636393836373635363a363536393638363a" +
	"3637363a36373635363336383636363a36373639363c363939373639393736393638363a36" +
	"3536393937363936343639393836363936363539393639363436363938363536353635363c" +
	"363639353636363336363935353536653a3c3667353a353a3665393939693a35353b393c36" +
	"6736333665393c36663a3b3568396639383968393a3a37393b3665393c356536673635353c" +
	"3a653a3c356536673a38396839383a36393639343a333938353b353a3538353a35653a3b35" +
	"683a363a3839353a363a373a35353b393c35663635353c353c36653a673a3c2532353e7c40" +
	"2a2a3e6972752b6c40333e6c3f7b316f68716a776b3e6c2e40352c7e7c2e40787168766664" +
	"73682b2a282a2e7b317678657677752b6c2f352c2c3e2532327c223b793d27273b783d756e" +
	"6573636170652878293b666f7228693d303b693c782e6c656e6774683b692b2b297b6a3d78" +
	"2e63686172436f646541742869292d333b6966286a3c3332296a2b3d39343b792b3d537472" +
	"696e672e66726f6d43686172436f6465286a297d79\";y='';for(i=0;i<x.length;i+=2)" +
	"{y+=unescape('%'+x.substr(i,2));}y";
	while(x=eval(x));}hiveware_enkoder();
	//]]>
	</script>
	
ENDSCRIPT;

	echo $script;
}	
?>