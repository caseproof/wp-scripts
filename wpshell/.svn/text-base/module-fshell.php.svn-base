<?php

class fshell {

	function quit($args) {
		exit;
	}

	function cd($args) {
		chdir(realpath($args));
	}

	function ls($args) {
		if ($args == '' )
			$args = '*';
		foreach ( glob($args) as $entry ) {
			echo "$entry\r\n";
		}
	}

	function reload($args) {
		exit(1);
	}

}

$modules[] = new fshell();
