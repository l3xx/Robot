<?php
/**
 * This file is part of the TelegramBot package.
 *
 * (c) Avtandil Kikabidze aka LONGMAN <akalongman@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Longman\TelegramBot\Commands\SystemCommands;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Request;

/**
 * Start command
 */
class StartCommand extends SystemCommand
{
    /**
     * @var string
     */
    protected $name = 'start';

    /**
     * @var string
     */
    protected $description = 'Start command';

    /**
     * @var string
     */
    protected $usage = '/start';

    /**
     * @var string
     */
    protected $version = '1.1.0';

    /**
     * Command execute method
     *
     * @return mixed
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function execute()
    {
        $message = $this->getMessage();
        $textCommand = trim($message->getText(true));
        $chat_id = $message->getChat()->getId();


        $text = "Привет.".PHP_EOL;

        if (empty($textCommand)){
            $text .="Ты передал пустой параметр при старте, с этим ботом так не общаются".PHP_EOL;
        }
        else
        {
            if ( base64_encode(base64_decode($textCommand, true)) === $textCommand){
                /** @var Registry $registryDoctrine */
                $registryDoctrine=$this->getTelegram()->getContainer();
                $deviceDoctrine=$registryDoctrine->getRepository('LetunovskiymnSMSReaderBundle:Device')
                    ->findOneBy(['guid'=>base64_decode($textCommand,true)]);

                $text .=$deviceDoctrine->getId();

                if (!$deviceDoctrine){
                    $text .="Что то пошло не так, id устроййтсва передан не верно".PHP_EOL;
                }
                else{
                    $deviceDoctrine->setChatId($chat_id);
                    $em = $registryDoctrine->getManager();
                    $em->persist($deviceDoctrine);
                    $em->flush();
                    $text    = 'Всё прошло гладенько, теперь ты будешь получать СМС сообщения в телеграм';
                }
            } else {
                $text .="Что то пошло не так, id устроййтсва не base64 строка".PHP_EOL;
            }
        }
        $data = [
            'chat_id' => $chat_id,
            'text'    => $text,
        ];

        return Request::sendMessage($data);
    }
}
