<?php
/**
 * Created by PhpStorm.
 * User: taachja1
 * Date: 03/03/16
 * Time: 16:42
 *
 * Import direct into db
 *
 * php vendor/graviton/graviton/bin/graviton DirectImportCommand:initialData /path-to-data-folder
 * Run first /initial and then /data
 */

namespace Graviton\CoreBundle\Command;

use Graviton\I18nBundle\Document\TranslatableDocumentInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;
use Graviton\I18nBundle\Document\Translatable;
use Graviton\I18nBundle\Model\Translatable as ModelTranslatable;
use Symfony\Component\Console\Helper\ProgressBar;
use Doctrine\ODM\MongoDB\DocumentManager;
use Graviton\RestBundle\Validator\Form;
use Graviton\ExceptionBundle\Exception\ValidationException;
use Graviton\DocumentBundle\Service\FormDataMapperInterface;
use Graviton\RestBundle\Model\DocumentModel;

/**
 * Class DirectImportCommand
 * @package Graviton\CoreBundle\Command
 * @author  List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link    http://swisscom.ch
 * @return  void
 */
class DirectImportCommand extends ContainerAwareCommand
{
    /** @var ModelTranslatable */
    protected $modelTranslatable;

    /** @var Container */
    protected $container;

    /**
     * Starting Command
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('DirectImportCommand:initialData')
            ->setDescription('Greet someone')
            ->addArgument('location', InputArgument::REQUIRED, 'Full path to location of folder to import');
    }

    /**
     * @param  InputInterface  $input  Sf command line input
     * @param  OutputInterface $output Sf command line output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $location = $input->getArgument('location');

        $this->container = $container = $this->getContainer();
        $this->modelTranslatable = $container->get('graviton.i18n.model.translatable');
        /** @var DocumentManager $documentManager */
        $documentManager = $container->get('doctrine_mongodb.odm.default_document_manager');

        $output->writeln('Importing from: '.$location);

        /**
         * @param SplFileInfo $file
         * @return bool
         */
        $filter = function (SplFileInfo $file) {
            if (!in_array($file->getExtension(), ['yml','json']) || $file->isDir()) {
                return false;
            }
            return true;
        };

        $finder  = new Finder();
        $finder->files()->in($location)->ignoreDotFiles(true)->filter($filter);

        $totalFiles = $finder->count();
        $progress = new ProgressBar($output, $totalFiles);
        $errors = [];
        $success = [];

        // CoreTypes and Refferences
        $referenceObjects = [];

