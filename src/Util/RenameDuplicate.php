<?php

namespace App\Util;

/**
 * Renames the file and adds a sequence to it.
 */
class RenameDuplicate
{
    /**
     * Renames the file and adds a sequence to it.
     *
     * @param string $filePathName The file name to be renamed.
     *
     * @throws \Exception When the sequence if over 999.
     *
     * @return string The renamed filename string.
     */
    public function renameFile(string $filePathName) :string
    {
        $pathParts = pathinfo($filePathName);
        if ($pathParts['dirname'] === '.') {
            $dirname = '';
        } else {
            $dirname = $pathParts['dirname'] . DIRECTORY_SEPARATOR;
        }
        $fileName = $pathParts['filename'];
        $extension = $pathParts['extension'] ?? '';

        $patterns = array('/^(.*)\((\d+)\)(\.?.*)$/','/(^((?!\.|\(\d+\)).)*$)()/');

        $fileName = preg_replace_callback(
            $patterns,
            function ($matches) {
                if ((int)$matches[2] >= 999) {
                    throw new \Exception('Sequence is too high!');
                }
                return $matches[1].'('.((int)$matches[2]+1).')';
            },
            $fileName
        );

        return $dirname . $fileName . '.' . $extension;
    }
}
