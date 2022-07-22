<?php

define('DB_DSN','mysql:dbname=vendo_simulation;host=localhost;charset=utf8mb4');
define('DB_USER','vendo');
define('DB_PASS','8V^5hq_n11:^');

define('ROOT_URL','https://vendo.club/vendo_simulation/');

define('BITCOIN_API_URL', 'https://www.coinpayments.net/api.php');
define('BITCOIN_API_KEY', "83a8f17d6bf79e52fee91611083766836b500909b359978c293e6bd91a499f62");
define('BITCOIN_HMAC_KEY', "39bE514F646d12ad40A34319889e2e2987a05f09dA215Af7E4d835d16386be4C");
define('BITCOIN_HMAC_IPN_KEY','vefgtvfLK!?HH45rRR!"§');

define('BITCOIN_PAYMENT_TOLERANCE', 0.05); //95% tollerance (min 5% of original price is accepted)

define('DURATION_TEMPORARY_CODE_VALID_SECONDS', 7*24*60*60);

define('REGISTRATION_FEE', 59);
define('ACCOUNT_1_NAME', 'Basic');
define('ACCOUNT_2_NAME', 'Plus');
define('ACCOUNT_3_NAME', 'Pro');
define('ACCOUNT_4_NAME', 'Pro+');
define('ACCOUNT_1_AMOUNT', 1000);
define('ACCOUNT_2_AMOUNT', 2500);
define('ACCOUNT_3_AMOUNT', 5000);
define('ACCOUNT_4_AMOUNT', 10000);
define('ACCOUNT_1_FEE', 150);
define('ACCOUNT_2_FEE', 450);
define('ACCOUNT_3_FEE', 1350);
define('ACCOUNT_4_FEE', 2650);
define('ACCOUNT_1_ORIGINAL_FEE', 150);
define('ACCOUNT_2_ORIGINAL_FEE', 450);
define('ACCOUNT_3_ORIGINAL_FEE', 1350);
define('ACCOUNT_4_ORIGINAL_FEE', 2650);

//Proton Captial Markets Broker API (https://protoncapitalmarkets.com/developers)
define('PROTONCAPITALMARKETS_SERVERNAME', 'VENDO');
define('PROTONCAPITALMARKETS_AUTHCODE', 'a203175c4245c32dc419e81fb80e5396');
define('PROTONCAPITALMARKETS_WLCODE', 'VN');

define('BITCOIN_REQUEST_FAILED_IMAGE_URL', './Images/error_bitcoin.png');

define('FROM_MAIL_GENERAL','noreply@vendo.club');

define('KEY_DB','9BB55429C214A544F93763A7BB67006E953A67B691769A6068D7ED92F7BED081');

define('CRON_SECRET','9aLXcNET3R7pd8pazT');

define('PROFILE_PICTURE_ROOT_DIR', './users/profiles/');
define('PICTURE_DIR', "/picture/");

define('BITCOIN_RESTART_PAYMENT_URL', '/bo_restart_payment.php');

define('BROKER_SECRET', '36k2iCe5Wa2lc1d4R4ma7C190ev0mjsz6YeoWR7OizwB1PNd6i5KGsXsttX8w83W');

define('BANXA_CURRENCY','BTC');

define('ROOT_USER', 1);
define('OWNER_USER', 79);

define('COMMISSION_DELAY', 0); //commissions are calculated 15 days after payment has been started
?>
