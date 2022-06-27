<?php

declare(strict_types=1);

namespace LogParser\Utils;

use LogParser\Exception\FileSystem\FileSystemException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class FileSystemUtils
{
    /**
     * @return string[] list of files in a directory
     */
    public static function listProjectDirectoryFilenames(string $projectDirectory): array
    {
        $finder = new Finder();
        $finder->files()->in(self::buildAbsolutePath($projectDirectory));
        $logFiles = [];

        foreach ($finder as $logFile) {
            $logFiles[] = $logFile->getFilename();
        }

        return $logFiles;
    }

    public static function getLogFileHandle(string $fileName, string $projectDirectory): \SplFileObject
    {
        $absoluteDirectoryPath = self::buildAbsolutePath($projectDirectory);
        $finder = (new Finder())
            ->files()
            ->in($absoluteDirectoryPath)
            ->name($fileName);

        if (!$finder->hasResults()) {
            throw FileSystemException::create(sprintf(
                'File "%s" not found in "%s"',
                $fileName,
                $absoluteDirectoryPath
            ));
        }

        if (1 < $finder->count()) {
            // In order to properly handle wildcard filename calls we have to check if there is only one result
            throw FileSystemException::create(sprintf(
                'Ambiguous definition of "%s" in "%s"',
                $fileName,
                $absoluteDirectoryPath
            ));
        }

        $fileIterator = $finder->getIterator();
        $fileIterator->rewind();
        /** @var SplFileInfo $file */
        $file = $fileIterator->current();

        if (!$file->isReadable()) {
            throw FileSystemException::create(sprintf(
                'File "%s" in "%s" is not readable',
                $fileName,
                $absoluteDirectoryPath
            ));
        }

        return $file->openFile();
    }

    private static function buildAbsolutePath(string $relativePath): string
    {
        return dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . $relativePath;
    }
}
