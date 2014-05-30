<?php

namespace \carlosreig\FileFinder;

class FileFinder {

	protected $m_aBlackListRules = array();
	protected $m_aWhiteListRules = array();
	protected $m_sBasePath = false;

	const DIRECTORY_SEPARATOR = '/';
	
	function __construct( $sBasePath = false ) {
		$this->ignoreRegexp( '/^\.$/' ); //. directory
		$this->ignoreRegexp( '/^\.\.$/' ); //.. directory

		if ( $sBasePath )
			$this->m_sBasePath = $sBasePath;
	}
	
	function explore( $path = false ) {
	
		if ( !$path && !$this->m_sBasePath )
			throw new Exception("FileFinder doesn't know where to seach");
		else if ( !$path )
			$path = $this->m_sBasePath;

		if ( !is_dir( $path  ) )
			return;
		
		$aDirContent = scandir( $path );
		$aResult = array();
		
		foreach ( $aDirContent as $content ) {
		
			if ( !$this->hasToIgnoreFile( $content ) ) {
				$newPath = $path . DIRECTORY_SEPARATOR . $content;
				
				if (!is_dir( $newPath ) ) {
					$aResult[] = $this->getRelativePathIfPossible( $newPath );
				}
				else {
					$aResult[ $this->getRelativePathIfPossible( $newPath ) ] = $this->explore( $newPath );
				}
			}
		}

		return $aResult;
	}

	protected function getRelativePathIfPossible( $absPath ) {
		if( $this->m_sBasePath )
			return substr( $absPath, strlen( $this->m_sBasePath ) + 1); //+1 because we want to remove the last /
		else
			return $absPath;
	}
	
	protected function allowRegexp( $regexp ) {
		$this->m_aWhiteListRules[] = $regexp;
	}
	
	protected function ignoreRegexp( $regexp ) {
		$this->m_aBlackListRules[] = $regexp;
	}
	
	protected function hasToIgnoreFile( $file ) {
	
		foreach ( $this->m_aWhiteListRules as $regexp ) {
		
			if ( preg_match( $regexp, $file ) )
				return false;
		}
		
		foreach ( $this->m_aBlackListRules as $regexp ) {
		
			if ( preg_match( $regexp, $file ) )
				return true;
		}
		
		return false;
	}
	
	public function ignoreExtension( $extension ) {
		$this->ignoreRegexp( '/\.' . $extension . '$/' );
	}
	
	public function allowExtension( $extension ) {
		$this->allowRegexp( '/\.' . $extension . '$/' );
	}
	
	public function ignoreWhenFilenameContains( $string, $caseSensitive = true ) {
		
		if ( !$caseSensitive )
			$modificator = 'i';
		else
			$modificator = '';
		
		$this->ignoreRegexp( '/' . preg_quote( $string ) . '/' . $modificator );
	}
	
	public function ignoreWhenFileNameEnds( $string, $caseSensitive = true ) {
	
		if ( !$caseSensitive )
			$modificator = 'i';
		else
			$modificator = '';
	
		$this->ignoreRegexp( '/' . preg_quote( $string ) . '$/' . $modificator );
	}
}