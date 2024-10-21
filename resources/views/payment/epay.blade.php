<?php

require_once(app_path('Payment')."/kkb.utils.php");
$signedOrder = process_request($id, '398', 10000, public_path(env('EPAY_CERT_PATH')).'/config.txt');
$signedOrderAppendix = base64_encode('<document><item number="1" name="Оплата заказа" quantity="1" amount="'. 10000 .'"/></document>');

?>
<form action="{{env('EPAY_URL')}}" method="post" style="display: none" name="payment">
    <input type="hidden" name="Signed_Order_B64" id="Signed_Order_B64" value="{{$signedOrder}}">
    <input type="hidden" id="Email" name="Email" value="{{$email}}">
    <input type="hidden" name="Language" value="rus">
    <input type="hidden" name="BackLink" id="BackLink" value="https://kgforum.kz">
    <input type="hidden" name="FailureBackLink" id="FailureBackLink" value="https://kgf.cic.kz/api/payment/epay/fail">
    <input type="hidden" name="PostLink" value="https://kgf.cic.kz/api/payment/epay/success">
    <input type="hidden" name="appendix" id="appendix" value="{{$signedOrderAppendix}}">
</form>

<script>
    window.onload = function(){
        document.forms['payment'].submit();
    }
</script>
