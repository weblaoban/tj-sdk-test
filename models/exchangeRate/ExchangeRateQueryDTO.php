<?php
namespace app\models\exchangeRate;
use app\models\common\BaseJsonRequestDTO;

class ExchangeRateQueryDTO extends BaseJsonRequestDTO {
  public $originalCurrency;
  public $targetCurrency;

  public function rules()
  {
      return array_merge(
          [
              [['originalCurrency','targetCurrency'],'required']
          ],
          parent::rules()); // TODO: Change the autogenerated stub
  }
}
