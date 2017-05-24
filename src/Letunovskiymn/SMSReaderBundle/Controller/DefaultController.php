<?php

namespace Letunovskiymn\SMSReaderBundle\Controller;

use Letunovskiymn\SMSReaderBundle\Entity\Device;
use Letunovskiymn\SMSReaderBundle\Entity\Message;
use Letunovskiymn\SMSReaderBundle\Telegram\Telegram;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Exception\TelegramLogException;
use Longman\TelegramBot\TelegramLog;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="smsreader_telegram_bot_index")
     */
    public function indexAction()
    {
        $key=$this->getParameter('smsreader_bot_api_key');
        $bot_name=$this->getParameter('smsreader_bot_name');

        // Define a path for your custom commands
        $commands_path = realpath(__DIR__.DIRECTORY_SEPARATOR."..").DIRECTORY_SEPARATOR.
            "TelegramCommand".DIRECTORY_SEPARATOR.
            "UserCommands";

        try {
            // Create Telegram API object
            $telegram =new Telegram($key, $bot_name);

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
            $telegram->addCommandsPath($commands_path,true);


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
     * @Route("/set", name="letunovskiymn_sms_reader_bot_set")
     */
    public function setAction()
    {
        $key=$this->getParameter('smsreader_bot_api_key');
        $bot_name=$this->getParameter('smsreader_bot_name');
        $hook_url=$this->getParameter('smsreader_bot_url'). $this->generateUrl('smsreader_telegram_bot_index');
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
     * @Route("/unset", name="letunovskiymn_ms_reader_bot_unset")
     */
    public function unsetAction()
    {
        $key=$this->getParameter('smsreader_bot_api_key');
        $bot_name=$this->getParameter('smsreader_bot_name');

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

    /**
     * @Route("/reg/{guid}", name="smsreader_register_device")
     */
    public function registerAction($guid=null)
    {
        $result=['guid'=>$guid];
        if ($guid){
            $device = new Device();
            $device->setGuid($guid);
            $em = $this->getDoctrine()->getManager();
            $em->persist($device);

            // actually executes the queries (i.e. the INSERT query)
            $em->flush();
            $result['id']=$device->getId();
        }

        return new JsonResponse($result);
    }


    /**
     * @Route("/get-guid/{guid}", name="smsreader_get_guid_device")
     */
    public function getIdAction($guid=null)
    {
        $result=['guid'=>$guid];
        if ($guid){
            $device = $this->getDoctrine()
                ->getRepository('LetunovskiymnSMSReaderBundle:Device')
                ->findOneBy(['guid'=>$guid]);

            if (!$device) {
                throw $this->createNotFoundException(
                    'No device found for id '.$guid
                );
            }
            $result['id']=$device->getId();
        }

        return new JsonResponse($result);
    }


    /**
     * @Route("/set-message/{guid}", name="smsreader_set_message")
     * @param Request $request
     * @param null $guid
     * @return JsonResponse
     */
    public function setMessageAction(Request $request,$guid=null)
    {
        $result=['guid'=>$guid];
        $postDataMessage = $request->request->get('message',null);
        $postDataFrom= $request->request->get('from',null);

        if ($guid){
            /** @var Device $device */
            $device = $this->getDoctrine()
                ->getRepository('LetunovskiymnSMSReaderBundle:Device')
                ->findOneBy(['guid'=>$guid]);

            if (!$device) {
                throw $this->createNotFoundException(
                    'No device found for id '.$guid
                );
            }
            $message = new Message();
            $message ->setMessage($postDataMessage);
            $message ->setDeviceId($device);
            $message ->setFromUser($postDataFrom);
            $message ->setUpdated();
            $em = $this->getDoctrine()->getManager();
            $em->persist($message);
            $em->flush();

            $result['message_id']=$message->getId();
        }

        return new JsonResponse($result);
    }





}
