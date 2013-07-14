<?php

class wpreference {

	var $fileindexes = array();
	
	function d( $n ) {
		$indexes = $this->bigfindex();
		if ( !ctype_digit( $n ) )
			$results = (array)preg_grep( '/^'.$n.'\|/i', $indexes['functions'] );
		else
			$results = array( $n => $indexes['functions'][$n] );
		$found=0;
		foreach ( array_keys($results) as $i ) {
			$found++;
			if ( count($results) > 1 )
				echo "// Search result #$found\n";
			$f = explode( '|', $indexes['functions'][$i] );
			echo rtrim( implode( '', array_slice( file( $f[1] ), $f[2]-1, 1 ) ) );
			if ( $found < count($results) )
				echo "\n\n";
		}

	}

	function f( $n ) {
		if ( ctype_digit( $n ) )
			return $this->fprint( $n );
		$indexes = $this->bigfindex();
		$results = (array)preg_grep( '/^'.$n.'\|/i', $indexes['functions'] );
		$found=0;
		foreach ( array_keys($results) as $i ) {
			$found++;
			if ( count($results) > 1 )
				echo "// Search result #$found\n";
			$this->fprint($i);
			if ( $found < count($results) )
				echo "\n\n";
		}
	}

	function fprint( $n ) {
		$indexes = $this->bigfindex();
		echo "// {$indexes['functions'][$n]}\n";
		$f = explode( '|', $indexes['functions'][$n] );
		echo rtrim( implode( '', array_slice( file( $f[1] ), $f[2]-1, ($f[3]-$f[2])+2 ) ) );
	}

	function fsearch( $func ) {
		$indexes = $this->bigfindex();
		$results = preg_grep( '/^[^|]*'.$func.'[^|]*\|/i', $indexes['functions'] );
		$results = str_replace( getcwd().'/', '', $results );
		$results = preg_grep( '/\|\//', $results, PREG_GREP_INVERT );
		print_r( tabularize( $results, '|', array(STR_PAD_RIGHT, STR_PAD_RIGHT, STR_PAD_LEFT) ) );
	}

	function cprint( $n ) {
		$indexes = $this->bigfindex();
		$c = explode( '|', $indexes['classes'][$n] );
		echo trim( implode( '', array_slice( file( $c[1] ), $c[2]-1, ($c[3]-$c[2])+2 ) ) );
	}

	function csearch( $class ) {
		$indexes = $this->bigfindex();
		$results = preg_grep( '/^[^|]*'.$class.'[^|]*\|/i', $indexes['classes'] );
		$results = str_replace( getcwd().'/', '', $results );
		$results = preg_grep( '/\|\//', $results, PREG_GREP_INVERT );
		print_r( tabularize( $results, '|', array(STR_PAD_RIGHT, STR_PAD_RIGHT, STR_PAD_LEFT) ) );
	}
	
	function c( $n ) {
		if ( ctype_digit( $n ) )
			return $this->fprint( $n );
		$indexes = $this->bigfindex();
		$results = (array)preg_grep( '/^'.$n.'\|/i', $indexes['classes'] );
		$found=0;
		foreach ( array_keys($results) as $i ) {
			$found++;
			if ( count($results) > 1 )
				echo "// Search result #$found\n";
			$this->cprint($i);
			if ( $found < count($results) )
				echo "\n\n";
		}
	}

	function bigfindex() {
		if ( count( $this->file_indexes ) )
			return $this->file_indexes;
		foreach ( get_included_files() as $file ) {
			$this->findex( $file );
		}
		return $this->file_indexes;
	}

	function findex( $file ) {
		$tokens = token_get_all( file_get_contents( $file ) );
		
		$next_is_func = false;
		$next_is_class = false;

		$classes     = array();
		$class_stack = array();
		$current_class = '';

		$functions   = array();
		$func_stack  = array();
		$current_function = '';

		$curly_level = 0;

		foreach ( $tokens as $token ) { 
			if ( ( $next_is_func || $next_is_class ) && is_array( $token ) && token_name( $token[0] ) == 'T_WHITESPACE' )
				continue;
			if ( is_array( $token ) )
				$current_line = $token[2];
			else if ( $token == '&' )
				continue;
			if ( $next_is_func ) {
				$next_is_func = false;
				$current_function = $token[1];
				if ( $current_class )
					$current_function = "$current_class::$current_function";
				$func_stack[] = $current_function;
				if ( !isset( $functions[$current_function] ) )
					$functions[$current_function] = array( 'start' => $current_line, 'stop' => null, 'depth' => 0 );
			} else if ( $next_is_class ) {
				$next_is_class = false;
				$current_class = $token[1];
				$class_stack[] = $current_class;
				if ( !isset( $classes[$current_class] ) )
					$classes[$current_class] = array( 'start' => $current_line, 'stop' => null, 'depth' => 0 );
			}
			if ( is_array( $token ) && $token[1] == '}' ) $token = '}';
			if ( is_array( $token ) && $token[1] == '{' ) $token = '{';
			if ( !is_array($token) ) {
				if ( $token == '{' ) {
					if ( $current_class )
						$classes[$current_class]['depth']++;
					if ( $current_function )
						$functions[$current_function]['depth']++;
					$curly_level++;
				}
				if ( $token == '}' ) {
					if ( $current_class ) {
						$classes[$current_class]['depth']--;
						$classes[$current_class]['stop'] = $current_line;
						if ( $classes[$current_class]['depth'] == 0 ) {
							unset( $classes[$current_class]['depth'] );
							array_pop( $class_stack );
							if ( count( $class_stack ) )
								$current_class = $class_stack[ (count($class_stack)-1) ];
							else
								$current_class = '';
						}
					}
					if ( $current_function ) {
						$functions[$current_function]['depth']--;
						$functions[$current_function]['stop'] = $current_line;
						if ( $functions[$current_function]['depth'] < 1 ) {
							unset( $functions[$current_function]['depth'] ); 
							array_pop( $func_stack );
							if ( count( $func_stack ) )
								$current_function = $func_stack[ (count($func_stack)-1) ];
							else
								$current_function = '';
						}
					}
					$curly_level--;
				}
				continue;
			}
			$tname = token_name( $token[0] );
			switch( $tname ) {
				case 'T_CLASS':
					$next_is_class = true;
					break;
				case 'T_FUNCTION':
					$next_is_func = true;
					break;
			}
		}

		if ( !isset( $this->file_indexes['classes'] ) )
			$this->file_indexes['classes'] = array();
		foreach ( $classes as $class => $info ) {
			$new = "$class|$file|{$info['start']}|{$info['stop']}";
			if ( !in_array( $new, $this->file_indexes['classes'] ) )
				$this->file_indexes['classes'][] = $new;
		}

		if ( !isset( $this->file_indexes['functions'] ) )
			$this->file_indexes['functions'] = array();
		foreach ( $functions as $func => $info ) {
			$new = "$func|$file|{$info['start']}|{$info['stop']}";
			if ( !in_array( $new, $this->file_indexes['functions'] ) )
				$this->file_indexes['functions'][] = $new;
		}

		sort( $this->file_indexes['functions'] );
		sort( $this->file_indexes['classes'] );

		return $this->file_indexes;		
	}

}

$modules[] = new wpreference();
