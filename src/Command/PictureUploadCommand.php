<?php

declare(strict_types=1);

namespace DBP\API\AuthenticDocumentBundle\Command;

use DBP\API\AuthenticDocumentBundle\UCard\UCardAPI;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PictureUploadCommand extends Command
{
    protected static $defaultName = 'dbp:picture-upload';

    private $service;
    private $config;

    public function __construct(UCardAPI $service)
    {
        parent::__construct();

        $this->service = $service;
        $this->config = [];
    }

    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    protected function configure()
    {
        $this->setDescription('Upload pictures for a CO user');
        $this->addArgument('ident', InputArgument::REQUIRED, 'The IDENT-NR-OBFUSCATED of the user');
        $this->addArgument('card-type', InputArgument::REQUIRED, 'The card type to set for');
        $this->addArgument('path', InputArgument::REQUIRED, 'The path to a picture');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = $this->config;
        $clientId = $config['co_oauth2_api_client_id'] ?? '';
        $clientSecret = $config['co_oauth2_api_client_secret'] ?? '';
        $baseUrl = $config['co_oauth2_api_api_url'] ?? '';

        $service = $this->service;
        $service->setBaseUrl($baseUrl);
        $service->fetchToken($clientId, $clientSecret);

        $ident = $input->getArgument('ident');
        $cardType = $input->getArgument('card-type');
        $filePath = $input->getArgument('path');

        $cards = $service->getCardsForIdent($ident, $cardType);
        // If there exists no card of the specified type we have to create one
        if (count($cards) === 0) {
            $service->createCardForIdent($ident, $cardType);
            $cards = $service->getCardsForIdent($ident, $cardType);
        }

        assert($cards[0]->cardType === $cardType);

        $card = $cards[0];
        $data = file_get_contents($filePath);
        $service->setCardPicture($card, $data);

        return 0;
    }
}
