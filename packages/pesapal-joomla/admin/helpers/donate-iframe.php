<?php
include_once('OAuth.php');


$token = $params = NULL;

$dconfig = JComponentHelper::getParams('com_pesapal');

$consumer_key = $dconfig->get('consumer_key');
$consumer_secret = $dconfig->get('consumer_secret');
$mode = $dconfig->get('app_mode');
$signature_method = new OAuthSignatureMethod_HMAC_SHA1();
$iframelink = 'https://www.pesapal.com/api/PostPesapalDirectOrderV4';
$amount = $_POST['amount'];
$amount = number_format($amount, 2);
$desc = 'Test';
$type = 'MERCHANT';
$reference = uniqid();
$first_name =  $_POST['firstname'];
$last_name = $_POST['lastname'];
$mobile = @$_POST['number'];
$email =  $_POST['email'];
$description =  $_POST['description'];
$currency =  $_POST['currency'];
$city =  @$_POST['city'];
$address =  @$_POST['address'];
$zip =  @$_POST['zip'];
$country =  @$_POST['country'];

if($mode=="demo"){
    $iframelink ="https://demo.pesapal.com/api/PostPesapalDirectOrderV4";
}
$donor = array();

$callback_url =JURI::root().'index.php?option=com_pesapal&view=redirect';

$post_xml = "<?xml version=\"1.0\" encoding=\"utf-8\"?><PesapalDirectOrderInfo xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" Currency=\"".$currency."\" Amount=\"".$amount."\" Description=\"".$desc."\" Type=\"".$type."\" Reference=\"".$reference."\" FirstName=\"".$first_name."\" LastName=\"".$last_name."\" Email=\"".$email."\"  xmlns=\"http://www.pesapal.com\" />";
$post_xml = htmlentities($post_xml);

$consumer = new OAuthConsumer($consumer_key, $consumer_secret);

//post transaction to pesapal
$iframe_src = OAuthRequest::from_consumer_and_token($consumer, $token, "GET", $iframelink, $params);
$iframe_src->set_parameter("oauth_callback", $callback_url);
$iframe_src->set_parameter("pesapal_request_data", $post_xml);
$iframe_src->sign_request($signature_method, $consumer, $token);

//save data to db
$db = JFactory::getDbo();
$query = $db->getQuery(true);
$columns = array('firstname',
    'lastname',
    'mobile',
    'email',
    'description',
    'amount',
    'status',
    'currency',
    'city',
    'address',
    'zipcode',
    'country',
    'reference'
    );
$values = array($db->quote($first_name),
    $db->quote($last_name),
    $db->quote($mobile),
    $db->quote($email),
    $db->quote($description),
    $db->quote($amount),
    $db->quote('PLACED'),
    $db->quote($currency),
    $db->quote($city),
    $db->quote($address),
    $db->quote($zip),
    $db->quote($country),
    $db->quote($reference),
);
$query
    ->insert($db->quoteName('#__pesapaldonations'))
    ->columns($db->quoteName($columns))
    ->values(implode(',', $values));
$db->setQuery($query);
$db->execute();
?>
<?php echo $iframe_src;?>
