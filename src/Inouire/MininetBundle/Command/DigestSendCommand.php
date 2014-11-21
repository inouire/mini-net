<?php

namespace Inouire\MininetBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DigestSendCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('digest:send')
            ->setDescription('Send a digest of what has been posted during the last 7 days to the users that did not connect recently');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        
        $now = new \DateTime();
        $last_week = new \Datetime();
        $last_week->sub(new \DateInterval('P7D'));
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $twig = $this->getContainer()->get('twig');
        
        $output->writeln('Sending digest sending - '.$now->format('Y-m-d H:i:s'));
        
        // get weekly posts, and build weekly digest from them
        $posts = $em->getRepository('InouireMininetBundle:Post')->getPostsSince($last_week);
        $output->writeln('<comment>'.count($posts).'</comment><info> news have been posted last week</info>');
        $subject = "Que s'est il passé sur le mini-net cette semaine ?";
        $html_content = $twig->render(
            'InouireMininetBundle:Admin:digest_email.html.twig',
            array('post_list' => $posts)
        );
        $raw_content = $twig->render(
            'InouireMininetBundle:Admin:digest_email.txt.twig',
            array('post_list' => $posts)
        );
        print($html_content);

        // retrieve list of users for which last connection >= 7 days
        $users = $em->getRepository('InouireUserBundle:User')->getUsersNotConnectedSince($last_week);
        $output->writeln('<comment>'.count($users).'</comment><info> users did not connect since last week</info>');
        
        // send email to the list of users
        $output->writeln('<info>Sending digest to users...</info>');
        foreach($users as $user){
            $output->writeln('    <info>Sending email to</info> <comment>'.$user->getUsername().' ('.$user->getEmail().')</comment>');
            $message = \Swift_Message::newInstance()
                    ->setSubject($subject)
                    ->setFrom('admin@inouire.net')
                    ->setTo($user->getEmail())
                    ->setBody($raw_content)
                    ->addPart($html_content, 'text/html');
            $this->getContainer()->get('mailer')->send($message);
        }
        
    }
}
