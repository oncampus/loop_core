<?php
/**
 * Template used when there is no LocalSettings.php file.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 * @ingroup Templates
 */

if ( !defined( 'MEDIAWIKI' ) ) {
	die( "NoLocalSettings.php is not a valid MediaWiki entry point\n" );
}

if ( !isset( $wgVersion ) ) {
	$wgVersion = 'VERSION';
}

# bug 30219 : can not use pathinfo() on URLs since slashes do not match
$matches = array();
$ext = 'php';
$path = '/';
foreach( array_filter( explode( '/', $_SERVER['PHP_SELF'] ) ) as $part ) {
	if( !preg_match( '/\.(php5?)$/', $part, $matches ) ) {
		$path .= "$part/";
	} else {
		$ext = $matches[1] == 'php5' ? 'php5' : 'php';
	}
}

# Check to see if the installer is running
if ( !function_exists( 'session_name' ) ) {
	$installerStarted = false;
} else {
	session_name( 'mw_installer_session' );
	$oldReporting = error_reporting( E_ALL & ~E_NOTICE );
	$success = session_start();
	error_reporting( $oldReporting );
	$installerStarted = ( $success && isset( $_SESSION['installData'] ) );
}



if (!$installerStarted) {

$q = $_SERVER['SERVER_NAME'];
#$q=$_GET['q'];
$q=explode('.', $q);
$hashtag = $q[0];
$domain = $q[1].'.'.$q[2];

$params=array();
define('LDAP_salt','jf|w6[PUa60A2D|lH&hz!]^w,|QS;-UMQWtP3R -uAa!`F$)Ws4J1{/@9^UZFy(q');
$params['token'] = md5(LDAP_salt.$hashtag);

$params['hashtag'] = $hashtag;
$params['domain'] = $domain;

$url = "https://moodalis.oncampus.de/admin/service.php";
#$url = "https://moodalis2.oncampus.de/admin/service.php";
$postfields = array(
	"lang"=>"de",
	"username"=>"admin",
	"password"=>"dA-PfocMoOdalis!",
	"TRIGGER_login"=>"1",
	"host"=>"webservice",
	"svc"=>"func",
	"func"=>"loop_exist",
	"ret"=>"phpa",
	"report"=>"1_",
	"elevel"=>"4_",
	"service_params"=>array(0=>$params)
);
$postfields = http_build_query($postfields);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,$url);
curl_setopt($ch, CURLOPT_FAILONERROR, 1);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch, CURLOPT_TIMEOUT, 3);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);

$cookiefile = '/tmp/'.uniqid().'cookies.tmp';
curl_setopt ($ch, CURLOPT_COOKIEFILE, $cookiefile);
curl_setopt ($ch, CURLOPT_COOKIEJAR, $cookiefile);

$result = curl_exec($ch);

curl_close($ch);
unlink($cookiefile);

$tmp = (array) unserialize($result);
$webservice_result = $tmp["webservice/func"];

if ($webservice_result) {
	?>
	<html><head></head><body><h2>Bitte haben Sie noch ein wenig Geduld.</h2>
<br/>
Sie werden von uns benachrichtigt, sobald Ihr persönliches LOOP konfiguriert wurde. Danach können Sie sofort starten. Schauen Sie doch gleich in die sozialen Netzwerke oder informieren Sie sich mit unseren Videos über die Möglichkeiten von LOOP:
</body></html>
	<?php
} else {
?><html><head><meta http-equiv="refresh" content="0; URL=http://newloop.oncampus.de"></head><body></body></html><?php
	exit(0);
}


} else {
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
	<head>
		<meta charset="UTF-8" />
		<title>MediaWiki <?php echo htmlspecialchars( $wgVersion ) ?></title>
		<style media='screen'>
			html, body {
				color: #000;
				background-color: #fff;
				font-family: sans-serif;
				text-align: center;
			}

			h1 {
				font-size: 150%;
			}
		</style>
	</head>
	<body>
		<img src="<?php echo htmlspecialchars( $path ) ?>skins/common/images/mediawiki.png" alt='The MediaWiki logo' />

		<h1>MediaWiki <?php echo htmlspecialchars( $wgVersion ) ?></h1>
		<div class='error'>
		<p>LocalSettings.php not found.</p>
		<p>
		<?php
		if ( $installerStarted ) {
			echo "Please <a href=\"" . htmlspecialchars( $path ) . "mw-config/index." . htmlspecialchars( $ext ) . "\"> complete the installation</a> and download LocalSettings.php.";
		} else {
			echo "Please <a href=\"" . htmlspecialchars( $path ) . "mw-config/index." . htmlspecialchars( $ext ) . "\"> set up the wiki</a> first.";
		}
		?>
		</p>

		</div>
	</body>
</html>
<?php
}
?>