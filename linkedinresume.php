<?php
/*
Plugin Name: LinkedIn Resume
Plugin URI: http://creations.arnaud-lejosne.com
Description: Display your CV on your blog from your linkedIn public page informations.
Version: 2.00
Author: Arnaud Lejosne
Author URI: http://creations.arnaud-lejosne.com
*/


/*  
	Copyright 2009  Arnaud Lejosne  (email : contact@arnaud-lejosne.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/


define('LINKEDINRESUMEPATH',WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__)));
define('Version',"1.95");
$plugin_dir = basename(dirname(__FILE__));
load_plugin_textdomain( 'linkedinresume', 'wp-content/plugins/'.$plugin_dir.'/lang', $plugin_dir.'/lang' ); 
define('LinedInResumeadminOptionsName', 'linedinResumeAdminOption');
add_shortcode('linkedinresume', 'linkedinresume_active_shortcode');
function linkedinresume_active_shortcode($atts) {
	return linkedinresume_display_CV($atts);
}

//Returns an array of admin options
function linkedinresume_getAdminOptions() {
	//$devLinkedinResumeAdminOptions = array('linkedinId' => '');
	$devOptions = get_option(LinedInResumeadminOptionsName);
	if (!empty($devOptions)) {
		foreach ($devOptions as $key => $option)
			$devLinkedinResumeAdminOptions[$key] = $option;
	}
	return $devLinkedinResumeAdminOptions;
}


### Function: WordPress Get CV
function linkedinresume_get_CV($options) {
	$devOptions = linkedinresume_getAdminOptions();
	
	if (!isset($options['lang']) && preg_match('/([a-z]{2})_([A-Z]{2})/', get_locale(), $regs)) {
		$language = $regs[1];
	} else {
		$language = $options['lang'];
	}
	$ch = curl_init("http://www.linkedin.com/in/".$devOptions['linkedinId'].'/'.$language);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	$useragent="Mozilla/5.0 (Windows; U; Windows NT 5.1; en-UK; rv:1.8.1.1) Gecko/20061204 Firefox/3.5";
	curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
	$page = curl_exec($ch);
	curl_close($ch);
	
	preg_match_all('%<div>[\r\n\t ]*<div class="position[^"]+" style="display:block">[\r\n\t ]*<a class="include" href="#name">[\r\n\t ]*</a>[\r\n\t ]*<div class="postitle">[\r\n\t ]*<h3 class="[^"]+">[\r\n\t ]*<span class="title">(?<title>[^<]*)</span>[\r\n\t ]*</h3>[\r\n\t ]*<h4>[\r\n\t ]*<strong>[\r\n\t ]*(?:<a class="company-profile-public" href="[^"]*">[\r\n\t ]*)?<span class="org summary">(?<summary>[^<]*)</span>(?:[\r\n\t ]*</a>)?[\r\n\t ]*(?:</strong>[\r\n\t ]*)+</h4>[\r\n\t ]*</div>[\r\n\t ]*<p class="orgstats organization-details[^"]+">(?<organization_details>[^<]*)</p>[\r\n\t ]*<p class="period">[\r\n\t ]*<abbr class="dtstart" title="(?<date_debut>[0-9\-]+)">(?<date_debut_simp>[^<]*)</abbr>[\r\n\t ]*&#8211;[\r\n\t ]*<abbr class="(?:dtstamp|dtend)" title="(?<date_fin>[0-9\-]+)">(?<date_fin_simp>[^<]*)</abbr>[\r\n\t ]*<span class="duration">[\r\n\t ]*<span class="value-title" title="(?:[^<]*)">(?<duree>[^<]*)</span>(?:[^<]*)</span>[\r\n\t ]*</p>[\r\n\t ]*(?:<p class=" description[^"]+">(?<description>(?:(?:[^<]*)(?:<br>)?)*)</p>[\r\n\t ]*)?</div>[\r\n\t ]*</div>%m', $page, $result, PREG_PATTERN_ORDER);

	$infosPerso = array();
	$jobsArray = array();

	for($i = 0; $i<count($result[1]); $i++)
	{
		$tmpArray = array();
		$tmpArray['poste'] = trim($result['title'][$i]);
		$tmpArray['entreprise'] = trim($result['summary'][$i]);
		$tmpArray['type_entreprise'] = trim($result['organization_details'][$i]);
		$tmpArray['date_debut'] = trim($result['date_debut'][$i]);
		$tmpArray['date_debut_simp'] = trim($result['date_debut_simp'][$i]);
		$tmpArray['date_fin'] = trim($result['date_fin'][$i]);
		$tmpArray['date_fin_simp'] = trim($result['date_fin_simp'][$i]);
		$tmpArray['duree'] = trim($result['duree'][$i]);
		$tmpArray['description'] = trim($result['description'][$i]);
		$jobsArray[$i] = $tmpArray;
	}
	$infosPerso['jobs'] = $jobsArray;
	
	preg_match_all('%<div class="image[^"]*" style="display:block" id="profile-picture">[ \n\r\t]*<img src="([^"]+)" class="photo" width="[^"]+" height="[^"]+" alt="([^"]+)">[ \n\r\t]*</div>%m', $page, $result, PREG_PATTERN_ORDER);
	$infosPerso['image_url'] = trim($result[1][0]);
	$infosPerso['image_alt'] = trim($result[1][1]);
	preg_match_all('%<span class="given-name">([^<]*)</span>%m', $page, $result, PREG_PATTERN_ORDER);
	$infosPerso['givenName'] = trim($result[1][0]);
	preg_match_all('%<span class="family-name">([^<]*)</span>%m', $page, $result, PREG_PATTERN_ORDER);
	$infosPerso['name'] = trim($result[1][0]);
	preg_match_all('%<p class="title" style="display:block">([^<]*)</p>%m', $page, $result, PREG_PATTERN_ORDER);
	$infosPerso['title'] = trim($result[1][0]);
	preg_match_all('%<span class="locality">([^<]*)</span>%m', $page, $result, PREG_PATTERN_ORDER);
	$infosPerso['locality'] = trim($result[1][0]);
	preg_match_all('%<ul class="current">[\r\n\t ]*<li>([^<]*)(?:[\r\n\t ]*<span class="at">at </span>[\r\n\t ]*[^<]*)?</li>[\r\n\t ]*</ul>%m', $page, $result, PREG_PATTERN_ORDER);
	$infosPerso['curJob'] = trim($result[1][0]);
	preg_match_all('%<p class=" description summary">((?:(?:[^<]*)(?:<br>)?)*)</p>%m', $page, $result, PREG_PATTERN_ORDER);
	$infosPerso['summary'] = trim($result[1][0]);
	preg_match_all('%<div id="profile-specialties" style="display:block">[\r\n\t ]*<h3>[^<]*</h3>[\r\n\t ]*<p class="null">((?:(?:[^<]*)(?:<br>)?)*)</p>[\r\n\t ]*</div>%m', $page, $result, PREG_PATTERN_ORDER);
	$infosPerso['skills'] = trim($result[1][0]);

	preg_match_all('%<div class="position[^"]+" id="[^"]*">[\r\n\t ]*<h3 class="summary fn org">[\r\n\t ]*(?<summary>[^<]*)</h3>[\r\n\t ]*<h4 class="details-education">[\r\n\t ]*(?:<span class="degree">(?<degree>[^<]*)</span>)?[\r\n\t ]*,?[\r\n\t ]*(?:<span class="major">[\r\n\t ]*,?(?<major>[^<]*)</span>)?[\r\n\t ]*</h4>[\r\n\t ]*<p class="period">(?:[\r\n\t ]*<abbr class="dtstart" title="(?<date_debut>[^"]+)">(?<date_debut_simp>[^<]*)</abbr>[\r\n\t ]*&#8211;[\r\n\t ]*<abbr class="dtend" title="(?<date_fin>[^"]+)">(?<date_fin_simp>[^<]*)</abbr>[\r\n\t ]*(?<commentaire>\((?:[^)]*)\))?)?[\r\n\t ]*</p>[\r\n\t ]*(?:<p class="notes">(?<notes>(?:(?:[^<]*)(?:<br)?)*)</p>[\r\n\t ]*)?(?:<dl class="activities-societies">(?<activities>(?:(?:[^<]*)(?:<dt)?(?:</dt)?(?:<dd)?(?:</dd)?)*)</dl>[\r\n\t ]*)?<p class=" desc details\-education">(?<details>(?:(?:[^<]*)(?:<br)?)*)</p>[\r\n\t ]*</div>%m', $page, $result, PREG_PATTERN_ORDER);
	
	$educArray = array();
	for($i = 0; $i<count($result['summary']); $i++)
	{
		$tmpArray = array();
		$tmpArray['ecole'] = trim($result['summary'][$i]);
		$tmpArray['degree'] = trim($result['degree'][$i]);
		$tmpArray['course'] = trim($result['major'][$i]);
		$tmpArray['date_debut'] = trim($result['date_debut'][$i]);
		$tmpArray['date_debut_simp'] = trim($result['date_debut_simp'][$i]);
		$tmpArray['date_fin'] = trim($result['date_fin'][$i]);
		$tmpArray['date_fin_simp'] = trim($result['date_fin_simp'][$i]);
		$tmpArray['commentaire'] = trim($result['commentaire'][$i]);
		$tmpArray['notes'] = trim($result['notes'][$i]);
		$tmpArray['activities'] = trim($result['activities'][$i]);
		$tmpArray['details'] = trim($result['details'][$i]);
		$educArray[$i] = $tmpArray;
	}
	$infosPerso['education'] = $educArray;
	
	if (preg_match('%<dt>Websites</dt>[\r\n\t ]*<dd>[\r\n\t ]*<ul>[\r\n\t ]*(<li>[\r\n\t ]*<a href="(?:[^"]*)" class="url" rel="[a-z]*" target="[a-z_]*">[\r\n\t ]*(?:[A-Za-z ]*)[\r\n\t ]*</a>[\r\n\t ]*</li>[\r\n\t ]*)+</ul>[\r\n\t ]*</dd>%', $page, $regs)) {
		preg_match_all('%<li>[\r\n\t ]*<a href="([^"]*)" class="url" rel="[a-z]*" target="[a-z_]*">[\r\n\t ]*([A-Za-z ]*)[\r\n\t ]*</a>[\r\n\t ]*</li>[\r\n\t ]*%', $regs[0], $result, PREG_PATTERN_ORDER);
	
		$websitesArray = array();
		for($i = 0; $i<count($result[1]); $i++){
			$tmpArray = array();
			$tmpArray['url'] = trim($result[1][$i]);
			$tmpArray['name'] = trim($result[2][$i]);
			$websitesArray[$i] = $tmpArray;
		}
		$infosPerso['sites'] = $websitesArray;
	}
	return $infosPerso;
}

### Function: Display CV
function linkedinresume_display_CV($atts) {
	wp_register_style('linkedincv', LINKEDINRESUMEPATH.'css/style.css', false, Version, 'all');
	wp_print_styles('linkedincv');
	
	$myCV = linkedinresume_get_CV($atts);
	echo '
		<div class="cvPart">
			<div class="cvHeaderInfos">
				<h2>'.$myCV['givenName'].' '.$myCV['name'].'</h2>
				<h3>'.$myCV['title'].'</h3>
				<h4>'.$myCV['locality'].'</h4>
			</div>';
		if(!empty($myCV['image_url']))
		{
			echo '
			<div class="cvImage">
				<img src="'.$myCV['image_url'].'" alt="'.$myCV['image_alt'].'" height="80"/>
			</div>';
		}
		echo '</div>';
		if(!empty($myCV['summary'])||!empty($myCV['skills']))
		{
			echo '
			<div class="cvPart">';
			if(!empty($myCV['summary']))
			{
				echo '
				<h3>'.__('Summary : ','linkedinresume').'</h3>
				<p>'.$myCV['summary'].'</p>';
			}
			if(!empty($myCV['skills']))
			{
				echo '
				<h3>'.__('Specialties : ','linkedinresume').'</h3>
				<p>'.$myCV['skills'].'</p>';
			}

			echo '</div>';
		}
		if(!empty($myCV['jobs']))
		{
			echo '
			<div class="cvPart">
			<h2>'.__('Experience','linkedinresume').'</h2>';
			foreach($myCV['jobs'] as $job)
			{
				echo '
				<h3>'.$job['poste'].'</h3>
				'.(isset($job['url_entreprise'])?'<h4>'.$job['entreprise'].'</h4>':'<h4>'.$job['entreprise'].'</h4>').'
				<p>'.$job['type_entreprise'].'</p>
				<p>'.$job['date_debut_simp'].' - '.$job['date_fin_simp'].' '.$job['duree'].'</p>
				<p>'.$job['description'].'</p>';
			}
			echo '</div>';
		}
		if(!empty($myCV['education'][0]))
		{
			echo '<div class="cvPart">
			<h2>'.__('Education','linkedinresume').'</h2>';
			foreach($myCV['education'] as $educ)
			{
				echo '<h3>'.$educ['ecole'].'</h3>';
				if($educ['degree'] || $educ['course'])
					echo '<p>'.$educ['degree'].' '.$educ['course'].'</p>';
				echo '<p>';
				if($educ['date_debut_simp'] && $educ['date_fin_simp'])
					echo $educ['date_debut_simp'].' - '.$educ['date_fin_simp'];
				echo $educ['commentaire'].'</p>';
				if(!empty($educ['notes']))
					echo '<p>'.$educ['notes'].'</p>';
				if(!empty($educ['details']))
					echo '<p>'.$educ['details'].'</p>';
			}
			echo '</div>';
		}
		if(!empty($myCV['sites']))
		{
			echo '<div class="cvPart">
				<h2>'.__('WebSites','linkedinresume').'</h2><ul>';
			foreach($myCV['sites'] as $site)
			{
				echo '<li><a href="'.$site['url'].'" target="_blank">'.$site['name'].'</a></li>';
			}
			echo '</ul></div>';
		}
	echo '
	<p>Copyright '.date('Y').' LinkedIn Corporation. '.__('All rights reserved','linkedinresume').'</p>
	<!-- '.__('All rights reserved','linkedinresume').' Arnaud Lejosne -->
	';
}

## Interface d'admin

function linkedinresume_printAdminPage() {
	wp_register_style('admin_linkedinresume', LinkedinResumePATH.'css/admin.css', false, Version, 'all');
	wp_print_styles('admin_linkedinresume');
	
	if (isset($_POST['update_linkedinresumeSettings'])) {
		if (isset($_POST['linkedinId'])) {
			$devOptions['linkedinId'] = $_POST['linkedinId'];
		}
		update_option(LinedInResumeadminOptionsName, $devOptions);
	}
	$devOptions = linkedinresume_getAdminOptions();

	?>
	<div class="wrap">
		<h2><?php _e('WordPress LinkedinResume Plugin Option','linkedinresume') ?></h2>
		<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
			<div style="font-style:italic;">Add [linkedinresume] on the page where you want your cv to appear
			<br/>Note : by default, the language will be your wordpress language but you can specify one by adding the attribute "lang" if you have a multilanguage profile to choose which one you want to display.
			<br/>Per example : [linkedinresume lang="fr"] will display the french version of your resume</div>
			<h4>- Url linkedin</h4>
				<blockquote><div>http://www.linkedin.com/in/<input type="text" name="linkedinId" value="<?php _e(apply_filters('format_to_edit',$devOptions['linkedinId']), 'linkedinresume') ?>"/></div>
		<div><input type="submit" name="update_linkedinresumeSettings" value="<?php _e('Update Settings', 'linkedinresume') ?>" /></div></blockquote>
		</form>
		
	</div>
	
	<?php
}

if (!function_exists("linkedinResume_ap")){
	function linkedinResume_ap() {
		if (function_exists('add_options_page')) {
			add_options_page('LinkedIn Resume', 'LinkedIn Resume', 8, basename(__FILE__), 'linkedinresume_printAdminPage');
		}
	}
}

add_action('admin_menu', 'linkedinResume_ap');

?>