        /** @var SplFileInfo $file */
        foreach ($finder as $file) {
            $fileName = str_replace($location, '', $file->getPathname());

            $progress->advance();

            $contents = explode('---', $file->getContents());
            if (!$contents || count($contents)!==3) {
                $errors[$fileName] = 'Content of file is incorrect, could not parse it with --- ';
                continue;
            }

            // Target or collection can be defined in first lines of file.
            if (strpos($contents[1],'target') !== false) {
                $target = trim(str_replace('target:', '', $contents[1]));
                $targets = explode('/', $target);
                if (!$targets || count($targets) < 3) {
                    $errors[$fileName] = 'Target is not correctly defined: ' . json_encode($targets);
                    continue;
                }
                $domain = $targets[1];
            } else {
                $target = trim(str_replace('collection:', '', $contents[1]));
                $domain = $target;
                $targets = [$target,$target,$target];
            }

            // Data parsing of file
            if ('yml' == $file->getExtension()) {
                $yaml = $contents[2];
                try {
                    $data = Yaml::parse($yaml);
                } catch (ParseException $e) {
                    $errors[$fileName] = 'Could not parse yml file';
                    continue;
                }
            } else {
                $json = $contents[2];
                try {
                    $data = json_decode($json, true);
                } catch (ParseException $e) {
                    $errors[$fileName] = 'Could not parse json file';
                    continue;
                }
            }

            if (!$data) {
                $errors[$fileName] = $file->getExtension().' file is empty or parsed failed.';
                continue;
            }

            $objectId = array_key_exists('id', $data) ? $data['id'] : end($targets);

            // Locate class name like graviton.i18n.document.language
            $service = $this->findServiceObject($targets);
            $objectClass = $service['class'];
            $serviceName = $service['service'];

            if ($service['error'] || !$objectClass) {
                $errors[$fileName] = $service['error'] ? $service['error'] : 'Could not find service';
                continue;
            }

            $object = clone $objectClass;
            $method = false;
            foreach ($data as $key => $value) {
                $translated = [];
                if ($object instanceof TranslatableDocumentInterface) {
                    $translated = $object->getTranslatableFields();
                }
                if ("_id" == $key) {
                    $key = 'id';
                }
                $method = 'set' . ucfirst($key);

                if (method_exists($object, $method)) {
                    if (is_array($value) && in_array($key, $translated)) {
                        $langErrors = $this->generateLanguages($domain, $value, $objectId);
                        if ($langErrors) {
                            $errors[$fileName] = 'Errors generating language: '.json_encode($langErrors);
                        }
                        $object->$method(reset($value));
                    } elseif (is_array($value)) {
                        try {
                            // Check if MongoData
                            $obj = (object) $value;
                            $type = '@type';
                            if ($obj->$type == 'MongoDate' && $obj->sec) {
                                $ts = $obj->sec;
                                $obj = new \DateTime("@$ts");
                            }
                            $object->$method($obj);
                        } catch (\Exception $e) {
                            $referenceObjects[$objectId] = [
                                'data' => $data,
                                'file' => $fileName,
                                'service' => $serviceName
                            ];
                        }
                    } else {
                        try {
                            $object->$method($value);
                        } catch (\Exception $e) {
                            $errors[$fileName] = 'Method: '.$method.' with:'.$value.' error:'.$e->getMessage();
                            continue;
                        }
                    }
                } else {
                    $errors[$fileName] = 'Method: '.$method.' does not exists';
                }
            }

            try {

                if ($method) {
                    $documentManager->persist($object);
                }

                $documentManager->flush();
                $documentManager->clear();
            } catch (\Exception $e) {
                $errors[$fileName] = 'Save error:'.$e->getMessage();
                continue;
            }
            $success[] = $fileName;
        }

        $progress->finish();

        // Resume:
        $inserted = $totalFiles - count($errors);
        $output->writeln("\n".'<info>Inserted Objects:'.$inserted.'</info>');

        // Referenced object update
        $errorsRefs=[];
        if ($referenceObjects) {
            $output->writeln("\n".'References Updating: '.count($referenceObjects));
            $errorsRefs = $this->updateReferences($referenceObjects, $output);
        }

        // Output's
        if (!$errors && !$errorsRefs) {
            $output->writeln('<comment>Done without errors</comment>');
        } else {

            if ($errors) {
                $output->writeln("\n".'<error>== Errors: '.count($errors).' ==</error>');
                foreach ($errors as $fileName => $error) {
                    $errString = is_array($error) ? json_encode($error) : $error;
                    $output->writeln("<comment>$fileName : $errString</comment>");
                }
            }

            if ($errorsRefs) {
                $output->writeln("\n" . '<error>== Update Ref. Errors: ' . count($errorsRefs) . ' ==</error>');
                foreach ($errorsRefs as $fileName => $error) {
                    $errString = is_array($error) ? json_encode($error) : $error;
                    $output->writeln("<comment>$fileName : $errString</comment>");
                }
            }
        }

        $output->writeln("\n".'<info>============ Success: '.count($success).' ============</info>');
        if (!$success) {
            $output->writeln('No files imported');
        }

