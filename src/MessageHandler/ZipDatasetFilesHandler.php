<?php

namespace App\MessageHandler;

use App\Entity\DatasetSubmission;
use App\Message\ZipDatasetFiles;
use App\Repository\FileRepository;
use App\Util\Datastore;
use App\Util\ZipFiles;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

/**
 * Handler for zipping files for download.
 */
class ZipDatasetFilesHandler implements MessageHandlerInterface
{
    /**
     * The monolog logger.
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Zip files utility class.
     *
     * @var ZipFiles
     */
    private $zipFiles;

    /**
     * Download directory for the zip file.
     *
     * @var string
     */
    private $downloadDirectory;

    /**
     * The File Repository.
     *
     * @var FileRepository
     */
    private $fileRepository;

    /**
     * Entity Manager interface instance.
     *
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * Datastore utility instance.
     *
     * @var Datastore
     */
    private $datastore;

    /**
     * ZipDatasetFilesHandler constructor.
     *
     * @param LoggerInterface        $zipperLogger      A name hinted "<monologhandlername>Logger" Monolog logger interface.
     * @param ZipFiles               $zipFiles          Zip files utility instance.
     * @param string                 $downloadDirectory Temporary download directory path.
     * @param FileRepository         $fileRepository    File repository to query objects.
     * @param EntityManagerInterface $entityManager     Entity manager interface instance.
     * @param Datastore              $datastore         Datastore utility instance.
     */
    public function __construct(
        LoggerInterface $zipperLogger,
        ZipFiles $zipFiles,
        string $downloadDirectory,
        FileRepository $fileRepository,
        EntityManagerInterface $entityManager,
        Datastore $datastore
    ) {
        $this->logger = $zipperLogger;
        $this->zipFiles = $zipFiles;
        $this->downloadDirectory = $downloadDirectory;
        $this->fileRepository = $fileRepository;
        $this->entityManager = $entityManager;
        $this->datastore = $datastore;
    }

    /**
     * Invoke function to zip files for download.
     *
     * @param ZipDatasetFiles $zipDatasetFiles  Symfony message for zip dataset files.
     */
    public function __invoke(ZipDatasetFiles $zipDatasetFiles)
    {
        $this->logger->info('Zip worker starting');
        $fileIds = $zipDatasetFiles->getFileIds();
        $datasetSubmissionId = $zipDatasetFiles->getDatasetSubmissionId();
        $datasetSubmission = $this->entityManager->getRepository(DatasetSubmission::class)->find($datasetSubmissionId);
        $filesInfo = $this->fileRepository->getFilePathNameAndPhysicalPath($fileIds);
        $destinationPath = $this->downloadDirectory . DIRECTORY_SEPARATOR .  str_replace(':', '.', $datasetSubmission->getDataset()->getUdi()) . '.zip';
        $this->logger->info('Zipfile opened: ' . $destinationPath);
        try {
            $outputStream = array('fileStream' => fopen($destinationPath, 'w+'));
            $this->zipFiles->start($outputStream, basename($destinationPath));
            foreach ($filesInfo as $fileItemInfo) {
                $this->logger->info("adding file to $destinationPath:" . $fileItemInfo['filePathName']);
                $this->zipFiles->addFile($fileItemInfo['filePathName'], $this->datastore->getFile($fileItemInfo['physicalFilePath']));
            }
            $this->zipFiles->finish();
            $this->logger->info('Zipfile closed: ' . $destinationPath);
            fclose($outputStream['fileStream']);
            $datasetSubmission->getFileset()->setZipFilePath($destinationPath);
            $this->entityManager->flush();
        } catch (\Exception $exception) {
            $this->logger->error(sprintf('Unable to zip file. Message: %s', $exception->getMessage()));
            return;
        }
        $this->logger->info('Zip worker finished');
    }
}
