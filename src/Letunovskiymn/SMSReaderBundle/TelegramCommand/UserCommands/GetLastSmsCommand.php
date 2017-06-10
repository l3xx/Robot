<?php
/**
 * Created by PhpStorm.
 * User: letunovskiymn
 * Date: 22.05.17
 * Time: 19:33
 */

namespace Longman\TelegramBot\Commands\UserCommands;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Letunovskiymn\SMSReaderBundle\Entity\Message;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Request;

class GetLastSmsCommand extends UserCommand
{

    /**#@+
     * {@inheritdoc}
     */
    protected $name = 'getLastSms sms from phone';
    protected $description = 'Return last SMS to chat';
    protected $usage = '/getLastSms';
    protected $version = '0.1.0';
    protected $enabled = true;
    /**#@-*/

    /**
     * Execute command
     *
     * @return \Longman\TelegramBot\Entities\ServerResponse
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function execute()
    {
        $chat_id = $this->getMessage()->getChat()->getId();
        $message = $this->getMessage();
        $textCommand = trim($message->getText(true));
        try {
            /** @var Registry $registryDoctrine */
            $registryDoctrine = $this->getTelegram()->getContainer();
            $deviceDoctrine = $registryDoctrine->getRepository('LetunovskiymnSMSReaderBundle:Device')
                ->findOneBy(['chatId' => $chat_id]);
            if ($deviceDoctrine) {
                $count = 3;
                if ((int)$textCommand > 0) {
                    $count = (int)$textCommand;
                }

                $query = $registryDoctrine->getManager()->createQuery(
                    'SELECT m
                FROM LetunovskiymnSMSReaderBundle:Message m
                WHERE m.deviceId = :deviceId ORDER by m.updated DESC'
                )
                    ->setParameters(['deviceId' => $deviceDoctrine->getId()])
                    ->setMaxResults($count);

                $messages = $query->getResult();

                if ($messages) {
                    /** @var Message $message */
                    foreach ($messages as $message) {
                        $inline_keyboard = new InlineKeyboard(
                            [
                                [
                                    'text' => 'Прочиать ->',
                                    'url' => 'https://api.opendevelopers.ru/sms-reader/validation/' . $message->getId()],
                            ]);

                        $data = [
                            'chat_id' => $chat_id,
                            'text' => 'От ' . $message->getFromUser() . " (" .
                                ($message->getUpdated())->format('d-m-y H:i:s') . ")",
                            'reply_markup' => $inline_keyboard,
                        ];
                        Request::sendMessage($data);
                    }
                }
            } else {
                $resultMessage = "Вы не присоеденислись к боту, попробуйте скачать приложение и связать приложение";
            }
        } catch (\Exception $e) {
            $resultMessage = $e->getMessage();
        }
        if (!empty($resultMessage)) {
            //todo доделать отправку сообщения что-то сломалось
        }
        return Request::emptyResponse();
    }
}