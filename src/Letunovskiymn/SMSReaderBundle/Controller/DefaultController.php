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
            $telegram->setContainer($this->getDoctrine());
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
        $iv=bin2hex(random_bytes(8));
        if ($guid){
            $device = $this->getDoctrine()
                ->getRepository('LetunovskiymnSMSReaderBundle:Device')
                ->findOneBy(['guid'=>$guid]);
            if (!$device){
                $device = new Device();
                $device->setGuid($guid);
                $device->setIv($iv);
                $em = $this->getDoctrine()->getManager();
                $em->persist($device);

                // actually executes the queries (i.e. the INSERT query)
                $em->flush();
            }
            $result['id']=$device->getId();
            $result['iv']=$device->getIv();

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

            $key=$this->getParameter('smsreader_bot_api_key');
            $bot_name=$this->getParameter('smsreader_bot_name');
            $telegram =new Telegram($key, $bot_name);
            $text='Есть новое сообщение от '.$postDataFrom.PHP_EOL;
            $text='Чтобы получить введите код в форме ';

            $result = \Longman\TelegramBot\Request::sendMessage(['chat_id' => $device->getChatId(), 'text' => $text]);

        }

        return new JsonResponse($result);
    }


    /**
     * @Route("/validation", name="validation_code")
     * @param Request $request
     *
     */
    public function validCodeAction(Request $request){
//        $defaultData = array('message' => 'Type your message here');


        $form = $this->createFormBuilder()
            ->add('code', TextType::class)
            ->add('email', EmailType::class)
            ->add('message', TextareaType::class)
            ->add('send', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // data is an array with "name", "email", and "message" keys
            $data = $form->getData();
        }
    }



    /**
     * @Route("/decrypt-by-device/{guid}/{messageId}/{key}", name="decrypt_by_device_id_message")
     * @param Request $request
     * @param null $guid
     * @param null $key
     * @return JsonResponse
     */
    public function deCryptByDeviceIdAction(Request $request,$guid=null,$key=null, $messageId=0)
    {
        $result=[];
        if (!empty($key) && !empty($guid) && !empty($messageId))
        {
            $key=$key.$key.$key."z";
            /** @var Device $device */
            $device = $this->getDoctrine()
                ->getRepository('LetunovskiymnSMSReaderBundle:Device')
                ->findOneBy(['guid'=>$guid]);

            if (!$device) {
                throw $this->createNotFoundException(
                    'No device found for id '.$guid
                );
            }

            /** @var Message $message */
            $message = $this->getDoctrine()
                ->getRepository('LetunovskiymnSMSReaderBundle:Message')
                ->findOneBy(['deviceId'=>$device->getId(),'id'=>$messageId]);

            if (!$message) {
                throw $this->createNotFoundException(
                    'Message not found'
                );
            }
            $strSource = openssl_decrypt(base64_decode($message->getMessage()),
                'AES-128-CTR',
                $key,
                true,$device->getIv());
            $result['text']=$strSource;
        }
        return new JsonResponse($result);
    }


    /**
     * @Route("/test-crypt/{str}", name="test_crypt_message")
     * @param Request $request
     * @return JsonResponse
     */
    public function testCryptAction(Request $request,$str=null)
    {
        $key='709717097170971z';
//        $nonce=bin2hex(random_bytes(8));
        $nonce="ccdc84ca5d167831";
        $str = openssl_encrypt("русский текст", 'AES-128-CTR', $key, true,$nonce); // OpenSSL
//        var_dump($str);
//        $str = openssl_decrypt($str, 'AES-256-CTR', $key, true,$nonce); // OpenSSL
        $result=['str'=>base64_encode($str),'nonce'=>$nonce];

//        $result=['str'=>utf8_encode($str),'nonce'=>$nonce];
        return new JsonResponse($result);
    }


    /**
     * @Route("/testSend", name="testSend_message")
     */
    public function testSendAction()
    {

        $key=$this->getParameter('smsreader_bot_api_key');
        $bot_name=$this->getParameter('smsreader_bot_name');
        $telegram =new Telegram($key, $bot_name);
        $result = \Longman\TelegramBot\Request::sendMessage(['chat_id' => "133530807", 'text' => 'Your utf8 text 😜 ...']);


        return new Response();

    }


    /**
     * @Route("/decrypt/{iv}/{key}/{str}", name="decrypt_message")
     * @param Request $request
     * @return JsonResponse
     */
    public function deCryptAction(Request $request,$iv=null,$key=null,$str=null)
    {
        $postDataMessage = base64_decode($str,true);
        $str = openssl_decrypt($postDataMessage, 'AES-256-CTR', $key, true,$iv); // OpenSSL
        $result=['str'=>$str,'str1'=>"Слово за слово",'iv'=>$iv,'key'=>$key];
        return new JsonResponse($result);

    }

}
