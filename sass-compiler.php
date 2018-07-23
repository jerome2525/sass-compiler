<?php
define( 'SASS_COMPILER_DIR', dirname(__FILE__));
require SASS_COMPILER_DIR."/scssphp/scss.inc.php";
use Leafo\ScssPhp\Compiler;
class SASS_Compiler {
	private $files = array();
	private $save_format = 'compressed';

	public function add_sass_file( $sass_loc, $sass_file, $save_loc, $save_file ) {

		array_push( $this->files, array(
			'sass_file' => $sass_file,
			'sass_loc'	=> $sass_loc,
			'save_file'	=> $save_file,
			'save_loc'	=> $save_loc
		) );

	}
	public function set_save_format( $save_format ) {
		$this->save_format = $save_format;
	}
	public function execute(){

		foreach( $this->files as $file ) :

			$folder_files = $this->get_dir_files( $file['sass_loc'] );
			$high_ftime = $this->highest_file_time( $folder_files );

			$file_time = filemtime( $file['save_loc'] . DIRECTORY_SEPARATOR . $file['save_file'] );

			if ( $file_time < $high_ftime ) :
			
				$this->convert_sass_to_css(
					$file['sass_loc'], // SASS FILE LOCATION
					$file['sass_file'], // SASS FILE NAME
					$file['save_loc'], // SAVE LOCATION
					$file['save_file'], // SAVE NAME
					$this->save_format // COMPRESS FORMAT
				);
			endif;

		endforeach;

	}
	public function get_dir_files( $dir ) {
		$result = array(); 

		$cdir = scandir($dir); 
		foreach ($cdir as $key => $value) :

			if (!in_array($value,array(".",".."))) :
				$result[] = $dir . DIRECTORY_SEPARATOR . $value;
			endif;

		endforeach;

		return $result; 
	}
	public function highest_file_time( $files ){

		$time = null;
		foreach ($files as $file ) :

			$file_time = filemtime( $file );
			if( $file_time > $time ){
				$time = $file_time;
			}
			
		endforeach;

		return $time;
		
	}
	public function convert_sass_to_css(

		$sass_loc, 
		$sass_file, 
		$save_loc, 
		$css_save_name, 
		$format  = 'compact') {

		switch ( strtolower( $format ) ) {
			case 'compressed':
				$format = 'Leafo\ScssPhp\Formatter\Compressed';
				break;
			case 'outputblock':
				$format = 'Leafo\ScssPhp\Formatter\OutputBlock';
				break;
			case 'crunched':
				$format = 'Leafo\ScssPhp\Formatter\Crunched';
				break;
			case 'expanded':
				$format = 'Leafo\ScssPhp\Formatter\Expanded';
				break;
			case 'nested':
				$format = 'Leafo\ScssPhp\Formatter\Nested';
				break;
			default:				
				$format = 'Leafo\ScssPhp\Formatter\Compact';
				break;
		}
		$scss = new Compiler();

		$scss->setFormatter( $format );

		$scss->setImportPaths( $sass_loc);

		$css = $scss->compile('@import "'.$sass_file.'"');

		$header_comments = "/***************************************************\n\nSASS Origin: ".$sass_file."\nSASS Location: ".$sass_loc."\n\n***************************************************/\n\n\n";
		$css = $header_comments . $css;
		$this->create_css_file( $save_loc, $css_save_name, $css );

	}
	public function create_css_file( 
		$location ,
		$file_name, 
		$css ) {

		$file = fopen( $location . DIRECTORY_SEPARATOR . $file_name, "w");
		fwrite($file, $css);
		fclose($file);
	}
}
?>