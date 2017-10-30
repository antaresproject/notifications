<?php

namespace Antares\Notifications\Decorator;

use Antares\Form\Controls\AbstractType;
use Antares\Form\Decorators\AbstractDecorator;

class NotificationFormDecorator extends AbstractDecorator
{

    /** @var string */
    protected $name = 'horizontal';

    /**
     * @param AbstractType $control
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function render(AbstractType $control)
    {
        $this->inputWrapper['class'] = 'text-left col-dt-14 col-14 col-mb-14';
        $this->labelWrapper['class'] = 'text-right col-dt-2 col-2 col-mb-2';

        return parent::render($control);
    }

}