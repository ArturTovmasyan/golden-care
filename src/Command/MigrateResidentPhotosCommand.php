<?php

namespace App\Command;

use App\Api\V1\Common\Service\ImageFilterService;
use App\Entity\Resident;
use App\Entity\ResidentImage;
use App\Repository\ResidentImageRepository;
use App\Repository\ResidentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class MigrateResidentPhotosCommand extends Command
{
    /** @var ImageFilterService */
    private $imageFilterService;
    /** @var EntityManagerInterface */
    private $em;

    /**
     * MigrateResidentPhotosCommand constructor.
     * @param EntityManagerInterface $em
     * @param ImageFilterService $imageFilterService
     */
    public function __construct(EntityManagerInterface $em, ImageFilterService $imageFilterService)
    {
        parent::__construct();

        $this->em = $em;
        $this->imageFilterService = $imageFilterService;
    }

    protected function configure(): void
    {
        $this
            ->setName('app:migrate:photos')
            ->setDescription('Import residents photos from JSON file.')
            ->setHelp('Use this command to import residents photos from JSON file.')
            ->addOption('json', null, InputOption::VALUE_REQUIRED, 'The JSON file to import.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var ResidentRepository $resident_repo */
        $resident_repo = $this->em->getRepository(Resident::class);
        /** @var ResidentImageRepository $image_repo */
        $image_repo = $this->em->getRepository(ResidentImage::class);

        $file_system = new Filesystem();
        $json_filename = $input->getOption('json');
        if ($file_system->exists($json_filename)) {
            $file = file_get_contents($json_filename);
            $data = json_decode($file, true);

            $progressBar = new ProgressBar($output, \count($data));
            $progressBar->start();

            foreach ($data as $item) {
                $resident = $resident_repo->find($item['id']);
                if ($resident) {
                    $data = file_get_contents($item['photo']);

                    if ($data) {
                        $type = pathinfo($item['photo'], PATHINFO_EXTENSION);
                        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);

                        /** @var Resident $resident */
                        $image = $image_repo->getBy($resident->getId());
                        if ($image === null) {
                            $image = new ResidentImage();
                        }

                        $image->setResident($resident);
                        $image->setPhoto($base64);

                        $this->imageFilterService->createAllFilterVersion($image);
                        $this->em->flush();
                    } else {
                        $output->writeln(sprintf("Download of '%s' for resident '%d' failed.", $item['photo'], $item['id']));
                    }
                } else {
                    $output->writeln(sprintf("Resident '%d' not found.", $item['id']));
                }
            }

            $progressBar->advance();
            $progressBar->finish();
        } else {
            $output->writeln(sprintf("Invalid input file '%s'.", $json_filename));
        }
    }
}
