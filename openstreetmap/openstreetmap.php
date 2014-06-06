<?php
/**
 * Name: OpenStreetMap
 * Description: Use OpenStreetMap for displaying locations.  After activation the post location just beneath your avatar in your posts will link to openstreetmap.
 * Version: 1.1
 * Author: Mike Macgirvin <http://macgirvin.com/profile/mike>
 * Author: Klaus Weidenbach
 *
 */

function openstreetmap_load() {
	register_hook('render_location', 'addon/openstreetmap/openstreetmap.php', 'openstreetmap_location');
	register_hook('page_header', 'addon/openstreetmap/openstreetmap.php', 'openstreetmap_alterheader');

	logger("installed openstreetmap");
}

function openstreetmap_unload() {
	unregister_hook('render_location', 'addon/openstreetmap/openstreetmap.php', 'openstreetmap_location');
	unregister_hook('page_header', 'addon/openstreetmap/openstreetmap.php', 'openstreetmap_alterheader');

	logger("removed openstreetmap");
}

function openstreetmap_alterheader($a, &$navHtml) {
	$addScriptTag='<script type="text/javascript" src="' . $a->get_baseurl() . '/addon/openstreetmap/openstreetmap.js' . '"></script>' . "\r\n";
	$a->page['htmlhead'] .= $addScriptTag;
}

function openstreetmap_location($a, &$item) {

	//

	if(! (strlen($item['location']) || strlen($item['coord'])))
		return;

	/*
	 * Get the configuration variables from the .htconfig file.
	 * Separate the coordinate server from the text-string to map tile server since they apparently use 
	 * different URL conventions.
	 */

	$tmsserver = get_config('openstreetmap','tmsserver');
	if(! $tmsserver)
		$tmsserver = 'http://nominatim.openstreetmap.org';

	$coordserver = get_config('openstreetmap','coordserver');
	if(! $coordserver)
		$coordserver = 'http://openstreetmap.org';


	$zoom = get_config('openstreetmap','zoom');
	if(! $zoom)
		$zoom = 17;

	$location = '';
	$coord = '';

	
	$location = $item['location'];

	$location = (($location && (! $item['coord'])) ? '<a target="map" title="' . $item['location'] . '" href="'.$tmsserver.'?q=' . urlencode($item['location']) . '">' . $item['location'] . '</a>' : $location);

	if($item['coord']) {
		$coords = explode(' ', $item['coord']);
		if(count($coords) > 1) {
			$coord = '<a target="map" class="OSMMapLink" title="' . $item['coord'] . '" href="'.$coordserver.'?lat=' . urlencode($coords[0]) . '&lon=' . urlencode($coords[1]) . '&zoom='.$zoom.'">Map</a>' ;
		}
	}
	if(strlen($coord)) {
		if($location)
			$location .= '&nbsp;<span class="smalltext">(' . $coord . ')</span>';
		else
			$location = '<span class="smalltext">' . $coord . '</span>';
	}
	$item['html'] = $location;
	return;
}


function openstreetmap_plugin_admin (&$a, &$o) {
	$t = get_markup_template( "admin.tpl", "addon/openstreetmap/" );
	$tmsserver = get_config('openstreetmap','tmsserver');
	if(! $tmsserver)
		$tmsserver = 'http://openstreetmap.org';
	$zoom = get_config('openstreetmap','zoom');
	if(! $zoom)
		$zoom = 17;

	$o = replace_macros( $t, array(
			'$submit' => t('Submit'),
			'$tmsserver' => array('tmsserver', t('Tile Server URL'), $tmsserver, t('A list of <a href="http://wiki.openstreetmap.org/wiki/TMS" target="_blank">public tile servers</a>')),
			'$zoom' => array('zoom', t('Default zoom'), $zoom, t('The default zoom level. (1:world, 18:highest)')),
	));
}
function openstreetmap_plugin_admin_post (&$a) {
	$url = ((x($_POST, 'tmsserver')) ? notags(trim($_POST['tmsserver'])) : '');
	$zoom = ((x($_POST, 'zoom')) ? intval(trim($_POST['zoom'])) : '17');
	set_config('openstreetmap', 'tmsserver', $url);
	set_config('openstreetmap', 'zoom', $zoom);
	info( t('Settings updated.'). EOL);
}
