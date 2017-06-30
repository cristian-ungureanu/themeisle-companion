<?php
/**
 * The file that defines autoload class
 *
 * A simple autoloader that loads class files recursively starting in the directory
 * where this class resides.  Additional options can be provided to control the naming
 * convention of the class files.
 *
 * @link        https://themeisle.com
 * @copyright   Copyright (c) 2017, Bogdan Preda
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 *
 * @since       1.0.0
 * @package     Orbit_Fox
 */

/**
 * The Autoloader class.
 *
 * @since      1.0.0
 * @package    Orbit_Fox
 * @author     Themeisle <friends@themeisle.com>
 */
class Autoloader {

	/**
	 * File extension as a string. Defaults to ".php".
	 *
	 * @since   1.0.0
	 * @access  protected
	 * @var     string $file_ext The file extension to look for.
	 */
	protected static $file_ext = '.php';

	/**
	 * The top level directory where recursion will begin. Defaults to the current
	 * directory.
	 *
	 * @since   1.0.0
	 * @access  protected
	 * @var     string $path_top The root directory.
	 */
	protected static $path_top = __DIR__;

	/**
	 * The plugin directory where recursion will begin. Defaults to empty ( No module will be loaded ).
	 *
	 * @since   1.0.0
	 * @access  protected
	 * @var     string $plugins_path The installation plugins directory.
	 */
	protected static $plugins_path = '';

	/**
	 * Holds an array of namespaces to filter in autoloading if set.
	 *
	 * @since   1.0.0
	 * @access  protected
	 * @var array $namespaces The namespace array, used if not empty on autoloading.
	 */
	protected static $namespaces = array();

	/**
	 * An array of files to exclude when looking to autoload.
	 *
	 * @since   1.0.0
	 * @access  protected
	 * @var     array $excluded_files The excluded files list.
	 */
	protected static $excluded_files = array();

	/**
	 * A placeholder to hold the file iterator so that directory traversal is only
	 * performed once.
	 *
	 * @since   1.0.0
	 * @access  protected
	 * @var     RecursiveIteratorIterator $file_iterator Holds an instance of the iterator class.
	 */
	protected static $file_iterator = null;

	/**
	 * Autoload function for registration with spl_autoload_register
	 *
	 * Looks recursively through project directory and loads class files based on
	 * filename match.
	 *
	 * @since   1.0.0
	 * @access  public
	 * @param   string $class_name The class name requested.
	 * @return mixed
	 */
	public static function loader( $class_name ) {
	    $directory = new RecursiveDirectoryIterator( static::$path_top, RecursiveDirectoryIterator::SKIP_DOTS );

		if ( is_null( static::$file_iterator ) ) {
			static::$file_iterator = new RecursiveIteratorIterator( $directory, RecursiveIteratorIterator::LEAVES_ONLY );
		}

		if ( ! empty( static::$namespaces ) ) {
		    $found = false;
			foreach ( static::$namespaces as $namespace ) {
                if ( $namespace == 'OBFX_Module' && substr( $class_name, strlen( $namespace ) * (-1), strlen( $namespace ) ) == $namespace ) {
                    return static::module_loader( $class_name );
                }
				if ( substr( $class_name, 0, strlen( $namespace ) ) == $namespace ) {
					$found = true;
				}
			}
			if ( ! $found ) {
				return $found;
			}
		}

		$filename = 'class-' . str_replace( '_', '-', strtolower( $class_name ) ) . static::$file_ext;
		foreach ( static::$file_iterator as $file ) {
			if ( strtolower( $file->getFilename() ) === strtolower( $filename ) && $file->isReadable() ) {
                include_once $file->getPathname();
                return true;
			}
		}
	}

	/**
	 * Method used for loading required module init file.
	 *
	 * @since   1.0.0
	 * @access  public
	 * @param   string $class_name The class name requested.
	 * @return bool
	 */
	public static function module_loader( $class_name ) {
		$module_name = str_replace( '_', '-', strtolower( str_replace( '_OBFX_Module', '', $class_name ) ) );
		if ( static::$plugins_path != '' ) {
			$plugin_directory = new RecursiveDirectoryIterator( static::$plugins_path, RecursiveDirectoryIterator::SKIP_DOTS );
			$file_iterator = new RecursiveIteratorIterator( $plugin_directory, RecursiveIteratorIterator::LEAVES_ONLY );
			$filename = 'init.php';
			foreach ( $file_iterator as $file ) {
				if ( in_array( 'obfx_modules', explode( DIRECTORY_SEPARATOR, $file->getPathname() ) ) && in_array( $module_name, explode( DIRECTORY_SEPARATOR, $file->getPathname() ) ) ) {
					if ( strtolower( $file->getFilename() ) === strtolower( $filename ) ) {
						if ( $file->isReadable() ) {
							include_once $file->getPathname();
							return true;
						}
					}
				}
			}
		}
		return false;
	}

	/**
	 * Sets the $file_ext property
	 *
	 * @since   1.0.0
	 * @access  public
	 * @param   string $file_ext The file extension used for class files.  Default is "php".
	 */
	public static function set_file_ext( $file_ext ) {
		static::$file_ext = $file_ext;
	}

	/**
	 * Sets the $plugins_path property
	 *
	 * @since   1.0.0
	 * @access  public
	 * @param   string $path The path representing the top level where recursion should
	 *                       begin for plugins. Defaults to empty ( does not look in plugins ).
	 */
	public static function set_plugins_path( $path ) {
		static::$plugins_path = $path;
	}

	/**
	 * Sets the $path property
	 *
	 * @since   1.0.0
	 * @access  public
	 * @param   string $path The path representing the top level where recursion should
	 *                       begin. Defaults to the current directory.
	 */
	public static function set_path( $path ) {
		static::$path_top = $path;
	}

	/**
	 * Adds a new file to the exclusion list.
	 *
	 * @since   1.0.0
	 * @access  public
	 * @param   string $file_name The file name to exclude from autoload.
	 */
	public static function exclude_file( $file_name ) {
	    static::$excluded_files[] = $file_name;
	}

	/**
	 * Sets the namespaces used in autoloading if any.
	 *
	 * @since   1.0.0
	 * @access  public
	 * @param   array $namespaces The namespaces to use.
	 */
	public static function define_namespaces( $namespaces = array() ) {
	    static::$namespaces = $namespaces;
	}
}