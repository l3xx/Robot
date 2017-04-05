<?php
/**
 * Created by PhpStorm.
 * User: LetunovskiyMN
 * Date: 04.04.2017
 * Time: 10:25
 */

namespace Letunovskiymn\KorablikBundle\Controller;
use Letunovskiymn\KorablikBundle\Telegram\Telegram;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Exception\TelegramLogException;
use Longman\TelegramBot\TelegramLog;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Process;


class TelegramBotController extends Controller
{
    /**
     * @Route("/bot", name="letunovskiymn_korablik_telegram_bot")
     */
    public function indexAction()
    {
        // Add you bot's API key and name
        $key=$this->getParameter('korablik_bot_api_key');
        $bot_name=$this->getParameter('korablik_bot_name');

        // Define a path for your custom commands
        $commands_path = realpath(__DIR__.DIRECTORY_SEPARATOR."..").DIRECTORY_SEPARATOR."TelegramCommand".DIRECTORY_SEPARATOR;
//        var_dump($commands_path);
        // Enter your MySQL database credentials
        //$mysql_credentials = [
        //    'host'     => 'localhost',
        //    'user'     => 'dbuser',
        //    'password' => 'dbpass',
        //    'database' => 'dbname',
        //];


        $pathExe=$this->container->get('kernel')->locateResource('@LetunovskiymnKorablikBundle').'Ansible';
//
//        $keyboards[] = new Keyboard(
//            ['7', '8', '9'],
//            ['4', '5', '6'],
//            ['1', '2', '3'],
//            [' ', '0', ' ']
//        );

        $process = new Process('cd '.$pathExe.' && ansible-playbook git.yml -vvvv');
        $process->run();
        var_dump($process->getOutput());

        try {
            // Create Telegram API object
            $telegram =new Telegram($key, $bot_name);
            $telegram->setContainer($this->container);
            // Error, Debug and Raw Update logging
            //Longman\TelegramBot\TelegramLog::initialize($your_external_monolog_instance);
            //Longman\TelegramBot\TelegramLog::initErrorLog($path . '/' . $BOT_NAME . '_error.log');
            //Longman\TelegramBot\TelegramLog::initDebugLog($path . '/' . $BOT_NAME . '_debug.log');
            //Longman\TelegramBot\TelegramLog::initUpdateLog($path . '/' . $BOT_NAME . '_update.log');

            // Enable MySQL
            //$telegram->enableMySql($mysql_credentials);

            // Enable MySQL with table prefix
            //$telegram->enableMySql($mysql_credentials, $BOT_NAME . '_');

            // Add an additional commands path
            $telegram->addCommandsPath($commands_path);

            // Enable admin user(s)
            //$telegram->enableAdmin(your_telegram_id);
            //$telegram->enableAdmins([your_telegram_id, other_telegram_id]);

            // Add the channel you want to manage
            //$telegram->setCommandConfig('sendtochannel', ['your_channel' => '@type_here_your_channel']);

            // Here you can set some command specific parameters,
            // for example, google geocode/timezone api key for /date command:
            //$telegram->setCommandConfig('date', ['google_api_key' => 'your_google_api_key_here']);

            // Set custom Upload and Download path
            //$telegram->setDownloadPath('../Download');
            //$telegram->setUploadPath('../Upload');

            // Botan.io integration
            // Second argument are options
            //$telegram->enableBotan('your_token');
            //$telegram->enableBotan('your_token', ['timeout' => 3]);

            // Requests Limiter (tries to prevent reaching Telegram API limits)
            $telegram->enableLimiter();

            // Handle telegram webhook request
            $telegram->handle();
        } catch (TelegramException $e) {
            // Silence is golden!
            //echo $e;
            // Log telegram errors
           TelegramLog::error($e);
        } catch (TelegramLogException $e) {
            // Silence is golden!
            // Uncomment this to catch log initilization errors
            //echo $e;
        }
        return new Response();
    }


    /**
     * @Route("/set", name="letunovskiymn_korablik_telegram_bot_set")
     */
    public function setAction()
    {
        $key=$this->getParameter('korablik_bot_api_key');
        $bot_name=$this->getParameter('korablik_bot_name');
        $hook_url=$this->getParameter('korablik_bot_url'). $this->generateUrl('letunovskiymn_korablik_telegram_bot');
        echo $hook_url."\n";
        try {
        $telegram = new Telegram($key, $bot_name);

        $result = $telegram->setWebhook($hook_url);

        // Uncomment to use certificate
        //$result = $telegram->setWebhook($hook_url, ['certificate' => $path_certificate]);

        if ($result->isOk()) {
            echo $result->getDescription();
        }
        } catch (TelegramException $e) {
            echo $e;
        }

        return new Response();
    }
    /**
     * @Route("/unset", name="letunovskiymn_korablik_telegram_bot_unset")
     */
    public function unsetAction()
    {
        $key=$this->getParameter('korablik_bot_api_key');
        $bot_name=$this->getParameter('korablik_bot_name');

        try {
            // Create Telegram API object
            $telegram = new Telegram($key, $bot_name);
            // Delete webhook
            $result = $telegram->deleteWebhook();
            if ($result->isOk()) {
                echo $result->getDescription();
            }
        } catch (TelegramException $e) {
            echo $e;
        }
        return new Response();
    }

}