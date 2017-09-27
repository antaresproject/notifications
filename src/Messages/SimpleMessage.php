<?php

namespace Antares\Notifications\Messages;

use Antares\Notifications\Contracts\MessageContract;
use Antares\Notifications\Contracts\TemplateMessageContract;
use Antares\Notifications\Traits\DeliveredTemplateTrait;
use Illuminate\Notifications\Messages\SimpleMessage as BaseSimpleMessage;

class SimpleMessage extends BaseSimpleMessage implements MessageContract, TemplateMessageContract {

    use DeliveredTemplateTrait;

}
