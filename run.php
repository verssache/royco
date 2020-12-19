<?php

$headers = array();
$headers[] = 'User-Agent: Mozilla/5.0 (Linux; Android 10; MI 8 Lite) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.86 Mobile Safari/537.36';
$headers[] = 'Content-Type: application/json;charset=UTF-8';

echo color('blue', "[+]")." Royco - By: GidhanB.A\n";
echo color('blue', "[+]")." Input No: ";
$nohp = trim(fgets(STDIN));
// if ($nohp[0] == "0") $nohp = substr($nohp, 1);

// Data
$base = json_decode(file_get_contents("https://wirkel.com/data.php?qty=1&domain=ladang.site"))->result[0];
$first = $base->firstname;
$last = $base->lastname;
$email = $base->email;
$sess = 'abcdefghijklmn';

$gas = curl('https://juaranutrimenu.royco.co.id/api/otp/request', '{"country":"ID","sessId":"'.$sess.'","phone_number":"'.$nohp.'"}', $headers);
if (strpos($gas[1], '"success":true')) {
	$sec = json_decode($gas[1])->data->secret;
	if (json_decode($gas[1])->data->isNewUser == false) {
		$isu = 'false';
	} else {
		$isu = 'true';
	}
	echo color('blue', "[+]")." Input OTP: ";
	$otp = trim(fgets(STDIN));
	$dat = '{"phone_number":"'.$nohp.'","sessId":"'.$sess.'","secret":"'.$sec.'","otp_token":"'.$otp.'","isNewUser":'.$isu.'}';
	$ver = curl('https://juaranutrimenu.royco.co.id/api/otp/submit', $dat, $headers);
	if (strpos($ver[1], '"success":true')) {
		$token = json_decode($ver[1])->data->access_token;
		print_r($token);
		die();
		$headers[] = 'Authorization: Bearer '.$token;
		$reg = curl('https://juaranutrimenu.royco.co.id/api/profiles/register', '{"first_name":"'.$first.'","last_name":"'.$last.'","email":"'.$email.'","phone":"+62'.$nohp.'","sex":"male","address":"Jakarta","birthday":"2000-12-'.mt_rand(10,30).'"}', $headers);
		if (strpos($reg[1], '"success":true')) {
			echo color('green', "[+]")." Registration successfully!\n";
			$tokenew = json_decode($reg[1])->new_access_token;
			file_put_contents("royco-token.txt", "$tokenew\n", FILE_APPEND);
			array_pop($headers);
			$headers[] = 'Authorization: Bearer '.$tokenew;
			$saldo = curl('https://juaranutrimenu.royco.co.id/api/profiles/detail', null, $headers);
			if (strpos($saldo[1], '"success":true')) {
				$bal = json_decode($saldo[1])->data->balance;
				if ($bal >= 15000) {
					$amo = '10000';
				} else if ($bal >= 5000 && $bal < 15000) {
					$amo = $bal;
				} else {
					echo color('red', "[+]")." Empty Balance!\n";
					die();
				}
				echo color('yellow', "[+]")." Balance: $bal pts\n";
				echo color('blue', "[+]")." Target No: ";
				$target = trim(fgets(STDIN));
				$pulsa = curl('https://juaranutrimenu.royco.co.id/api/lixus/pulsa', '{"phone":"+62'.$target.'","amount":'.$amo.',"name":"'.$first.'"}', $headers);
				if (strpos($pulsa[1], '"success":true')) {
					$puldat = json_decode($pulsa[1])->data->paymentDetails[0];
					echo color('green', "[+]")." Success! - TrxId: ".$puldat->TransactionId." - TrxCode: ".$puldat->transactionCode."\n";
				} else {
					print_r($pulsa);
				}
			} else {
				print_r($saldo);
			}
		} else {
			print_r($reg);
		}
	} else {
		print_r($ver);
	}
} else {
	print_r($gas);
}

function curl($url, $post, $headers, $follow = false, $method = null)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		if ($follow == true) curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		if ($method !== null) curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
		if ($headers !== null) curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		if ($post !== null) curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		$result = curl_exec($ch);
		$header = substr($result, 0, curl_getinfo($ch, CURLINFO_HEADER_SIZE));
		$body = substr($result, curl_getinfo($ch, CURLINFO_HEADER_SIZE));
		preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $result, $matches);
		$cookies = array();
		foreach ($matches[1] as $item) {
			parse_str($item, $cookie);
			$cookies = array_merge($cookies, $cookie);
		}
		return array(
			$header,
			$body,
			$cookies
		);
	}

function color($color = "default" , $text)
    {
        $arrayColor = array(
            'red'       => '1;31',
            'green'     => '1;32',
            'yellow'    => '1;33',
            'blue'      => '1;34',
        );  
        return "\033[".$arrayColor[$color]."m".$text."\033[0m";
    }
