#!/usr/local/bin/php
<?php

@ini_set( 'error_reporting', E_ALL );
@ini_set( 'display_errors', 1 );
@ini_set( 'html_errors', 0 );
@ini_set( 'log_errors', false );

class shell {

	var $modules = array();		// loaded modules
	var $os = 'linux';			// What operating system we're using... I dont test on windows
	var $ps1 = '%u@%n:%c > ';	// The Format of the prompt
	var $prompt;				// (auto) the prompt
	var $stdin;					// (auto) stdin
	var $cwd;					// (auto) current working directory
	var $whoiam;				// (auto) the current user
	var $whatami;				// (auto) unique md5 id
	var $uname;

	function shell($modules=null, $options=array()) { $this->__construct($modules, $options); }
	function __construct($modules=null, $options=array() ) {
		if ( is_array($modules) ) {
			foreach ( $modules as $idx => $object ) {
				if ( is_object($object) ) {
					$this->add_module($object);
				} else if ( !empty($object) ) {
					if ( class_exists($object) ) {
						$this->add_module(new $object());
					}
				}
			}
		} else if ( is_object($modules) ) {
			$this->add_module($modules);
		} else if ( !empty($modules) ) {
			if ( class_exists($modules) )
				$this->add_module(new $modules());
		}
		foreach ( $options as $idx => $val ) {
			$this->$idx = $val;
		}
		$this->stdin = @fopen('php://stdin', 'r');
		$this->whoami();
		$this->getuname();
		$this->whatami=md5($this->whoiam.basename(__FILE__));
		$this->main();
	}

	function whoami() {
		switch( $this->os ) {
			case 'linux':
				$this->whoiam=trim(`whoami`);
				break;
			default:
				$this->whoiam="nobody";
				break;
		}
		return $this->whoiam;
	}

	function getuname() {
		$this->uname = php_uname('n');
		return $this->uname;
	}

	function add_module($object) {
		if ( is_object($object) ) {
			$this->modules[]=$object;
			return true;
		} else {
			return false;
		}
	}

	function checkenv() {
		$this->cwd = getcwd();
		$this->prompt = $this->ps1;
		$this->prompt = str_replace('%u', $this->whoiam, $this->prompt);
		$this->prompt = str_replace('%n', $this->uname, $this->prompt);
		$this->prompt = str_replace('%p', $this->cwd, $this->prompt);
		$this->prompt = str_replace('%c', basename($this->cwd), $this->prompt);
	}

	function prompt() {
		$this->checkenv();
		echo $this->prompt;
	}

	function main() {
		while ( true ) {
			$this->checkenv();
			$cmd = 'set -f && history -r "/tmp/.getline_history-' . 
				$this->whatami . 
				'" && LINE="" ; read -re -p ' . 
				escapeshellarg($this->prompt) . 
				' LINE ; history -s "$LINE" ; history -w "/tmp/.getline_history-' . 
				$this->whatami . 
				'" ; echo $LINE';
			$fp = popen( "/bin/bash -c " . escapeshellarg($cmd), 'r');
			while ( $input = fgets($fp) ) {
				$input=trim($input);
				if ( $input == '' )
					continue;

				$words = explode(' ', $input);
				if ( count($words) > 1 ) {
					$cmd = array_shift( $words );
					$args = implode(' ', $words);
				} else {
					$cmd = $input;
					$args = '';
				}

				$ran = false;

				if ( !$ran && isset($this->modules) && is_array($this->modules) ) {
					foreach ( $this->modules as $idx => $object ) {
						if ( method_exists($this->modules[$idx], $cmd) ) {
							$rval = $this->modules[$idx]->$cmd($args);
							$ran = true;
							break;
						}
					}
				}

				if ( !$ran && method_exists($this, $cmd) ) {
					$rval = $this->$cmd($args);
					$ran = true;
				}

				if ( !$ran ) {
					if ( !ereg('[};]$', $input) )
						$input = $input.';';
					if ( eval( "return true; $input" ) ) {
						$rval = eval( "extract(\$GLOBALS); $input" );
					} else {
						$rval = null;
						echo "WARNING: input failed syntax check!";
					}
				}

				if ( isset( $_SERVER['argv'][1] ) )
					die();

				if ( !preg_match( '/^\s*(var_dump|var_export|printf?|echo|die)\s*\(/', $input ) ) {
					if ( null === $rval ) {
						echo "\r\n";
					} else if ( false === $rval ) {
						echo "FALSE\r\n";
					} else if ( true === $rval ) {
						echo "TRUE\r\n";
					} else {
						var_export( $rval );
						echo "\r\n";
					}
				}

				unset( $rval );
			}
		}
	}

	function parse_input($input) {
	}

}

function shell_preg_color( $preg, $color, $rval, $part='\0', $delim="\n") { 
	return implode( $delim, preg_replace( $preg, shell_color( $color ) . $part . "\033[00m", explode( $delim, $rval ) ) );
}

function shell_color( $color ) {
	$color = str_ireplace( 
		array( 'dark ', 'dark', 'light ', 'light' ),
		array( 'd', 'd', 'l', 'l' ),
		$color
	);
	$color = str_ireplace(
		array(
			',black', ',red',  ',green', ',yellow', ',blue',  ',purple', ',cyan',	',white',							// background colors
			'dgray',  'dgrey', 'lred',	 'lgreen',	'yellow', 'lblue',	 'lpurple', 'lcyan', 'white',					// light foreground colors
			'black',  'red',   'green',	 'brown',	'blue',	  'purple',	 'cyan',	'lgray', 'lgrey', 'gray', 'grey',	// foreground colors
		),
		array(
			';40',	  ';41',   ';42',	 ';43',		';44',	  ';45',	 ';46',		';47',								// background colors
			'1;30',	  '1;30',  '1;31',	 '1;32',	'1;33',	  '1;34',	 '1;35',	'1;36', '1;37',						// light foreground colors
			'30',	  '31',	   '32',	 '33',		'34',	  '35',		 '36',		'37',	'37',	 '37',	  '37',		// foreground colors
		),
		$color
	);
	return "\033[{$color}m";
}

function tabularize($arr, $delim = ' ', $pad_type = STR_PAD_LEFT, $pad_string = ' ') {
	$explode = $lengths = $result = array();
	if ( !is_array($arr) )
		$arr = explode("\n", $arr);
	foreach ( $arr as $i => $row ) {
		// col -1 is array index
		$lengths[-1] = max($lengths[-1], strlen($i));
		$explode[$i] = explode($delim, $row);
		foreach ( $explode[$i] as $col => $val )
			$lengths[$col] = max($lengths[$col], strlen($val));
	}
	foreach ( $explode as $i => $cols ) {
		$i = str_pad($i, $lengths[-1], ' ', STR_PAD_LEFT);
		foreach ( $cols as $col => $val ) {
			if ( is_array($pad_type) ) {
				if ( isset($pad_type[$col]) )
					$_pad_type = $pad_type[$col];
				else
					$_pad_type = end($pad_type);
			} else {
				$_pad_type = $pad_type;
			}
			$result[$i] .= str_pad($val, $lengths[$col], $pad_string, $_pad_type) . ' ';
		}
		$result[$i] = rtrim($result[$i]);
	}
	return $result;
}

$modules = array();

if ( file_exists( dirname( __FILE__ ) . '/wpshell-config.php' ) ) 
	require dirname( __FILE__ ) . '/wpshell-config.php';

$shell = new shell( $modules );

