<?php

/**
 * Class file
 *
 * @author Tobias Munk <schmunk@usrbin.de>
 * @link http://www.phundament.com/
 * @copyright Copyright &copy; 2012 diemeisterei GmbH
 * @license http://www.phundament.com/license
 */

namespace config;
use Composer\Script\Event;
use Composer\Script\PackageEvent;

/**
 * ComposerCallback provides composer hooks
 *
 * This setup class triggers `./yiic migrate` at post-install and post-update.
 * For a package the class triggers `./yiic <vendor/<packageName>-<action>` at post-package-install and
 * post-package-update.
 * See composer manual (http://getcomposer.org/doc/articles/scripts.md)
 *
 * Usage example
 *
 * config.php
 *     'params' => array(
        'composer.callbacks' => array(
            'post-update' => array('yiic', 'migrate'),
            'post-install' => array('yiic', 'migrate'),
            'yiisoft/yii-install' => array('yiic', 'webapp', realpath(dirname(__FILE__))),
        ),
    )
);

 * composer.json
 *   "scripts": {
 *      "pre-install-cmd": "config\\ComposerCallback::preInstall",
        "post-install-cmd": "config\\ComposerCallback::postInstall",
        "pre-update-cmd": "config\\ComposerCallback::preUpdate",
        "post-update-cmd": "config\\ComposerCallback::postUpdate",
        "post-package-install": [
        "config\\ComposerCallback::postPackageInstall"
        ],
        "post-package-update": [
        "config\\ComposerCallback::postPackageUpdate"
        ]
    }

 *
 */

defined('YII_PATH') or define('YII_PATH', dirname(__FILE__).'/../../vendor/yiisoft/yii/framework');
defined('CONSOLE_CONFIG') or define('CONSOLE_CONFIG', dirname(__FILE__).'/../../app/config/console.php');

define('YII_DEBUG', true);

// we don't check YII_PATH, since it will be downloaded with composer

if (!is_file(CONSOLE_CONFIG)) {
    throw new \Exception("File '".CONSOLE_CONFIG."' from CONSOLE_CONFIG not found!");
}

class ComposerCallback
{
    /**
     * Displays welcome message
     * @static
     * @param \Composer\Script\Event $event
     */
    public static function preInstall(Event $event)
    {
        $composer = $event->getComposer();
        // do stuff
        echo "Welcome\n\n";
        echo "Installing application...\n\n";

        self::runHook('pre-install');
    }

    /**
     * Executes ./yiic migrate
     * @static
     * @param \Composer\Script\Event $event
     */
    public static function postInstall(Event $event)
    {
        self::runHook('post-install');
        echo "\n\nInstallation completed.\n\n";
    }

    /**
     * Displays welcome message
     *
     * @static
     * @param \Composer\Script\Event $event
     */
    public static function preUpdate(Event $event)
    {
        echo "Welcome\n\nUpdating packages...\n\n";
        self::runHook('pre-update');
    }

    /**
     * Executes ./yiic migrate
     *
     * @static
     * @param \Composer\Script\Event $event
     */
    public static function postUpdate(Event $event)
    {
        self::runHook('post-update');
        echo "\n\nUpdate completed.\n\n";
    }

    /**
     * Executes ./yiic <vendor/<packageName>-<action>
     *
     * @static
     * @param \Composer\Script\PackageEvent $event
     */
    public static function postPackageInstall(PackageEvent $event)
    {
        $installedPackage = $event->getOperation()->getPackage();
        $hookName = $installedPackage->getPrettyName().'-install';
        self::runHook($hookName);
    }

    /**
     * Executes ./yiic <vendor/<packageName>-<action>
     *
     * @static
     * @param \Composer\Script\PackageEvent $event
     */
    public static function postPackageUpdate(PackageEvent $event)
    {
        $installedPackage = $event->getOperation()->getTargetPackage();
        $hookName = $installedPackage->getPrettyName().'-update';
        self::runHook($hookName);
    }

    /**
     * Asks user to confirm by typing y or n.
     *
     * Credits to Yii CConsoleCommand
     *
     * @param string $message to echo out before waiting for user input
     * @return bool if user confirmed
     */
    public static function confirm($message)
    {
        echo $message . ' [yes|no] ';
        return !strncasecmp(trim(fgets(STDIN)), 'y', 1);
    }

    /**
     * Runs Yii command, if available (defined in config/console.php)
     */
    private static function runHook($name){
        $app = self::getYiiApplication();
        if ($app === null) return;

        if (isset($app->params['composer.callbacks'][$name])) {
            echo "composer.callback: ".$name."\n\n";
            $args = $app->params['composer.callbacks'][$name];
            $app->commandRunner->addCommands(\Yii::getPathOfAlias('system.cli.commands'));
            $app->commandRunner->run($args);
        }
    }

    /**
     * Creates console application, if Yii is available
     */
    private static function getYiiApplication()
    {
        if (!is_file(YII_PATH.'/yii.php'))
        {
            return null;
        }

        require_once(YII_PATH . '/yii.php');
        spl_autoload_register(array('YiiBase', 'autoload'));

        if (\Yii::app() === null) {
            if (is_file(CONSOLE_CONFIG)) {                
                $app = \Yii::createConsoleApplication(CONSOLE_CONFIG);
            } else {
                throw new \Exception("File from CONSOLE_CONFIG not found");   
            }            
        } else {
            $app = \Yii::app();
        }
        return $app;
    }

}
