<?php
namespace app\models\emvco;

use app\models\common\BaseJsonRequestDTO;
class EmvcoCreateDTO extends BaseJsonRequestDTO
{
    public $type;
    public $name;
    public $codeType;
    public $terminalId;
    public $shopName;
    public $amount;
    public $city;

    public function rules()
    {
        return array_merge([
            [['type', 'codeType', 'terminalId', 'city'], 'required'],
            [['name', 'shopName'], 'string'],
            ['amount', 'double','min'=>0],
        ], parent::rules());
    }
}
