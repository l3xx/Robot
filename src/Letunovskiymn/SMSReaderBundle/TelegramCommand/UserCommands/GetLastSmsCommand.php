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
use Longman\TelegramBot\Entities\Keyboard;
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
//        $chat_id = $this->getMessage()->getChat()->getId();
//        $message = $this->getMessage();
//        $textCommand = trim($message->getText(true));
//        $replyMarkup=null;
//        $chunks=[];
//        try{
//            /** @var Registry $registryDoctrine */
//            $registryDoctrine=$this->getTelegram()->getContainer();
//            $deviceDoctrine=$registryDoctrine->getRepository('LetunovskiymnSMSReaderBundle:Device')
//                ->findOneBy(['chatId'=>$chat_id]);
//            if ($deviceDoctrine)
//            {
//                $resultMessage='';
//                $count=6;
//                if ((int)$textCommand>0){
//                    $count=(int)$textCommand;
//                }
//
//                $query = $registryDoctrine->getManager()->createQuery(
//                    'SELECT m
//                FROM LetunovskiymnSMSReaderBundle:Message m
//                WHERE m.deviceId = :deviceId ORDER by m.updated DESC'
//                )
//                    ->setParameters(['deviceId'=>$deviceDoctrine->getId()])
//                    ->setMaxResults($count);
//
//                $messages = $query->getResult();
//
//                if ($messages){
//                    /** @var Message $message */
//                    foreach ($messages as $message){
//
//                        $chunks[]=['text' => 'От '.$message->getFromUser().printf("(%s)",
//                                ($message->getUpdated())->format('d-m-y H:i:s')),
//                            'url' => 'https://github.com/akalongman/php-telegram-bot'];
//                    }
////                    $keyboard=array_chunk($chunks,3);
//                    $replyMarkup=new InlineKeyboard($chunks);
//
//                }
//            }
//            else
//            {
//                $resultMessage="Вы не присоеденислись к боту, попробуйте скачать приложение и связать приложение";
//            }
//        }
//        catch (\Exception $e){
//            $resultMessage=$e->getMessage();
//        }
//
//        $inline_keyboard = new InlineKeyboard([
//            ['text' => 'callback', 'callback_data' => 'identifier'],
//            ['text' => 'open url', 'url' => 'https://github.com/akalongman/php-telegram-bot'],
//            ['text' => 'open url', 'url' => 'https://github.com/akalongman/php-telegram-bot'],
//            ['text' => 'open url', 'url' => 'https://github.com/akalongman/php-telegram-bot'],
//            ['text' => 'open url', 'url' => 'https://github.com/akalongman/php-telegram-bot'],]
//        );
//
//        $data = [
//            'chat_id'      => $chat_id,
//            'text'         =>var_export(array_chunk($chunks,3),true),
//            'reply_markup' =>$replyMarkup
//
//        ];

//
//        $switch_element = mt_rand(0, 9) < 5 ? 'true' : 'false';
//
//        $inline_keyboard = new InlineKeyboard([
//            ['text' => 'inline', 'switch_inline_query' => $switch_element],
//            ['text' => 'inline current chat', 'switch_inline_query_current_chat' => $switch_element],
//        ], [
//            ['text' => 'callback', 'callback_data' => 'identifier'],
//            ['text' => 'open url', 'url' => 'https://github.com/akalongman/php-telegram-bot'],
//        ]);
//
//        $data = [
//            'chat_id'      => $chat_id,
//            'text'         => 'inline keyboard',
//            'reply_markup' => $inline_keyboard,
//        ];
        return Request::sendMessage($data);
    }




}