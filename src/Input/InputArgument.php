<?php

namespace Silly\Input;

/**
 * Extending InputArgument because...
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class InputArgument extends \Symfony\Component\Console\Input\InputArgument
{
    private $description;

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getDescription()
    {
        return $this->description ?: parent::getDescription();
    }
}
