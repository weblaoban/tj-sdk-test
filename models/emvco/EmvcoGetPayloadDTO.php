<?php
namespace tj\sdk\test\models\emvco;

use tj\sdk\test\models\common\BaseJsonRequestDTO;

class EmvcoGetPayloadDTO extends BaseJsonRequestDTO {
  public $type;

  public function rules()
  {
      return array_merge(
          parent::rules(),
          [
              ['type','required']
          ]
      ); // TODO: Change the autogenerated stub
  }
}
