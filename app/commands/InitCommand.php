<?php
/**
 * WebAppCommand class file.
 *
 */

/**
 * WebAppCommand creates an Yii Web application at the specified location.
 *
 * Based on Yii Web App Command, modified for Eyeframe
 *
 */
Yii::import('system.cli.commands.*');

class InitCommand extends CConsoleCommand
{
    protected $colors = false;

    public function run($args) {

    	// Choosing action
    	if (!isset($args[0])) {
    		$this->usageError('Unsupported VCS specified.');
     	} else {
     		$action = array_shift($args);
     	}

     	$method = 'action' . ucfirst($action);

     	if (method_exists($this, $method)) {
     		$this->$method($args);
     	}

    }	

    protected function actionCreate($args) {                        
        while (true) {
            if (false !== ($dbInfo = $this->dbInit())) {
                $this->outputMsg('Connection successful', 'green', 'black');
                break;
            } else {
                $this->outputMsg('DB Connection failed', 'black', 'red');                

                if (!$this->confirm('Try again?', true)) {
                    exit;
                }
            }   
        }

        list($dbHost, $dbUser, $dbPassword, $dbName) = $dbInfo;
        
        $this->createConfigs($dbHost, $dbUser, $dbPassword, $dbName);
    }

    /**
     * Getting information about database
     * @return array [dbHost, dbUser, dbPassword, dbName]
     */
    protected function dbInit() {
        $enterDetails = true;

        if ($enterDetails) {
            echo 'DB host: ';
            $dbHost = trim(fgets(STDIN));

            echo 'DB user: ';
            $dbUser = trim(fgets(STDIN));

            echo 'DB password: ';
            $dbPassword = trim(fgets(STDIN));
        }

        echo 'DB name:';
        $dbName = trim(fgets(STDIN));

        $dsn = 'mysql:host=' . $dbHost . ';dbname=' . $dbName;

        $connError = false;

        try {
            $conn = new CDbConnection($dsn, $dbUser, $dbPassword);
            $conn->init();
            $conn->setActive(false);
        } catch (Exception $e) {
            $connError = true;
        }

        return $connError ? false : array($dbHost, $dbUser, $dbPassword, $dbName);
    }

    /**
     * Creates enviroment config file
     * @return void
     */
    protected function createConfigs($dbHost, $dbUser, $dbPassword, $dbName) {
        /**
         * Function for creating enviroment configuration file
         */
        $createServerEnviroment = function($dbHost, $dbUser, $dbPassword, $dbName, $params = []) {
            $data = [
                 'dbHost' => $dbHost,
                 'dbUser' => $dbUser,
                 'dbPassword' => $dbPassword,
                 'dbName' => $dbName
             ];

            $data = array_merge($data, $params);

            $srcFile = 'config/database.php';
            $destFile = 'config/database.php';
            $this->createFileUsePattern($srcFile, $destFile, $data);           
        };

        if ($this->confirm('Create config file (config/database.php)?')) {
            $createServerEnviroment($dbHost, $dbUser, $dbPassword, $dbName);
        }
    }

    protected function outputMsg($msg, $foregroundColor = 'white', $backgroundColor = 'black') {
        if (!$this->colors) {
            $this->colors = Colors::get();
        }

        echo $this->colors->getColoredString($msg, $foregroundColor, $backgroundColor), PHP_EOL;
    }

    /**
     * Creates file into app using one of the patterns
     * @param  string $src  path to pattern (relative path, f.e.: (src/patterns/) config/server.vdev.php)
     * @param  string $dest path to new file (relative path, f.e.: (app/) config/server.vdev.php)
     * @param  array  $data list of variables for pattern
     * @return boolean result of file creation
     */
    protected function createFileUsePattern($src, $dest, $data) {
        $srcFile = dirname(__FILE__) . '/../../src/patterns/' . $src;
        $destFile =  dirname(__FILE__) . '/../'. $dest;

        if (!file_exists($srcFile)) {            
            $this->outputMsg('File "src/patterns/'.$src.'" doesn\'t exists!', "black", "red");
            exit;
        }

        foreach ($data as $_name => $_value) {
            $$_name = $_value;
        }

        $content = require($srcFile);
        file_put_contents($destFile, $content);
        chmod($destFile, 0777);

        $this->outputMsg('File "app/'.$dest.'" created!', 'green', 'black');
        return true;
    }
}

class Colors
{
    private $foreground_colors = array();
    private $background_colors = array();
    protected static $_INSTANCE;

    private function __construct()
    {
        // Set up shell colors
        $this->foreground_colors['black'] = '0;30';
        $this->foreground_colors['dark_gray'] = '1;30';
        $this->foreground_colors['blue'] = '0;34';
        $this->foreground_colors['light_blue'] = '1;34';
        $this->foreground_colors['green'] = '0;32';
        $this->foreground_colors['light_green'] = '1;32';
        $this->foreground_colors['cyan'] = '0;36';
        $this->foreground_colors['light_cyan'] = '1;36';
        $this->foreground_colors['red'] = '0;31';
        $this->foreground_colors['light_red'] = '1;31';
        $this->foreground_colors['purple'] = '0;35';
        $this->foreground_colors['light_purple'] = '1;35';
        $this->foreground_colors['brown'] = '0;33';
        $this->foreground_colors['yellow'] = '1;33';
        $this->foreground_colors['light_gray'] = '0;37';
        $this->foreground_colors['white'] = '1;37';

        $this->background_colors['black'] = '40';
        $this->background_colors['red'] = '41';
        $this->background_colors['green'] = '42';
        $this->background_colors['yellow'] = '43';
        $this->background_colors['blue'] = '44';
        $this->background_colors['magenta'] = '45';
        $this->background_colors['cyan'] = '46';
        $this->background_colors['light_gray'] = '47';
    }

    /**
     * @static
     * @return Colors
     */
    public static function get()
    {
        if (is_object(self::$_INSTANCE)) {
            $obj = self::$_INSTANCE;
        }
        else {
            $obj = new Colors();
        }
        return $obj;
    }

    /**
     * @param  $string
     * @param null $foreground_color
     * @param null $background_color
     * @return string Colored string
     */
    public function getColoredString($string, $foreground_color = null, $background_color = null)
    {
        $colored_string = "";

        // Check if given foreground color found
        if (isset($this->foreground_colors[$foreground_color])) {
            $colored_string .= "\033[" . $this->foreground_colors[$foreground_color] . "m";
        }
        // Check if given background color found
        if (isset($this->background_colors[$background_color])) {
            $colored_string .= "\033[" . $this->background_colors[$background_color] . "m";
        }

        // Add string and end coloring
        $colored_string .= $string . "\033[0m";

        return $colored_string;
    }

    /**
     * @return array All foreground color names
     */
    public function getForegroundColors()
    {
        return array_keys($this->foreground_colors);
    }

    /**
     * @return array All background color names
     */
    public function getBackgroundColors()
    {
        return array_keys($this->background_colors);
    }
}

?>