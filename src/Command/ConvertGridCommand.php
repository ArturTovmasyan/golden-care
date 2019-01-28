<?php

namespace App\Command;

use App\Annotation\Grid;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\AnnotationReader;
use Phramz\Doctrine\Annotation\Scanner\Scanner;
use Phramz\Doctrine\Annotation\Scanner\ClassFileInfo;

class ConvertGridCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('app:convert:grid')
            ->setDescription('Create Swagger docs.')
            ->setHelp('Use this command to generate Swagger docs.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $reader = new AnnotationReader();
            $scanner = new Scanner($reader);

            $scanner->scan(array(
                Grid::class
            ))
                ->in('./src/Entity');

            $output = "";

            /** @var ClassFileInfo $file */
            foreach ($scanner as $file) {
                $annotations = $file->getClassAnnotations();
                foreach ($annotations as $annotation) {
                    if ($annotation instanceof Grid) {
                        $groups = $annotation->getGroups();
                        $output .= $file->getClassName() . "\n";
                        $output .= "/*\n* @Grid(\n";
                        foreach ($groups as $key => $group) {
                            $output .= "*     " . $key . "={\n";
                            foreach ($group as $field) {
                                $output .= "*          {\n";

                                if ($field['id'] == "id") {
                                    $field['type'] = "id";
                                }

                                if ($field['type'] == "enum" && $field['values']['class'] == "\App\Model\Boolean") {
                                    $field['type'] = "boolean";

                                    unset($field['values']);
                                }

                                foreach ($field as $key => $value) {
                                    if ($key == 'values') {
                                        $value = $value['class'] . "::" . $value['method'];
                                    }

                                    if (!in_array($key, [
                                        Grid::FIELD_OPTIONS[Grid::FIELD_OPTION_SORTABLE],
                                        Grid::FIELD_OPTIONS[Grid::FIELD_OPTION_FILTERABLE]
                                    ])) {
                                        $value = '"' . $value . '"';
                                    } else {
                                        $value = $value ? "true" : "false";
                                    }

                                    $output .= "*              " . str_pad('"' . $key . '"', 12, " ") . " = " . $value . ",\n";
                                }

                                $output = substr($output, 0, -2) . "\n";
                                $output .= "*          },\n";
                            }
                            $output = substr($output, 0, -2) . "\n";
                            $output .= "*     },\n";
                        }
                        $output = substr($output, 0, -2) . "\n";
                        $output .= "* )\n*/\n";
                    }
                }
            }

            echo $output;
        } catch (AnnotationException $e) {
            var_dump($e);
        } // get an instance of the doctrine annotation reader
    }
}