        $output->writeln('<info>============ End ============</info>'."\n");
        if (count($errors)) {
            $output->writeln('<error>== Errors: '.(count($errors)+count($errorsRefs)).', please check ==</error>'."\n");
        }

    }



    /**
     * generate strings for languages
     *
     * @param string      $domain     Domain Name
     * @param array       $languages  Translated language values
     * @param string|bool $idLanguage Id to reference name
     *
     * @return array
     */
    private function generateLanguages($domain, $languages, $idLanguage = false)
    {
        $errors = [];
        foreach ($languages as $language => $translation) {
            if ($idLanguage) {
                $id = implode('-', array($domain, $idLanguage, $translation));
            } else {
                $id = implode('-', array($domain, $language, $translation));
            }

            try {
                $record = new Translatable;
                $record->setId($id);
                $record->setDomain($domain);
                $record->setLocale($language);
                $record->setOriginal($translation);
                $this->modelTranslatable->insertRecord($record);
            } catch (\Exception $e) {
                $errors[$language] = $e->getMessage();
            }
        }

        return $errors;
    }


    /**
     * @param array           $referenceObjects Array of objects to be referenced
     * @param OutputInterface $output           Command line output
     * @return array
     */
    private function updateReferences($referenceObjects, $output)
    {
        $errors = [];
        $progress = new ProgressBar($output, count($referenceObjects));

        /** @var Form $formValidator */
        $formValidator = $this->container->get('graviton.rest.validator.form');
        /** @var FormDataMapperInterface $formDataMapper */
        $formDataMapper = $this->container->get('graviton.document.service.formdatamapper');
        $request = new Request();
        $request->setMethod('PUT');

        foreach ($referenceObjects as $id => $ref) {
            $progress->advance();
            // Get Reference
            $data = $ref['data'];
            $fileName = $ref['file'];
            $model = str_replace('.document.', '.model.', $ref['service']);

            if ($this->container->has($model)) {
                /** @var DocumentModel $modelClass */
                $modelClass = $this->container->get($model);
                $formValidator->getForm($request, $modelClass);
                $form = $formValidator->getForm($request, $modelClass);

                if (array_key_exists('_id', $data)) {
                    $data['id'] = $data['_id'];
                    unset($data['_id']);
                }

                try {
                    $record = $formValidator->checkForm(
                        $form,
                        $modelClass,
                        $formDataMapper,
                        json_encode($data)
                    );
                } catch (ValidationException $e) {
                    $err = $formValidator->getErrorMessages($form);
                    $errors[] = $fileName.': '.$id . ': '.json_encode($err);
                    continue;
                } catch (\Exception $e) {
                    $errors[] = $fileName.': '.$id . ': '.$e->getMessage();
                    continue;
                }

                try {
                    $modelClass->updateRecord($id, $record);
                } catch (\Exception $e) {
                    $errors[] = $fileName.': '.$id . ' Updating: '.$e->getMessage();
                    continue;
                }
            } else {
                $errors[] = $fileName . ': Model not found to make relation';
            }
        }

        $progress->finish();
        return $errors;
    }

    /**
     * @param array $targets Path parts to service
     * @return array
     */
    private function findServiceObject($targets)
    {
        // Locate class name graviton.i18n.document.language
        $cleanedName = $targets[1].str_replace('refi-', '', $targets[2]);
        $serviceNames = [
            'gravitondyn.'.$cleanedName.'.document.'.$cleanedName,
            'gravitondyn.evojabasics'.$cleanedName.'.document.evojabasics'.$cleanedName,
            'gravitondyn.'.$targets[2].'.document.'.$targets[2],
            'graviton.'.$targets[1].'.document.'.$targets[2]
        ];
        $objectClass = false;
        $serviceName = false;
        $error = false;
        foreach ($serviceNames as $serviceName) {
            $serviceName = strtolower(str_replace('-', '', $serviceName));
            if ($this->container->has($serviceName)) {
                try {
                    $objectClass = $this->container->get($serviceName);
                } catch (\Exception $e) {
                    $error = 'Service name not found: '.$serviceName;
                }
                break;
            }
        }
        return [
            'class'   => $objectClass,
            'service' => $serviceName,
            'error'   => $error
        ];
    }
}
