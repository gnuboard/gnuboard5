<?php

namespace API\v1\Model\Response\Qa;

use API\v1\Traits\SchemaHelperTrait;

//class QaResponse
//{
//    use SchemaHelperTrait;
//
//    public function __construct($data)
//    {
//        $this->mapDataToProperties($this, $data);
//    }
//
//
//
//}

/**
 * @OA\Schema(
 *   title="QaResponse",
 *   description="QaResponse",
 * )
 */
class QaResponse
{
    /**
     * @OA\Property(ref="#/components/schemas/QaContent")
     */
    public QaContent $qa_content;

    /**
     * @OA\Property(ref="#/components/schemas/QaAnswer")
     */
    public $answer;

//    /**
//     * @OA\Property(ref="#/components/schemas/PrevNext")
//     */
//    public $prev;
//
//    /**
//     * @OA\Property(ref="#/components/schemas/PrevNext")
//     */
//    public $next;

    /**
     * @OA\Property(type="array", @OA\Items(ref="#/components/schemas/QaAnswer"))
     */
    public array $related;

    public function __construct()
    {
//        $this->qa_content = new QaContent();
//        $this->answer = new Answer();
//        $this->prev = new PrevNext();
//        $this->next = new PrevNext();
        $this->related = [];
    }

//    public function addRelated(Related $related)
//    {
//        $this->related[] = $related;
//    }
}