#!/usr/bin/env php
<?php
namespace be\bastelstu\wcf\chromaHash;
/**
 * Builds ChromaHash
 *
 * @author 	Tim Düsterhus
 * @copyright	2010-2013 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	be.bastelstu.wcf.chromaHash
 */
$packageXML = file_get_contents('package.xml');
preg_match('/<version>(.*?)<\/version>/', $packageXML, $matches);
echo "Building ChromaHash $matches[1]\n";
echo str_repeat("=", strlen("Building ChromaHash $matches[1]"))."\n";

echo <<<EOT
Cleaning up
-----------

EOT;
	if (file_exists('package.xml.old')) {
		file_put_contents('package.xml', file_get_contents('package.xml.old'));
		unlink('package.xml.old');
	}
	if (file_exists('file.tar')) unlink('file.tar');
	if (file_exists('be.bastelstu.wcf.chromaHash.tar')) unlink('be.bastelstu.wcf.chromaHash.tar');
echo <<<EOT

Fetching submodule
------------------

EOT;
	passthru('git submodule init', $code);
	if ($code != 0) exit($code);
	passthru('git submodule update', $code);
	if ($code != 0) exit($code);
echo <<<EOT

Checking PHP for Syntax Errors
------------------------------

EOT;
	chdir('file');
	$check = null;
	$check = function ($folder) use (&$check) {
		if (is_file($folder)) {
			if (substr($folder, -4) === '.php') {
				passthru('php -l '.escapeshellarg($folder), $code);
				if ($code != 0) exit($code);
			}
			
			return;
		}
		$files = glob($folder.'/*');
		foreach ($files as $file) {
			$check($file);
		}
	};
	$check('.');
echo <<<EOT

Building file.tar
-----------------

EOT;
	passthru('tar cvf ../file.tar * --exclude=.git', $code);
	if ($code != 0) exit($code);
echo <<<EOT

Building be.bastelstu.wcf.chromaHash.tar
----------------------------------

EOT;
	chdir('..');
	file_put_contents('package.xml.old', file_get_contents('package.xml'));
	file_put_contents('package.xml', preg_replace('~<date>\d{4}-\d{2}-\d{2}</date>~', '<date>'.date('Y-m-d').'</date>', file_get_contents('package.xml')));
	passthru('tar cvf be.bastelstu.wcf.chromaHash.tar * --exclude=*.old --exclude=file --exclude=build.php', $code);
	if (file_exists('package.xml.old')) {
		file_put_contents('package.xml', file_get_contents('package.xml.old'));
		unlink('package.xml.old');
	}
	if ($code != 0) exit($code);

if (file_exists('file.tar')) unlink('file.tar');

