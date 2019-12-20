<?php

namespace spec\Cardinity\Exception;

use Cardinity\Method\ResultObject;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class NotAcceptableSpec extends ObjectBehavior
{
    function let(\RuntimeException $exception, ResultObject $error)
    {
        $this->beConstructedWith(
            $exception,
            $error
        );
    }
    
    function it_is_initializable()
    {
        $this->shouldHaveType('Cardinity\Exception\NotAcceptable');
    }

    function it_should_return_correct_code()
    {
        $this->getCode()->shouldReturn(406);
    }
}
