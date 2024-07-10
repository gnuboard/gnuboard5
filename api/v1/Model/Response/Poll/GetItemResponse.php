<?php

namespace API\v1\Model\Response\Poll;

class GetItemResponse
{
    public array $poll;
    public int $totoal_vote;
    public array $items;
    public array $etcs;
    public array $other_polls;
}