<?php

namespace Feral\Symfony\Console\Command;

use DataObject\Configuration;
use Feral\Core\Process\Attributes\ConfigurationDescriptionInterface;
use Feral\Core\Process\Catalog\Catalog;
use Feral\Core\Process\NodeCode\NodeCodeFactory;
use Reepository\ConfigurationRepository;
use Symfony\Component\Console\Attribute as Console;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[Console\AsCommand(
    name: 'feral:details:catalog-node',
    description: 'List the configuration for a catalog node.'
)]
class CatalogNodeDetailsCommand extends Command
{

    public function __construct(
        protected Catalog $catalog,
        protected NodeCodeFactory $factory,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('key', InputArgument::REQUIRED, 'The Catalog Node Key?');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $key = $input->getArgument('key');
        $catalogNode = $this->catalog->getCatalogNode($key);

        $configurationDescriptions = [];

        // NODE CONFIGURATION
        $nodeCode = $this->factory->getNodeCode($catalogNode->getNodeCodeKey());
        $nodeCodeReflection = new \ReflectionClass($nodeCode::class);
        $nodeCodeAttributes = $nodeCodeReflection->getAttributes();
        foreach ($nodeCodeAttributes as $attribute) {
            $instance = $attribute->newInstance();
            if (is_a($instance, ConfigurationDescriptionInterface::class)) {
                $configurationDescriptions[$instance->getKey()] = $instance;
            }
        }

        // CATALOG CONFIGURATION
        $catalogNodeReflection = new \ReflectionClass($catalogNode::class);
        $catalogAttributes = $catalogNodeReflection->getAttributes();
        foreach ($catalogAttributes as $attribute) {
            $instance = $attribute->newInstance();
            if (is_a($instance, ConfigurationDescriptionInterface::class)) {
                $configurationDescriptions[$instance->getKey()] = $instance;
            }
        }

        // DETAILS
        $output->writeln(sprintf(
            '<options=bold>Feral Catalog Node "%s" (%s) Details</>',
            $catalogNode->getName(),
            $catalogNode->getKey()
            ));
        $output->writeln(sprintf(
                "<comment>%s</comment>\n\nNode Code: %s (%s) Category: %s\n<comment>%s</comment>",
                $catalogNode->getDescription(),
                $nodeCode->getName(),
                $nodeCode->getKey(),
                $nodeCode->getCategoryKey(),
                $nodeCode->getDescription(),
                )
        );

        $configuration = $catalogNode->getConfiguration();
        $output->writeln("\n<options=underscore>Catalog Node Configuration</>");
        foreach ($configuration as $key => $value) {
            $configuration = $configurationDescriptions[$key];
            $output->writeln(sprintf(
                " - %s <info>(%s)</info> = '%s' : <comment>%s</comment>",
                $configuration->getName(),
                $configuration->getKey(),
                $value,
                $configuration->getDescription())
            );
            unset($configurationDescriptions[$key]);
        }

        $output->writeln("\n<options=underscore>Catalog Configurations</>");
        if (empty($configurationDescriptions)) {
            $output->writeln(sprintf(' - %s <info>(%s)</info> : <comment>Contains no configuration items.</comment>', $catalogNode->getName(), $catalogNode->getKey()));
        } else {
            foreach ($configurationDescriptions as $description) {
                $output->writeln(sprintf(" - %s <info>(%s)</info> : <comment>%s</comment>", $description->getName(), $description->getKey(), $description->getDescription()));
            }
        }
        return Command::SUCCESS;
    }
}