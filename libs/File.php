<?php

namespace libs;

class File
{
    private $file;

    private function getFileName($name)
    {
        $name = strtolower($name);
        $name = preg_replace('/[^a-zA-Z0-9\s\-]/', '', $name);
        $name = trim($name);
        $name = str_replace(' ', '-', $name);
        
        preg_match('/\/\w+$/', $this->getMimeType(), $matches);
        $type = str_replace('/', '', $matches[0]);

        return "$name.$type";
    }

    private function uniqualizeName($name)
    {
        $timestamp = time();
        $nameArr = explode('.', $name);
        $nameArr[0] = $nameArr[0] . "_{$timestamp}";

        return implode('.', $nameArr);
    }

    public function __construct($file)
    {
        $this->file = $file;
    }

    public static function get($field)
    {
        $file = isset($_FILES[$field]) ? $_FILES[$field] : null;

        return new self($file);
    }

    public function isExistsInInput()
    {
        return $this->file !== null;
    }

    public function isExistsOnDisk($path)
    {
        return file_exists($path);
    }

    public function isImage()
    {
        return !!preg_match('/^image\/(jpeg|png|gif)$/', $this->getMimeType());
    }

    public function getMimeType()
    {
        if (!$this->isExistsInInput())
        {
            throw new \Exception("File doesn't exist.");
        }

        return mime_content_type($this->file['tmp_name']);
    }

    public function delete($path, $fileName)
    {
        if (!$this->isExistsOnDisk("$path/$fileName"))
        {
            throw new \Exception("File doesn't exist.");
        }

        if (!is_readable($path))
        {
            throw new \Exception("Read permission denied.");
        }

        if (!is_writable($path))
        {
            throw new \Exception("Write permission denied.");
        }

        if (!is_executable($path))
        {
            throw new \Exception("Execute permission denied.");
        }

        unlink("$path/$fileName");
    }

    public function move($path, $name = null, $replace = true)
    {
        if (!$this->isExistsInInput())
        {
            throw new \Exception("File doesn't exist.");
        }

        if (!is_readable($path))
        {
            throw new \Exception("Read permission denied.");
        }

        if (!is_writable($path))
        {
            throw new \Exception("Write permission denied.");
        }

        if (!is_executable($path))
        {
            throw new \Exception("Execute permission denied.");
        }

        $fileName = null !== $name ? 
            $this->getFileName($name) :
            $this->file['name'];
        $fileTmp = $this->file['tmp_name'];

        if ($this->isExistsOnDisk("$path/$fileName"))
        {
            if (true === $replace)
            {
                $this->delete($path, $fileName);
            }
            else
            {
                $fileName = $this->uniqualizeName($fileName);
            }
        }

        $fileMoved = move_uploaded_file($fileTmp, "$path/$fileName");

        if (!$fileMoved)
        {
            throw new \Exception("Uploaded file wasn't moved.");
        }

        return $fileName;
    }
}