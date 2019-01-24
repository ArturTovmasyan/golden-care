<?php

namespace App\Command;

use App\Api\V1\Common\Service\Helper\ResidentPhotoHelper;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class MigrateResidentPhotosCommand extends ContainerAwareCommand
{
    /** @var ResidentPhotoHelper */
    private $photoHelper;

    /**
     * MigrateResidentPhotosCommand constructor.
     */
    public function __construct(ResidentPhotoHelper $photoHelper)
    {
        parent::__construct();
        $this->photoHelper = $photoHelper;
    }

    protected function configure()
    {
        $this
            ->setName('app:migrate:photos')
            ->setDescription('Import residents photos from JSON file.')
            ->setHelp('Use this command to import residents photos from JSON file.')
            ->addOption('json', null, InputOption::VALUE_REQUIRED, 'The JSON file to import.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fileSystem = new Filesystem();

        if ($fileSystem->exists($input->getOption('json'))) {
            $file = file_get_contents($input->getOption('json'));
            $data = json_decode($file, true);

            $progressBar = new ProgressBar($output, count($data));
            $progressBar->start();

            foreach ($data as $item) {
                $data = file_get_contents($item['photo']);

                if($data) {
                    $type = pathinfo($item['photo'], PATHINFO_EXTENSION);
                    $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                    $this->photoHelper->save($item['id'], $base64);
                } else {
                    $output->writeln("Download failed");
                }
                $progressBar->advance();
            }
            $progressBar->finish();
        } else {
            $output->writeln("Invalid input file");
        }
    }
}
