<?php
namespace app\models\common;

use yii\base\Model;

class ServerHostDTO extends Model {
  public $token;
  public $phone;

  public function rules()
  {
      return [
          [['token','phone'],'required']
      ];
  }
}
