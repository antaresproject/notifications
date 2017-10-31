<?php

namespace Antares\Notifications\Contracts;

interface TemplateMessageContract
{

    /**
     * @param string $name
     * @return $this
     */
    public function template(string $name);

    /**
     * @param array $data
     * @return $this
     */
    public function subjectData(array $data);

    /**
     * @param array $data
     * @return $this
     */
    public function viewData(array $data);

    /**
     * @return null|string
     */
    public function getTemplate();

    /**
     * @return array
     */
    public function getSubjectData(): array;

    /**
     * @return array
     */
    public function getViewData(): array;
}
