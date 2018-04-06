<?php

namespace Acme;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use GuzzleHttp\ClientInterface;
use ZipArchive;

class NewCommand extends Command
{
    private $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;

        parent::__construct();
    }

    public function configure ()
    {
        $this->setName('new')
            ->setDescription('Create A New Mix Code Application.')
            ->addArgument('name', InputArgument::REQUIRED);
    }

    public function execute (InputInterface $input, OutputInterface $output)
    {
        // Assert The Folder doesn't already exist
        // getcwd() => get current working directory
        $directory = getcwd() . '/' . $input->getArgument('name');

        $output->writeln('<info>Crafting Application ...</info>');
        
        $this->assertApplicationDoesNotExist($directory, $output);

        // Download nightly version of laravel
        $this->download($zipFile = $this->makeFileName())
        // extract zip file
        ->extract($zipFile, $directory)
        ->cleanUp($zipFile);

        // alert the user that they are ready to go

        $output->writeln('<comment>Application Ready By alaaDragneel Just Study Case on Laravel Installer :) !!</comment>');
    }

    private function assertApplicationDoesNotExist ($directory, OutputInterface $output)
    {
        if (is_dir($directory)){
            $output->writeln('<error>Application Already Exists!</error>');
            exit(1);
        }
    }

    private function makeFileName()
    {
        return getcwd() . '/laravel_mixcode_' . md5(time().uniqid()) . '.zip';
    }

    private function download ($zipFile) 
    {
        $response = $this->client->get('http://cabinet.laravel.com/latest.zip')->getBody();
        
        file_put_contents($zipFile, $response);

        return $this;
    }

    private function extract($zipFile, $directory)
    {
        $archive = new ZipArchive;       

        $archive->open($zipFile);

        $archive->extractTo($directory);
    
        $archive->close();
        
        return $this;    
    }

    private function cleanUp($zipFile)
    {
        @chmod($zipFile, 0777);
        @unlink($zipFile);

        return $this;    
    }
}