<?php 

abstract class AutomationType {
    const OFF = "Off";
    const ON = "On";
    const WAITING_FOR_ACTIVE = "Waiting_for_Active";
    const WAITING_FOR_INACTIVE = "Waiting_for_Inactive";
}


abstract class AccountType {
    const WAITING_FOR_UPGRADE = 2000;
    const UNKNOWN = -1;
    const BASIC = 1;
    const PLUS = 2;
    const PRO = 3;
    const PRO_PLUS = 4;
}

abstract class PaymentMethod {
    const CREDIT_CARD = 1;
    const WIRE_TRANSFER = 2;
    const CRYPTO = 3;
    const BANXA = 4;
}

abstract class OpenOrdersCommandTypes {
    const BUY = 1;
    const SELL = 0;

    static function toString($type) {
        switch ($type) {
            case 1:
                return "BUY";
                break;
            case 0:
                return "SELL";
            default:
            return "NA";
                break;
        }
    }
}

abstract class PaymentType {
    const ACCESS = "access";
    const UPGRADE = "upgrade";
    const WITHDRAWAL = "withdrawal";
}

?>