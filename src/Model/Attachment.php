<?php

namespace Antares\Notifications\Model;

class Attachment {

    /**
     * File system path to file.
     *
     * @var string
     */
    protected $path;

    /**
     * File name with extension.
     *
     * @var string
     */
    protected $fileName;

    /**
     * MIME type.
     *
     * @var string
     */
    protected $mime;

    /**
     * Attachment constructor.
     * @param string $path
     * @param string $fileName
     * @param string $mime
     */
    public function __construct(string $path, string $fileName, string $mime) {
        $this->path     = $path;
        $this->fileName = $fileName;
        $this->mime     = $mime;
    }

    /**
     * Returns sile system path to file.
     *
     * @return string
     */
    public function getPath() : string {
        return $this->path;
    }

    /**
     * Returns file name with extension.
     *
     * @return string
     */
    public function getFileName() : string {
        return $this->fileName;
    }

    /**
     * Returns MIME type.
     *
     * @return string
     */
    public function getMime() : string {
        return $this->mime;
    }

    /**
     * Returns computed options of the attachment.
     *
     * @return array
     */
    public function getComputedOptions() : array {
        return [
            'as'    => $this->getFileName(),
            'mime'  => $this->getMime(),
        ];
    }

}
