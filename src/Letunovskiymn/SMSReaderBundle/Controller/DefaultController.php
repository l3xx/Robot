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
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="smsreader_telegram_bot_index")
     */
    public function indexAction()
    {
        $key = $this->getParameter('smsreader_bot_api_key');
        $bot_name = $this->getParameter('smsreader_bot_name');

        // Define a path for your custom commands
        $commands_path = realpath(__DIR__ . DIRECTORY_SEPARATOR . "..") . DIRECTORY_SEPARATOR .
            "TelegramCommand" . DIRECTORY_SEPARATOR .
            "UserCommands";

        try {
            // Create Telegram API object
            $telegram = new Telegram($key, $bot_name);
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
            $telegram->addCommandsPath($commands_path, true);


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
        $key = $this->getParameter('smsreader_bot_api_key');
        $bot_name = $this->getParameter('smsreader_bot_name');
        $hook_url = $this->getParameter('smsreader_bot_url') . $this->generateUrl('smsreader_telegram_bot_index');
        echo $hook_url . "\n";
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
        $key = $this->getParameter('smsreader_bot_api_key');
        $bot_name = $this->getParameter('smsreader_bot_name');

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
    public function registerAction($guid = null)
    {
        $result = ['guid' => $guid];
        $iv = bin2hex(random_bytes(8));
        if ($guid) {
            $device = $this->getDoctrine()
                ->getRepository('LetunovskiymnSMSReaderBundle:Device')
                ->findOneBy(['guid' => $guid]);
            if (!$device) {
                $device = new Device();
                $device->setGuid($guid);
                $device->setIv($iv);
                $em = $this->getDoctrine()->getManager();
                $em->persist($device);

                // actually executes the queries (i.e. the INSERT query)
                $em->flush();
            }
            $result['id'] = $device->getId();
            $result['iv'] = $device->getIv();

        }

        return new JsonResponse($result);
    }

    /**
     * @Route("/set-message/{guid}", name="smsreader_set_message")
     * @param Request $request
     * @param null $guid
     * @return JsonResponse
     */
    public function setMessageAction(Request $request, $guid = null)
    {
        $result = ['guid' => $guid];
        $postDataMessage = $request->request->get('message', null);
        $postDataFrom = $request->request->get('from', null);
        $postDataHash = $request->request->get('hash', null);

        if ($guid) {
            /** @var Device $device */
            $device = $this->getDoctrine()
                ->getRepository('LetunovskiymnSMSReaderBundle:Device')
                ->findOneBy(['guid' => $guid]);

            if (!$device) {
                throw $this->createNotFoundException(
                    'No device found for id ' . $guid
                );
            }

            $postDataMessage = trim(str_replace("\n", "", $postDataMessage));

            $message = new Message();
            $message->setMessage($postDataMessage);
            $message->setDeviceId($device);
            $message->setFromUser($postDataFrom);
            $message->setHash($postDataHash);
            $message->setDeleteHash(null);
            $message->setCount(0);
            $message->setUpdated();
            $em = $this->getDoctrine()->getManager();
            $em->persist($message);
            $em->flush();
            $result['message_id'] = $message->getId();
        }

        return new JsonResponse($result);
    }

    /**
     * @Route("/validation/{messageId}", name="validation_code")
     * @param Request $request
     *
     * @param null $messageId
     * @return Response
     */
    public function validCodeAction(Request $request, $messageId = null)
    {
//        $defaultData = array('message' => 'Type your message here');
        /** @var Message $message */
        $message = $this->getDoctrine()
            ->getRepository('LetunovskiymnSMSReaderBundle:Message')
            ->findOneBy(['id' => $messageId,
                'delete_hash' => null]);
        if (!$message) {
            throw $this->createNotFoundException(
                'Message not found'
            );
        }

        /** @var Form $form */
        $form = $this->createFormBuilder()
            ->add('code', IntegerType::class)
            ->add('send', SubmitType::class)
            ->getForm();
        $form->handleRequest($request);
        $success = '';
        $messageSend = '';
        if ($form->isSubmitted() && $form->isValid()) {
            $key = $this->getParameter('smsreader_bot_api_key');
            $bot_name = $this->getParameter('smsreader_bot_name');
            $telegram = new Telegram($key, $bot_name);

            $data = $form->getData();
            $text = $message->getMessage();

            $hash = $message->getHash();

            /** @var Device $device */
            $device = $message->getDeviceId();

            $key = $data['code'] . $data['code'] . $data['code'] . 'z';
            $iv = $device->getIv();
            $em = $this->getDoctrine()->getManager();
            if (trim(base64_encode(base64_decode($text))) !== trim($text)) {
                $success = "Not valid code";
                if ($this->addCount($message)) {
                    $result = \Longman\TelegramBot\Request::sendMessage(
                        ['chat_id' => $device->getChatId(), 'text' =>
                            "Код введён не верно несколько раз для сообщение с номером " . $messageId . PHP_EOL .
                            "Удалить сообщение с сервиса можно по ссылке: " . PHP_EOL .
                            $this->generateUrl('delete_message_by_delete_hash', array('hash' => $message->getDeleteHash()),
                                UrlGeneratorInterface::ABSOLUTE_URL)]
                    );
                }

            } else {
                $str = openssl_decrypt(base64_decode($text), 'AES-128-CTR', $key, true, $iv); // OpenSSL
                if ($hash != md5($str)) {
                    $success = "Not valid hash";
                    if ($this->addCount($message)) {
                        $result = \Longman\TelegramBot\Request::sendMessage(
                            ['chat_id' => $device->getChatId(), 'text' =>
                                "Код введён не верно несколько раз для сообщение с номером " . $messageId . PHP_EOL .
                                "Удалить сообщение с сервиса можно по ссылке: " . PHP_EOL .
                                $this->generateUrl('delete_message_by_delete_hash', array('hash' => $message->getDeleteHash()),
                                    UrlGeneratorInterface::ABSOLUTE_URL)]
                        );
                    }
                } else {
                    $success = 'Code valid, message send';
                    $messageSend = $str;
                    $result = \Longman\TelegramBot\Request::sendMessage(
                        ['chat_id' => $device->getChatId(), 'text' => $str]);

                    $em->remove($message);
                    $em->flush();

                }
            }
        }
        return $this->render('LetunovskiymnSMSReaderBundle:Default:validCode.html.twig',
            ['form' => $form->createView(), 'success' => $success, 'message' => $messageSend]);
    }

    private function addCount(Message $message): bool
    {
        $result = false;
        $count = (int)$message->getCount();
        $count++;
        $message->setCount($count);
        $em = $this->getDoctrine()->getManager();
        if ($count == 3) {
            $deleteHash = md5(time() . rand(99, 1000) . $message->getHash());
            $message->setDeleteHash($deleteHash);
            $result = $deleteHash;
        }
        $em->persist($message);
        $em->flush();
        return $result;
    }


    /**
     * @Route("/delete-message/{hash}/", name="delete_message_by_delete_hash")
     * @param Request $request
     * @param null $hash
     * @return Response
     */
    public function deleteHashAction(Request $request, $hash = null)
    {
        /** @var Message $message */
        $message = $this->getDoctrine()
            ->getRepository('LetunovskiymnSMSReaderBundle:Message')
            ->findOneBy(['delete_hash' => $hash]);

        if (!$message) {
            throw $this->createNotFoundException(
                'Message not found'
            );
        }

        $key = $this->getParameter('smsreader_bot_api_key');
        $bot_name = $this->getParameter('smsreader_bot_name');
        $telegram = new Telegram($key, $bot_name);
        $idMessage = $message->getId();

        /** @var Device $device */
        $device = $message->getDeviceId();

        $em = $this->getDoctrine()->getManager();
        $em->remove($message);
        $em->flush();
        $result = \Longman\TelegramBot\Request::sendMessage(['chat_id' => $device->getChatId(),
            'text' => "Удалено сообщение " . $idMessage]);
        return new Response();

    }

    /**
     * @Route("/decrypt-by-device/{guid}/{messageId}/{key}/", name="decrypt_by_device_id_message")
     * @param Request $request
     * @param null $guid
     * @param null $key
     * @return JsonResponse
     */
    public function deCryptByDeviceIdAction(Request $request, $guid = null, $key = null, $messageId = 0)
    {
        $result = [];
        if (!empty($key) && !empty($guid) && !empty($messageId)) {
            $key = $key . $key . $key . "z";
            /** @var Device $device */
            $device = $this->getDoctrine()
                ->getRepository('LetunovskiymnSMSReaderBundle:Device')
                ->findOneBy(['guid' => $guid]);

            if (!$device) {
                throw $this->createNotFoundException(
                    'No device found for id ' . $guid
                );
            }

            /** @var Message $message */
            $message = $this->getDoctrine()
                ->getRepository('LetunovskiymnSMSReaderBundle:Message')
                ->findOneBy(['deviceId' => $device->getId(), 'id' => $messageId]);

            if (!$message) {
                throw $this->createNotFoundException(
                    'Message not found'
                );
            }
            $strSource = openssl_decrypt(base64_decode($message->getMessage()),
                'AES-128-CTR',
                $key,
                true, $device->getIv());
            $result['text'] = $strSource;
        }
        return new JsonResponse($result);
    }


}
