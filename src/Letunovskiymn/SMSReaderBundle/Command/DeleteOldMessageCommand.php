<?php
/**
 * Created by PhpStorm.
 * User: LetunovskiyMN
 * Date: 05.06.2017
 * Time: 15:20
 */

namespace Letunovskiymn\SMSReaderBundle\Command;


use Letunovskiymn\SMSReaderBundle\Entity\Message;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteOldMessageCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('smsReader:deleteOldMessage')
            ->setDescription('Удалить все сообщение которые младше оерделённой даты')
            ->setHelp('Удалить все сообщение которые младше оерделённой даты')
            ->addArgument('date', InputArgument::OPTIONAL, 'Дата с которой надо удалить сообщения')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $date = $input->getArgument('date',null);
        $date = new \DateTime($date);
        $doctrine =$this->getApplication()->getKernel()->getContainer()->get('doctrine')->getEntityManager();;
        $query = $doctrine->createQuery(
            'SELECT m
                FROM LetunovskiymnSMSReaderBundle:Message m
                WHERE m.updated < :date'
        )->setParameter('date', $date);

        $messages = $query->getResult();
        if ($messages){
            /** @var Message $message */
            foreach ($messages as $message){
                $output->writeln('Delete message id '.$message->getId());
                $doctrine->remove($message);
                $doctrine->flush();

            }
        }

    }
}