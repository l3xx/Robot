<?php
/**
 * Created by PhpStorm.
 * User: LetunovskiyMN
 * Date: 04.04.2017
 * Time: 16:12
 */

namespace Longman\TelegramBot\Commands\UserCommands;

use Bitbucket\API\Authentication\Basic;
use Bitbucket\API\Http\Listener\BasicAuthListener;
use Bitbucket\API\Repositories;
use Bitbucket\API\Teams;
use Bitbucket\API\User;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\DBAL\Driver\Connection;
use Letunovskiymn\KorablikBundle\Entity\KeysBot;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class GetKeyCommand extends UserCommand
{
    /**#@+
     * {@inheritdoc}
     */
    protected $name = 'getKey';
    protected $description = 'Response key for zabbix';
    protected $usage = '/getKey';
    protected $version = '0.1.0';
    /**#@-*/

    /**
     * Execute command
     *
     * @return \Longman\TelegramBot\Entities\ServerResponse
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function execute()
    {
        $message = $this->getMessage();

        $chat_id = $message->getChat()->getId();
        /** @var ContainerInterface $container */
        $container=$this->telegram->container;
        /** @var ManagerRegistry $registry */
        $registry=$container->get('doctrine');

        $chatKeyId =$registry
            ->getRepository('LetunovskiymnKorablikBundle:KeysBot')
            ->findOneBy(['chatId' => $chat_id]);

        $data = [];
        $data['chat_id'] = $chat_id;
        $data['text'] = '';
        if ($chatKeyId)
        {
            $data['text'] = $chatKeyId->getKeyClient();
        }
            else
        {
            $chatKey = new KeysBot();
            $chatKey->setChatId($chat_id);
            $random = md5(time().rand(1000,9999));
            $chatKey->setKeyClient($random);
//            $chatKey->setName($this->getName());
            /** @var ObjectManager $connection */
            $connection=$registry->getManager();
            $connection->persist($chatKey);
            $connection->flush();
            $data['text'] = $random;
        }
        return Request::sendMessage($data);
    }




}