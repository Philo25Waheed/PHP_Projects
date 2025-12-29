<?php
// Simple Twilio WhatsApp helper
require_once __DIR__ . '/config.php';
$cfg = require __DIR__ . '/config.php';

function sendWhatsAppMessage($toNumber, $message) {
    $cfg = require __DIR__ . '/config.php';
    $tw = $cfg['twilio'] ?? null;
    if (empty($tw) || empty($tw['account_sid']) || empty($tw['auth_token']) || empty($tw['from_whatsapp_number'])) {
        error_log('Twilio config missing, cannot send WhatsApp');
        return false;
    }

    $sid = $tw['account_sid'];
    $token = $tw['auth_token'];
    $from = $tw['from_whatsapp_number'];

    // Ensure numbers are whatsapp: prefixed for Twilio API
    if (strpos($from, 'whatsapp:') !== 0) $from = 'whatsapp:' . $from;
    if (strpos($toNumber, 'whatsapp:') !== 0) $to = 'whatsapp:' . $toNumber; else $to = $toNumber;

    $url = "https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json";
    $post = http_build_query([
        'From' => $from,
        'To' => $to,
        'Body' => $message
    ]);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    curl_setopt($ch, CURLOPT_USERPWD, $sid . ':' . $token);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    $resp = curl_exec($ch);
    $err = curl_error($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($err) {
        error_log('Twilio curl error: ' . $err);
        return false;
    }
    if ($code >= 200 && $code < 300) return true;
    error_log('Twilio response code: ' . $code . ' resp: ' . $resp);
    return false;
}
